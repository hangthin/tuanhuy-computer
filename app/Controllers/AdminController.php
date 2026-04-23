<?php
require_once __DIR__.'/../Models/Models.php';
require_once __DIR__.'/../Models/ProductModel.php';
require_once __DIR__.'/../Middleware/RoleGuard.php';
require_once __DIR__.'/../Helpers/Logger.php';

class AdminController {

    /** Gate: allow Admin / Manager / Staff; block everyone else */
    private function check(): void {
        RoleGuard::requireStaffOrAbove();
    }

    /** Gate: Admin only (delete, logs, system actions) */
    private function checkAdmin(): void {
        if (!isAdmin()) {
            RoleGuard::deny('Chỉ Admin mới có quyền thực hiện hành động này.');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────────────
    public function index($p = null): void {
        $this->check();
        $om             = new OrderModel();
        $stats          = $om->getStats();
        $db             = Database::getInstance();
        $totalCustomers = $db->query("SELECT COUNT(*) FROM users WHERE role=0")->fetchColumn();
        $totalProducts  = $db->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND is_deleted=0")->fetchColumn();

        // Separate out-of-stock vs low-stock
        $outOfStock = $db->fetchAll(
            "SELECT p.name, i.stock_quantity
             FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0
             WHERE i.stock_quantity<=0 ORDER BY p.name LIMIT 5"
        );
        $lowStockItems = $db->fetchAll(
            "SELECT p.name, i.stock_quantity
             FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0
             WHERE i.stock_quantity>0 AND i.stock_quantity<=i.min_stock ORDER BY i.stock_quantity LIMIT 5"
        );

        // Yesterday stats for % change arrows
        $yesterday = $db->fetch(
            "SELECT COUNT(*) cnt, COALESCE(SUM(total),0) rev FROM orders
             WHERE DATE(created_at)=DATE_SUB(CURDATE(),INTERVAL 1 DAY)
             AND status!='cancelled' AND is_deleted=0"
        );

        // Pending count + how many are >2hrs old
        $pendingRow    = $db->fetch(
            "SELECT COUNT(*) cnt, SUM(TIMESTAMPDIFF(MINUTE,created_at,NOW())>120) old_cnt
             FROM orders WHERE status='pending' AND is_deleted=0"
        );
        $pendingCount  = (int)($pendingRow['cnt']     ?? 0);
        $pendingOldCnt = (int)($pendingRow['old_cnt'] ?? 0);

        // Last 7 days revenue for line chart
        $last7Raw = $db->fetchAll(
            "SELECT DATE(created_at) d, COALESCE(SUM(total),0) rev, COUNT(*) cnt
             FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             AND status!='cancelled' AND is_deleted=0
             GROUP BY DATE(created_at) ORDER BY d"
        );
        $last7Labels = array(); $last7Rev = array(); $dayMap = array();
        foreach ($last7Raw as $r) { $dayMap[$r['d']] = $r; }
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $last7Labels[] = date('d/m', strtotime($day));
            $last7Rev[]    = round((float)($dayMap[$day]['rev'] ?? 0) / 1000000, 2);
        }

        $recentOrders = $om->getAll('', '', 1, 8);
        $pageTitle    = 'Admin Dashboard';
        include __DIR__.'/../Views/admin/dashboard.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Products
    // ─────────────────────────────────────────────────────────────────
    public function products($action = null): void {
        $this->check();
        $pm     = new ProductModel();
        $search = $_GET['s']    ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));

        // ── CREATE ──────────────────────────────────────────────────
        if ($action === 'create') {
            if (!RoleGuard::canCreate('products')) {
                RoleGuard::deny('Bạn không có quyền thêm sản phẩm.');
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $d   = $this->getProdPost();
                $img = $this->handleUpload();
                if ($img) $d['image'] = $img;
                $pid = $pm->create($d);
                $this->handleExtraImages($pm, $pid);
                $this->handleExtraImagesB64($pm, $pid);
                $this->handleExtraImagesPresaved($pm, $pid);

                Logger::create('products', $pid, $d);

                setFlash('success', 'Thêm thành công!');
                header('Location:' . APP_URL . '/admin/products'); exit;
            }
            $categories   = (new CategoryModel())->getForAdmin();
            $brands       = Database::getInstance()->fetchAll("SELECT * FROM brands ORDER BY name");
            $product      = [];
            $productImages = [];
            $pageTitle    = 'Thêm sản phẩm';
            include __DIR__.'/../Views/admin/product_form.php'; return;
        }

        // ── EDIT ─────────────────────────────────────────────────────
        if ($action === 'edit') {
            $id      = (int)($_GET['id'] ?? 0);
            $product = $pm->getById($id);
            if (!$product) {
                setFlash('error', 'Không tìm thấy');
                header('Location:' . APP_URL . '/admin/products'); exit;
            }
            if (!RoleGuard::canEdit('products', $product['created_at'] ?? null)) {
                $secsLeft = RoleGuard::staffEditSecondsLeft($product['created_at'] ?? null);
                $msg = $secsLeft !== null
                    ? 'Hết thời gian chỉnh sửa (15 phút). Liên hệ Manager/Admin.'
                    : 'Bạn không có quyền chỉnh sửa sản phẩm.';
                RoleGuard::deny($msg);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $oldData = $product;
                $d       = $this->getProdPost();
                $img     = $this->handleUpload();
                if ($img) {
                    $pm->updateImage($id, $img);
                    $d['image'] = $img;
                }
                $pm->update($id, $d);
                $this->handleExtraImages($pm, $id);
                $this->handleExtraImagesB64($pm, $id);
                $this->handleExtraImagesPresaved($pm, $id);

                // Chỉ lưu các field thực sự thay đổi + name làm context hiển thị
                $diffOld = [];
                $diffNew = [];
                foreach ($d as $k => $v) {
                    if ($k === 'slug') continue; // slug luôn random, bỏ qua
                    $oldVal = isset($oldData[$k]) ? $oldData[$k] : null;
                    if ((string)$oldVal !== (string)$v) {
                        $diffOld[$k] = $oldVal;
                        $diffNew[$k] = $v;
                    }
                }
                if (!empty($diffOld) || $img) {
                    if ($img) {
                        $actStr = 'Cập nhật ảnh chính';
                    } elseif (!empty($_POST['remove_bg'])) {
                        $actStr = 'Tách nền ảnh';
                    } elseif (!empty($_POST['add_logo'])) {
                        $actStr = 'Gắn logo';
                    } elseif (array_key_exists('price', $diffOld) || array_key_exists('sale_price', $diffOld)) {
                        $actStr = 'Cập nhật giá';
                    } else {
                        $actStr = 'Cập nhật thông tin';
                    }
                    $logOld = !empty($diffOld) ? array_merge(['name' => $oldData['name'] ?? ''], $diffOld) : null;
                    $logNew = array_merge(['name' => $d['name'] ?? $oldData['name'] ?? ''], !empty($diffOld) ? $diffNew : []);
                    Logger::log($actStr, 'products', $id, $logOld, $logNew);
                }

                setFlash('success', 'Cập nhật thành công!');
                header('Location:' . APP_URL . '/admin/products'); exit;
            }
            $categories    = (new CategoryModel())->getForAdmin();
            $brands        = Database::getInstance()->fetchAll("SELECT * FROM brands ORDER BY name");
            $productImages = $pm->getImages($id);
            $pageTitle     = 'Sửa sản phẩm';
            include __DIR__.'/../Views/admin/product_form.php'; return;
        }

        // ── DELETE (soft) ────────────────────────────────────────────
        if ($action === 'delete') {
            if (!RoleGuard::canDelete()) {
                RoleGuard::deny(
                    RoleGuard::role() === RoleGuard::MANAGER
                        ? 'Manager không có quyền xóa dữ liệu.'
                        : 'Bạn không có quyền xóa sản phẩm.'
                );
            }
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $old = $pm->getById($id);
                $db  = Database::getInstance();
                $db->query(
                    "UPDATE products SET is_deleted=1, deleted_at=NOW(), deleted_by=? WHERE id=?",
                    [$_SESSION['user_id'], $id]
                );
                if ($old) Logger::delete('products', $id, $old);
                setFlash('success', 'Đã xóa sản phẩm!');
            }
            header('Location:' . APP_URL . '/admin/products'); exit;
        }

        // ── RESTORE (undo soft-delete) ───────────────────────────────
        if ($action === 'restore') {
            if (!RoleGuard::canDelete()) {
                RoleGuard::deny('Chỉ Admin mới có quyền khôi phục sản phẩm.');
            }
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $pm->restore($id);
                $restored = $pm->getById($id);
                if ($restored) Logger::update('products', $id, ['is_deleted' => 1], ['is_deleted' => 0]);
                setFlash('success', 'Đã khôi phục sản phẩm!');
            }
            header('Location:' . APP_URL . '/admin/products?trash=1'); exit;
        }

        // ── DELETE IMAGE ─────────────────────────────────────────────
        if ($action === 'delete-image') {
            $isAjax = !empty($_GET['json']);
            if (!RoleGuard::canDelete()) {
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'Không có quyền xóa ảnh']); exit; }
                RoleGuard::deny('Bạn không có quyền xóa ảnh.');
            }
            $imgId = (int)($_GET['img_id']    ?? 0);
            $pid   = (int)($_GET['product_id'] ?? 0);
            if ($imgId > 0) {
                $imgRow = Database::getInstance()->fetch(
                    "SELECT pi.image, p.name FROM product_images pi LEFT JOIN products p ON p.id=pi.product_id WHERE pi.id=?",
                    [$imgId]
                );
                $pm->deleteImage($imgId);
                Logger::logActivity('Xóa ảnh', 'products', $pid ?: null, array_filter([
                    'name'  => $imgRow['name'] ?? null,
                    'image' => $imgRow['image'] ?? null,
                ]));
            }
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true]); exit; }
            header('Location:' . APP_URL . '/admin/products/edit?id=' . $pid); exit;
        }

        // ── LIST ─────────────────────────────────────────────────────
        $catId             = (int)($_GET['cat'] ?? 0);
        $trash             = !empty($_GET['trash']);
        $products          = $pm->getAllAdmin($search, $page, 20, $catId, $trash);
        $totalPagesAdmin   = (int)ceil($pm->countAdmin($search, $catId, $trash) / 20);
        $trashCount        = $pm->countAdmin('', 0, true);
        $categories        = (new CategoryModel())->getAll();
        $currentCatId      = $catId;
        $pageTitle         = $trash ? 'Thùng rác – Sản phẩm' : 'Quản lý sản phẩm';
        include __DIR__.'/../Views/admin/products.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Categories
    // ─────────────────────────────────────────────────────────────────
    public function categories($action = null): void {
        $this->check();
        $cm = new CategoryModel();

        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!RoleGuard::canCreate('products')) {
                RoleGuard::deny('Bạn không có quyền chỉnh sửa danh mục.');
            }
            $id   = (int)($_POST['id'] ?? 0);
            $data = [
                'name'        => sanitize($_POST['name']        ?? ''),
                'slug'        => makeSlug($_POST['name']        ?? ''),
                'icon'        => sanitize($_POST['icon']        ?? ''),
                'description' => sanitize($_POST['description'] ?? ''),
                'sort_order'  => (int)($_POST['sort_order']    ?? 0),
                'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
            ];
            if ($id > 0) {
                $old = $cm->getById($id);
                $cm->update($id, $data);
                if ($old) Logger::update('categories', $id, $old, array_merge($old, $data), ['name' => $old['name']]);
                setFlash('success', 'Cập nhật danh mục thành công!');
            } else {
                $cm->create($data);
                $newId = (int)Database::getInstance()->lastInsertId();
                Logger::create('categories', $newId, $data);
                setFlash('success', 'Thêm danh mục thành công!');
            }
            header('Location:' . APP_URL . '/admin/categories'); exit;
        }

        $categories = $cm->getForAdmin();
        $pageTitle  = 'Quản lý danh mục';
        include __DIR__.'/../Views/admin/categories.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Staff / HR  (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function staff($action = null): void {
        $this->checkAdmin();
        $um = new UserModel();

        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            if ($um->findByEmail($email)) {
                setFlash('error', 'Email đã tồn tại!');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            $pw = $_POST['password'] ?? '';
            if (strlen($pw) < 6) {
                setFlash('error', 'Mật khẩu ít nhất 6 ký tự!');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            $data = [
                'fullname' => sanitize($_POST['fullname'] ?? ''),
                'email'    => $email,
                'phone'    => sanitize($_POST['phone'] ?? ''),
                'password' => $pw,
                'role'     => (int)($_POST['role'] ?? 3),
            ];
            $id = $um->createStaff($data);
            Logger::create('users', $id, ['fullname' => $data['fullname'], 'email' => $data['email'], 'role' => $data['role']]);
            setFlash('success', 'Thêm nhân sự thành công!');
            header('Location:' . APP_URL . '/admin/staff'); exit;
        }

        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $u  = $um->findById($id);
            if (!$u || (int)$u['role'] === 0) {
                setFlash('error', 'Không tìm thấy nhân sự');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            // Prevent demoting self
            if ($id === (int)$_SESSION['user_id'] && (int)($_POST['role'] ?? 1) !== 1) {
                setFlash('error', 'Không thể thay đổi vai trò của chính mình!');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            $old  = $u;
            $data = [
                'fullname' => sanitize($_POST['fullname'] ?? ''),
                'phone'    => sanitize($_POST['phone']    ?? ''),
                'role'     => (int)($_POST['role'] ?? $u['role']),
            ];
            $um->updateStaff($id, $data);
            Logger::update('users', $id, $old, array_merge($old, $data), ['fullname' => $old['fullname'] ?? '']);
            setFlash('success', 'Cập nhật thành công!');
            header('Location:' . APP_URL . '/admin/staff'); exit;
        }

        if ($action === 'toggle') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id === (int)$_SESSION['user_id']) {
                setFlash('error', 'Không thể tự khóa tài khoản của mình!');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            $u = $um->findById($id);
            if ($u && (int)$u['role'] > 0) {
                $um->setActive($id, $u['is_active'] ? 0 : 1);
                Logger::update('users', $id,
                    ['is_active' => $u['is_active']],
                    ['is_active' => $u['is_active'] ? 0 : 1],
                    ['fullname'  => $u['fullname'] ?? '']
                );
                setFlash('success', ($u['is_active'] ? 'Đã khóa' : 'Đã mở khóa') . ' tài khoản!');
            }
            header('Location:' . APP_URL . '/admin/staff'); exit;
        }

        if ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $pw = $_POST['password'] ?? '';
            if (strlen($pw) < 6) {
                setFlash('error', 'Mật khẩu ít nhất 6 ký tự!');
                header('Location:' . APP_URL . '/admin/staff'); exit;
            }
            $u = $um->findById($id);
            if ($u && (int)$u['role'] > 0) {
                $um->updatePassword($id, password_hash($pw, PASSWORD_BCRYPT));
                Logger::log('UPDATE', 'users', $id, null, ['note' => 'password_reset']);
                setFlash('success', 'Đã đặt lại mật khẩu!');
            }
            header('Location:' . APP_URL . '/admin/staff'); exit;
        }

        $search          = $_GET['s'] ?? '';
        $page            = max(1, (int)($_GET['page'] ?? 1));
        $staffList       = $um->getAllStaff($search, $page);
        $totalPagesAdmin = (int)ceil($um->countStaff($search) / 20);
        $pageTitle       = 'Quản lý nhân sự';
        include __DIR__ . '/../Views/admin/staff.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Customers
    // ─────────────────────────────────────────────────────────────────
    public function customers($action = null): void {
        $this->check();
        $db = Database::getInstance();
        $um = new UserModel();

        // ── TOGGLE (lock/unlock single) ─────────────────────────────
        if ($action === 'toggle') {
            if (RoleGuard::role() === RoleGuard::STAFF)
                RoleGuard::deny('Staff không có quyền.');
            $id = (int)($_GET['id'] ?? 0);
            $u  = $um->findById($id);
            if ($u && (int)$u['role'] === 0) {
                $newState = $u['is_active'] ? 0 : 1;
                $um->setActive($id, $newState);
                Logger::log('UPDATE','users',$id,
                    ['fullname'=>$u['fullname'],'is_active'=>$u['is_active']],
                    ['fullname'=>$u['fullname'],'is_active'=>$newState]
                );
            }
            header('Location:'.APP_URL.'/admin/customers'); exit;
        }

        // ── BULK TOGGLE ─────────────────────────────────────────────
        if ($action === 'bulk-toggle' && $_SERVER['REQUEST_METHOD']==='POST') {
            header('Content-Type: application/json');
            if (RoleGuard::role() === RoleGuard::STAFF) {
                echo json_encode(['ok'=>false,'message'=>'Không có quyền']); return;
            }
            $ids    = array_filter(array_map('intval', $_POST['ids'] ?? []));
            $active = ($_POST['act'] ?? '') === 'unlock' ? 1 : 0;
            foreach ($ids as $cid) {
                $u = $um->findById($cid);
                if ($u && (int)$u['role'] === 0) {
                    $um->setActive($cid, $active);
                }
            }
            echo json_encode(['ok'=>true,'count'=>count($ids)]); return;
        }

        // ── DETAIL (AJAX) ───────────────────────────────────────────
        if ($action === 'detail') {
            header('Content-Type: application/json');
            $id = (int)($_GET['id'] ?? 0);
            $c  = $db->fetch(
                "SELECT u.*, COUNT(DISTINCT o.id) AS order_count,
                        COALESCE(SUM(o.total),0) AS total_spent,
                        MAX(o.created_at) AS last_order_at
                 FROM users u
                 LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
                 WHERE u.id=? AND u.role=0 GROUP BY u.id", [$id]
            );
            if (!$c) { echo json_encode(['ok'=>false]); return; }
            $orders = $db->fetchAll(
                "SELECT id,order_code,total,status,created_at,
                        (SELECT COUNT(*) FROM order_details WHERE order_id=orders.id) AS items
                 FROM orders WHERE user_id=? AND is_deleted=0
                 ORDER BY created_at DESC LIMIT 10", [$id]
            );
            $notes = $db->fetchAll(
                "SELECT new_data, user_name, created_at FROM action_logs
                 WHERE action='CNOTE' AND target_id=?
                 ORDER BY created_at DESC LIMIT 20", [$id]
            );
            echo json_encode(['ok'=>true,'customer'=>$c,'orders'=>$orders,'notes'=>$notes]);
            return;
        }

        // ── SAVE NOTE ───────────────────────────────────────────────
        if ($action === 'note' && $_SERVER['REQUEST_METHOD']==='POST') {
            header('Content-Type: application/json');
            $cid  = (int)($_POST['customer_id'] ?? 0);
            $note = trim($_POST['note'] ?? '');
            if ($cid && $note) {
                Logger::log('CNOTE','users',$cid,null,[
                    'note' => $note,
                    'by'   => $_SESSION['user_name'] ?? 'Admin',
                ]);
            }
            echo json_encode(['ok'=>true]); return;
        }

        // ── RESET PASSWORD ──────────────────────────────────────────
        if ($action === 'reset-pw') {
            if (RoleGuard::role() === RoleGuard::STAFF)
                RoleGuard::deny('Staff không có quyền.');
            $id = (int)($_GET['id'] ?? 0);
            $u  = $um->findById($id);
            if ($u && (int)$u['role'] === 0) {
                $newPw   = substr(str_shuffle('abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 10);
                $hashed  = password_hash($newPw, PASSWORD_DEFAULT);
                $db->query("UPDATE users SET password=? WHERE id=?", [$hashed, $id]);
                Logger::log('UPDATE','users',$id,
                    ['fullname'=>$u['fullname'],'note'=>'reset_password'],
                    ['fullname'=>$u['fullname'],'note'=>'password_reset_by_admin']
                );
                $_SESSION['flash'] = array(
                    'type' => 'success_center',
                    'icon' => '🔑',
                    'msg'  => 'Đã reset mật khẩu — ' . $u['fullname'],
                    'sub'  => 'Mật khẩu mới: ' . $newPw,
                );
            }
            header('Location:'.APP_URL.'/admin/customers'); exit;
        }

        // ── EXPORT CSV ──────────────────────────────────────────────
        if ($action === 'export') {
            $rows = $db->fetchAll(
                "SELECT u.fullname, u.email, u.phone, u.city,
                        COUNT(DISTINCT o.id) AS order_count,
                        COALESCE(SUM(o.total),0) AS total_spent,
                        MAX(o.created_at) AS last_order_at,
                        u.created_at, IF(u.is_active,'Hoạt động','Bị khóa') AS status
                 FROM users u
                 LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
                 WHERE u.role=0 GROUP BY u.id ORDER BY u.created_at DESC"
            );
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="customers_'.date('Ymd').'.csv"');
            $out = fopen('php://output','w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, ['Họ tên','Email','SĐT','Thành phố','Số đơn','Tổng chi tiêu','Lần mua cuối','Ngày ĐK','Trạng thái']);
            foreach ($rows as $r) fputcsv($out, $r);
            fclose($out); exit;
        }

        // ── MAIN LIST ───────────────────────────────────────────────
        $search  = trim($_GET['s']       ?? '');
        $segment = $_GET['segment']      ?? '';
        $sort    = $_GET['sort']         ?? 'newest';
        $perPage = isset($_GET['per']) && in_array((int)$_GET['per'],[10,20,50]) ? (int)$_GET['per'] : 20;
        $page    = max(1,(int)($_GET['page']??1));

        // Stats với trend so tháng trước
        $stats = $db->fetch(
            "SELECT
               COUNT(*) AS total,
               SUM(is_active=0) AS locked,
               SUM(YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())) AS new_this_month,
               SUM(YEAR(created_at)=YEAR(DATE_SUB(NOW(),INTERVAL 1 MONTH)) AND MONTH(created_at)=MONTH(DATE_SUB(NOW(),INTERVAL 1 MONTH))) AS new_last_month
             FROM users WHERE role=0"
        );
        // VIP count
        $stats['vip'] = (int)$db->fetch(
            "SELECT COUNT(*) AS n FROM users u
             LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
             WHERE u.role=0 GROUP BY u.id HAVING SUM(o.total)>=10000000"
        )['n'] ?? 0;
        // Actually let me fix this - the above won't work properly for count
        $vipCount = (int)$db->query(
            "SELECT COUNT(*) FROM (
               SELECT u.id FROM users u
               LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
               WHERE u.role=0 GROUP BY u.id HAVING COALESCE(SUM(o.total),0)>=10000000
             ) t"
        )->fetchColumn();
        $inactiveCount = (int)$db->query(
            "SELECT COUNT(*) FROM (
               SELECT u.id FROM users u
               LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0
               WHERE u.role=0 GROUP BY u.id
               HAVING MAX(o.created_at) < DATE_SUB(NOW(),INTERVAL 90 DAY)
                   OR (COUNT(o.id)=0 AND MIN(u.created_at) < DATE_SUB(NOW(),INTERVAL 30 DAY))
             ) t"
        )->fetchColumn();
        $stats['vip']      = $vipCount;
        $stats['inactive'] = $inactiveCount;

        // Segment counts for filter bar
        $segCounts = [];
        $segCounts['all']      = (int)$db->query("SELECT COUNT(*) FROM users WHERE role=0")->fetchColumn();
        $segCounts['vip']      = $vipCount;
        $segCounts['inactive'] = $inactiveCount;
        $segCounts['new']      = (int)$db->query("SELECT COUNT(*) FROM users WHERE role=0 AND created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)")->fetchColumn();
        $segCounts['loyal']    = (int)$db->query(
            "SELECT COUNT(*) FROM (
               SELECT u.id FROM users u
               LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
               WHERE u.role=0 GROUP BY u.id HAVING COUNT(DISTINCT o.id)>=5 AND COALESCE(SUM(o.total),0)<10000000
             ) t"
        )->fetchColumn();
        $segCounts['locked']   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role=0 AND is_active=0")->fetchColumn();

        // Build main query
        $having = [];
        $where  = ['u.role=0'];
        $wParams = [];

        if ($search) {
            $where[]   = '(u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $wParams[] = "%{$search}%"; $wParams[] = "%{$search}%"; $wParams[] = "%{$search}%";
        }

        $orderBy = 'u.created_at DESC';
        if ($sort === 'spent')  $orderBy = 'total_spent DESC';
        if ($sort === 'orders') $orderBy = 'order_count DESC';

        $ws = implode(' AND ', $where);

        // Segment HAVING filter
        $havingStr = '';
        if ($segment === 'vip')      $havingStr = 'HAVING COALESCE(SUM(o.total),0) >= 10000000';
        if ($segment === 'loyal')    $havingStr = 'HAVING COUNT(DISTINCT o.id) >= 5 AND COALESCE(SUM(o.total),0) < 10000000';
        if ($segment === 'new')      { $where[] = "u.created_at >= DATE_SUB(NOW(),INTERVAL 30 DAY)"; $ws = implode(' AND ',$where); }
        if ($segment === 'inactive') $havingStr = 'HAVING (MAX(o.created_at) < DATE_SUB(NOW(),INTERVAL 90 DAY) OR (COUNT(o.id)=0 AND MIN(u.created_at) < DATE_SUB(NOW(),INTERVAL 30 DAY)))';
        if ($segment === 'locked')   { $where[] = 'u.is_active=0'; $ws = implode(' AND ',$where); }

        $baseQ = "FROM users u
                  LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
                  WHERE {$ws}
                  GROUP BY u.id {$havingStr}";

        $offset = ($page-1)*$perPage;
        $customers = $db->fetchAll(
            "SELECT u.id, u.fullname, u.email, u.phone, u.city, u.is_active, u.created_at,
                    COUNT(DISTINCT o.id) AS order_count,
                    COALESCE(SUM(o.total),0) AS total_spent,
                    MAX(o.created_at) AS last_order_at
             {$baseQ} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $wParams
        );
        $totalCustomers  = (int)$db->query("SELECT COUNT(*) FROM (SELECT u.id {$baseQ}) t", $wParams)->fetchColumn();
        $totalPagesAdmin = (int)ceil($totalCustomers / $perPage);
        $pageTitle       = 'Quản lý khách hàng';
        include __DIR__.'/../Views/admin/customers.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Orders
    // ─────────────────────────────────────────────────────────────────
    public function orders($action = null): void {
        $this->check();
        $om       = new OrderModel();
        $db       = Database::getInstance();
        $search   = trim($_GET['s']         ?? '');
        $status   = $_GET['status']         ?? '';
        $page     = max(1, (int)($_GET['page']      ?? 1));
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to']   ?? '');

        // One-way status transition map
        $statusTransitions = array(
            'pending'         => array('confirmed', 'cancelled'),
            'pending_payment' => array('confirmed', 'cancelled'),
            'confirmed'       => array('shipping',  'cancelled'),
            'shipping'        => array('delivered'),
            'delivered'       => array(),
            'cancelled'       => array(),
        );

        if ($action === 'status') {
            if (RoleGuard::role() === RoleGuard::STAFF) {
                RoleGuard::deny('Staff không có quyền cập nhật trạng thái đơn hàng.');
            }
            $isAjax    = !empty($_GET['ajax']);
            $id        = (int)($_GET['id'] ?? 0);
            $newStatus = sanitize($_POST['status'] ?? '');
            $oldOrder  = $om->getWithItems($id);
            $oldStatus = $oldOrder['status'] ?? 'pending';
            $allowed   = $statusTransitions[$oldStatus] ?? array();
            if (!in_array($newStatus, $allowed)) {
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'error'=>'Chuyển trạng thái không hợp lệ.')); return; }
                setFlash('error', 'Chuyển trạng thái không hợp lệ.');
                header('Location:' . APP_URL . '/admin/orders'); exit;
            }
            $om->updateStatus($id, $newStatus);
            if ($oldOrder) {
                Logger::log('UPDATE', 'orders', $id,
                    array('order_code' => $oldOrder['order_code'] ?? '', 'fullname' => $oldOrder['fullname'] ?? '', 'status' => $oldStatus),
                    array('order_code' => $oldOrder['order_code'] ?? '', 'fullname' => $oldOrder['fullname'] ?? '', 'status' => $newStatus)
                );
            }
            try {
                $mf = __DIR__.'/../Helpers/MailService.php';
                if (file_exists($mf)) { require_once $mf; }
                $od = $om->getWithItems($id);
                if ($od && class_exists('MailService')) {
                    @MailService::sendOrderStatusUpdate($od, $newStatus);
                }
            } catch (Exception $e) {}
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(array('success'=>true,'new_status'=>$newStatus)); return; }
            setFlash('success', 'Cập nhật trạng thái!');
            header('Location:' . APP_URL . '/admin/orders'); exit;
        }

        if ($action === 'detail') {
            $id    = (int)($_GET['id'] ?? 0);
            $order = $om->getWithItems($id);
            $pageTitle = 'Chi tiết đơn';
            include __DIR__.'/../Views/admin/order_detail.php'; return;
        }

        // Today's stats
        $stats = $db->fetch(
            "SELECT
                SUM(DATE(created_at)=CURDATE()) AS today_total,
                SUM(DATE(created_at)=CURDATE() AND status='pending') AS today_pending,
                SUM(DATE(created_at)=CURDATE() AND status IN ('confirmed','shipping')) AS today_processing,
                SUM(DATE(created_at)=CURDATE() AND status='delivered') AS today_delivered
             FROM orders WHERE is_deleted=0"
        );

        // Max order id for SSE baseline
        $maxIdRow     = $db->fetch("SELECT MAX(id) AS m FROM orders WHERE is_deleted=0");
        $maxOrderId   = (int)($maxIdRow['m'] ?? 0);

        // Build filterable query
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $where  = 'WHERE o.is_deleted=0';
        $params = array();
        if ($search)   { $where .= ' AND (o.order_code LIKE ? OR o.fullname LIKE ? OR o.phone LIKE ?)'; array_push($params, "%$search%", "%$search%", "%$search%"); }
        if ($status)   { $where .= ' AND o.status=?';            $params[] = $status; }
        if ($dateFrom) { $where .= ' AND DATE(o.created_at)>=?'; $params[] = $dateFrom; }
        if ($dateTo)   { $where .= ' AND DATE(o.created_at)<=?'; $params[] = $dateTo; }

        $orders = $db->fetchAll(
            "SELECT o.*,(SELECT COUNT(*) FROM order_details WHERE order_id=o.id) AS item_count
             FROM orders o $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $cntRow          = $db->fetch("SELECT COUNT(*) AS c FROM orders o $where", $params);
        $totalPagesAdmin = (int)ceil(($cntRow['c'] ?? 0) / $limit);

        $pageTitle = 'Quản lý đơn hàng';
        include __DIR__.'/../Views/admin/orders.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Inventory
    // ─────────────────────────────────────────────────────────────────
    public function inventory($action = null): void {
        $this->check();
        if (RoleGuard::role() === RoleGuard::STAFF) {
            RoleGuard::deny('Staff không có quyền quản lý kho.');
        }
        $db = Database::getInstance();

        // AJAX inline update (stock or min_stock)
        if ($action === 'ajax-update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $pid    = (int)($_POST['product_id'] ?? 0);
            $field  = $_POST['field'] ?? 'stock';
            $oldRow = $db->fetch("SELECT stock_quantity, min_stock FROM inventory WHERE product_id=?", array($pid));
            $prod   = $db->fetch("SELECT name FROM products WHERE id=?", array($pid));
            if (!$oldRow || !$prod) { echo json_encode(array('success'=>false,'message'=>'Không tìm thấy')); exit; }
            if ($field === 'min') {
                $val = max(0, (int)($_POST['min_stock'] ?? 0));
                $db->query("UPDATE inventory SET min_stock=? WHERE product_id=?", array($val, $pid));
                Logger::log('UPDATE','inventory',$pid,
                    array('product_name'=>$prod['name'],'min_stock'=>$oldRow['min_stock']),
                    array('product_name'=>$prod['name'],'min_stock'=>$val));
                $newMin = $val;
            } else {
                $val = max(0, (int)($_POST['quantity'] ?? 0));
                $db->query("UPDATE inventory SET stock_quantity=?,last_restocked=NOW() WHERE product_id=?", array($val, $pid));
                $db->query("UPDATE products SET stock=? WHERE id=?", array($val, $pid));
                Logger::log('UPDATE','inventory',$pid,
                    array('product_name'=>$prod['name'],'stock_quantity'=>$oldRow['stock_quantity']),
                    array('product_name'=>$prod['name'],'stock_quantity'=>$val));
                $newMinRow = $db->fetch("SELECT min_stock FROM inventory WHERE product_id=?", array($pid));
                $newMin = (int)($newMinRow['min_stock'] ?? 5);
            }
            echo json_encode(array('success'=>true,'value'=>$val,'min_stock'=>$newMin));
            exit;
        }

        // Bulk update
        if ($action === 'bulk-update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? array())));
            $qty = max(0, (int)($_POST['quantity'] ?? 0));
            foreach ($ids as $pid) {
                $oldRow = $db->fetch("SELECT stock_quantity FROM inventory WHERE product_id=?", array($pid));
                $prod   = $db->fetch("SELECT name FROM products WHERE id=?", array($pid));
                $db->query("UPDATE inventory SET stock_quantity=?,last_restocked=NOW() WHERE product_id=?", array($qty, $pid));
                $db->query("UPDATE products SET stock=? WHERE id=?", array($qty, $pid));
                if ($oldRow) Logger::log('UPDATE','inventory',$pid,
                    array('product_name'=>$prod['name']??'','stock_quantity'=>$oldRow['stock_quantity']),
                    array('product_name'=>$prod['name']??'','stock_quantity'=>$qty));
            }
            echo json_encode(array('success'=>true,'updated'=>count($ids)));
            exit;
        }

        // Legacy form update (backward compat)
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $pid    = (int)($_POST['product_id'] ?? 0);
            $qty    = (int)($_POST['quantity']    ?? 0);
            $oldRow = $db->fetch("SELECT stock_quantity FROM inventory WHERE product_id=?", array($pid));
            $product= $db->fetch("SELECT name FROM products WHERE id=?", array($pid));
            $db->query("UPDATE inventory SET stock_quantity=?,last_restocked=NOW() WHERE product_id=?", array($qty, $pid));
            $db->query("UPDATE products SET stock=? WHERE id=?", array($qty, $pid));
            if ($oldRow) Logger::log('UPDATE','inventory',$pid,
                array('product_name'=>$product['name']??'','stock_quantity'=>$oldRow['stock_quantity']),
                array('product_name'=>$product['name']??'','stock_quantity'=>$qty));
            setFlash('success', 'Đã cập nhật kho!');
            header('Location:' . APP_URL . '/admin/inventory'); exit;
        }

        // Filters
        $search  = trim($_GET['s']      ?? '');
        $catId   = (int)($_GET['cat']   ?? 0);
        $statusF = trim($_GET['status'] ?? '');

        $where  = 'WHERE p.is_deleted=0';
        $params = array();
        if ($search) { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
        if ($catId)  { $where .= ' AND p.category_id=?'; $params[] = $catId; }
        if ($statusF === 'out') $where .= ' AND i.stock_quantity<=0';
        elseif ($statusF === 'low') $where .= ' AND i.stock_quantity>0 AND i.stock_quantity<=i.min_stock';
        elseif ($statusF === 'ok')  $where .= ' AND i.stock_quantity>i.min_stock';

        // CSV export
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $rows = $db->fetchAll(
                "SELECT p.name,p.sku,c.name AS cat_name,i.stock_quantity,i.min_stock,i.last_restocked
                 FROM inventory i JOIN products p ON i.product_id=p.id
                 JOIN categories c ON p.category_id=c.id $where ORDER BY i.stock_quantity ASC",
                $params
            );
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="inventory_'.date('Y-m-d').'.csv"');
            $out = fopen('php://output','w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array('Sản phẩm','SKU','Danh mục','Tồn kho','Tối thiểu','Cập nhật cuối'));
            foreach ($rows as $r) fputcsv($out, array($r['name'],$r['sku']??'',$r['cat_name'],$r['stock_quantity'],$r['min_stock'],$r['last_restocked']??''));
            fclose($out); exit;
        }

        $inventory = $db->fetchAll(
            "SELECT i.*, p.name, p.sku, p.price, c.name AS cat_name, c.id AS cat_id
             FROM inventory i
             JOIN products p ON i.product_id=p.id
             JOIN categories c ON p.category_id=c.id
             $where ORDER BY i.stock_quantity ASC",
            $params
        );

        // Summary stats (unfiltered)
        $summary = $db->fetch(
            "SELECT COUNT(*) total_sku,
                    SUM(i.stock_quantity<=0) out_cnt,
                    SUM(i.stock_quantity>0 AND i.stock_quantity<=i.min_stock) low_cnt,
                    SUM(i.stock_quantity * COALESCE(p.price,0)) total_value
             FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0"
        );
        $categories = $db->fetchAll("SELECT id, name FROM categories ORDER BY name");
        $restockLog = $db->fetchAll(
            "SELECT l.created_at, l.user_name, l.old_data, l.new_data
             FROM action_logs l WHERE l.table_name='inventory' AND l.action='UPDATE'
             ORDER BY l.id DESC LIMIT 25"
        );

        $pageTitle = 'Quản lý kho';
        include __DIR__.'/../Views/admin/inventory.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Stats
    // ─────────────────────────────────────────────────────────────────
    public function stats($p = null): void {
        $this->check();
        if (RoleGuard::role() === RoleGuard::STAFF) {
            RoleGuard::deny('Staff không có quyền xem thống kê.');
        }
        $om         = new OrderModel();
        $stats      = $om->getStats();
        $db         = Database::getInstance();
        $yearlyData = $db->fetchAll(
            "SELECT MONTH(created_at) m, COALESCE(SUM(total),0) rev, COUNT(*) cnt
             FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND status!='cancelled' AND is_deleted=0
             GROUP BY m ORDER BY m"
        );
        $topCats = $db->fetchAll(
            "SELECT c.name, SUM(od.quantity) total_sold
             FROM order_details od
             JOIN products p ON od.product_id=p.id
             JOIN categories c ON p.category_id=c.id
             JOIN orders o ON od.order_id=o.id
             WHERE o.status!='cancelled' AND o.is_deleted=0
             GROUP BY c.id ORDER BY total_sold DESC LIMIT 6"
        );

        // CSV export
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="thongke_' . date('Y-m') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, array('Tháng', 'Số đơn', 'Doanh thu (đ)', 'TB/đơn (đ)'));
            foreach ($yearlyData as $r) {
                $avg = $r['cnt'] > 0 ? round($r['rev'] / $r['cnt']) : 0;
                fputcsv($out, array('T'.$r['m'], $r['cnt'], $r['rev'], $avg));
            }
            fclose($out);
            exit;
        }

        // Last 7 days revenue (for line chart)
        $last7Raw = $db->fetchAll(
            "SELECT DATE(created_at) d, COALESCE(SUM(total),0) rev, COUNT(*) cnt
             FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             AND status!='cancelled' AND is_deleted=0
             GROUP BY DATE(created_at) ORDER BY d"
        );
        // Normalize into 7-slot arrays
        $last7Labels = array(); $last7Rev = array(); $last7Cnt = array();
        $dayMap = array();
        foreach ($last7Raw as $r) { $dayMap[$r['d']] = $r; }
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $last7Labels[] = date('d/m', strtotime($day));
            $last7Rev[]    = isset($dayMap[$day]) ? (float)$dayMap[$day]['rev'] : 0;
            $last7Cnt[]    = isset($dayMap[$day]) ? (int)$dayMap[$day]['cnt']   : 0;
        }

        // Top 5 products by revenue
        $topProducts = $db->fetchAll(
            "SELECT p.name, SUM(od.quantity) qty, SUM(od.quantity * od.price) rev
             FROM order_details od
             JOIN products p ON od.product_id=p.id
             JOIN orders o ON od.order_id=o.id
             WHERE o.status!='cancelled' AND o.is_deleted=0
             GROUP BY od.product_id ORDER BY rev DESC LIMIT 5"
        );

        // New customers this month
        $newCustRow    = $db->fetch("SELECT COUNT(*) c FROM users WHERE role=0 AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())");
        $newCustomers  = (int)($newCustRow['c'] ?? 0);

        // Low stock count
        $lowStockRow   = $db->fetch("SELECT COUNT(*) c FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0 WHERE i.stock_quantity<=i.min_stock");
        $lowStockCount = (int)($lowStockRow['c'] ?? 0);

        // Last month stats (for comparison)
        $lastMonthRow  = $db->fetch(
            "SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM orders
             WHERE MONTH(created_at)=MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
             AND YEAR(created_at)=YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
             AND status!='cancelled' AND is_deleted=0"
        );
        $lastMonthRev  = (float)($lastMonthRow['rev'] ?? 0);
        $lastMonthCnt  = (int)($lastMonthRow['cnt']   ?? 0);

        // Month-over-month % change
        $monthRevPct = $lastMonthRev > 0
            ? round(($stats['month']['rev'] - $lastMonthRev) / $lastMonthRev * 100, 1)
            : ($stats['month']['rev'] > 0 ? 100 : 0);
        $monthCntPct = $lastMonthCnt > 0
            ? round(($stats['month']['cnt'] - $lastMonthCnt) / $lastMonthCnt * 100, 1)
            : ($stats['month']['cnt'] > 0 ? 100 : 0);

        // Conversion rate: non-cancelled / all orders this month
        $allMonthRow  = $db->fetch("SELECT COUNT(*) c FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND is_deleted=0");
        $allMonthCnt  = (int)($allMonthRow['c'] ?? 0);
        $convRate     = $allMonthCnt > 0 ? round($stats['month']['cnt'] / $allMonthCnt * 100, 1) : 0;

        // AOV this month
        $monthAOV = $stats['month']['cnt'] > 0 ? round($stats['month']['rev'] / $stats['month']['cnt']) : 0;

        $pageTitle = 'Thống kê doanh số';
        include __DIR__.'/../Views/admin/stats.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Activity Logs  (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function logs($p = null): void {
        $this->checkAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'action'  => $_GET['action'] ?? '',
            'table'   => $_GET['table']  ?? '',
            'user_id' => (int)($_GET['uid'] ?? 0) ?: null,
        ];
        $logs            = Logger::getAll($page, 50, $filters);
        $totalLogs       = Logger::count($filters);
        $totalPagesAdmin = (int)ceil($totalLogs / 50);
        $pageTitle       = 'Nhật ký hoạt động';
        include __DIR__.'/../Views/admin/logs.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Telegram Bot Console (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function telegramBot($p = null): void {
        $this->checkAdmin();
        $pageTitle = 'Telegram Bot';
        include __DIR__ . '/../Views/admin/telegram_bot.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // AI Report  (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function aiReport($action = null): void {
        $this->checkAdmin();
        require_once __DIR__ . '/../Helpers/AIInsight.php';
        require_once __DIR__ . '/../Helpers/TelegramNotifier.php';
        require_once __DIR__ . '/../Helpers/ZaloNotifier.php';

        // POST: generate new report
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoursBack = (int)($_POST['hours'] ?? 24);
            $hoursBack = max(1, min(168, $hoursBack)); // 1h – 7 ngày
            $data      = AIInsight::generateAndCache($hoursBack);

            // Optionally send via Telegram / Zalo
            $tgSent = false;
            if (!empty($_POST['send_telegram']) && $data['success']) {
                $tgSent = TelegramNotifier::notifyDailyReport($data['text']);
            }
            if (!empty($_POST['send_zalo']) && $data['success'] && ZaloNotifier::isConfigured()) {
                ZaloNotifier::notifyDailyReport($data['text']);
            }

            if ($tgSent) {
                $_SESSION['flash'] = array(
                    'type' => 'success_center',
                    'icon' => '✈️',
                    'msg'  => 'Đã gửi Telegram!',
                    'sub'  => 'Báo cáo AI đã được gửi đến bot @TuanHuyComputerBot',
                );
            } else {
                setFlash('success', 'Đã tạo báo cáo AI!');
            }
            header('Location:' . APP_URL . '/admin/ai-report'); exit;
        }

        $cached      = AIInsight::getCached();
        $tgReady     = TelegramNotifier::isConfigured();
        $zaloReady   = ZaloNotifier::isConfigured();
        $pageTitle   = 'Báo cáo AI';
        include __DIR__ . '/../Views/admin/ai_report.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // AI Generator
    // ─────────────────────────────────────────────────────────────────
    public function ai($action = null): void {
        $this->check();
        if ($action === 'generator') {
            $categories = (new CategoryModel())->getForAdmin();
            $brands     = Database::getInstance()->fetchAll("SELECT * FROM brands ORDER BY name");
            $pageTitle  = 'AI Content Generator';
            include __DIR__.'/../Views/admin/ai_generator.php'; return;
        }
        header('Location:' . APP_URL . '/admin/ai/generator'); exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────
    private function getProdPost(): array {
        $specs = null;
        if (!empty($_POST['spec_key']) && is_array($_POST['spec_key'])) {
            $specs = [];
            foreach ($_POST['spec_key'] as $i => $k) {
                if ($k && isset($_POST['spec_val'][$i]))
                    $specs[sanitize($k)] = sanitize($_POST['spec_val'][$i]);
            }
            $specs = !empty($specs) ? json_encode($specs, JSON_UNESCAPED_UNICODE) : null;
        }
        return [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'brand_id'    => (int)($_POST['brand_id']    ?? 0) ?: null,
            'name'        => sanitize($_POST['name']       ?? ''),
            'slug'        => makeSlug($_POST['name'] ?? '') . '-' . rand(100, 999),
            'sku'         => sanitize($_POST['sku']        ?? ''),
            'short_desc'  => sanitize($_POST['short_desc'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'specs'       => $specs,
            'price'       => (float)str_replace(',', '', $_POST['price']      ?? 0),
            'sale_price'  => isset($_POST['sale_price']) && $_POST['sale_price'] > 0
                             ? (float)str_replace(',', '', $_POST['sale_price']) : null,
            'stock'       => (int)($_POST['stock']      ?? 0),
            'is_featured' => (int)!empty($_POST['is_featured']),
            'is_new'      => (int)!empty($_POST['is_new']),
            'warranty'    => (int)($_POST['warranty']   ?? 12),
        ];
    }

    private function handleUpload(): ?string {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $f   = $_FILES['image'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return null;
            if ($f['size'] > 5 * 1024 * 1024) return null;
            $name = uniqid('prod_') . '.' . $ext;
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            if (move_uploaded_file($f['tmp_name'], UPLOAD_PATH . $name)) return $name;
        }
        if (!empty($_POST['image_presaved'])) {
            $fname = basename($_POST['image_presaved']);
            if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fname) && file_exists(UPLOAD_PATH . $fname))
                return $fname;
        }
        if (!empty($_POST['image_base64'])) {
            $b64  = preg_replace('/^data:[^;]+;base64,/i', '', $_POST['image_base64']);
            $b64  = preg_replace('/\s+/', '', $b64);
            $dec  = base64_decode($b64, true);
            if (!$dec) return null;
            $mime = $_POST['image_base64_mime'] ?? 'image/jpeg';
            $extM = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
            $ext  = $extM[$mime] ?? 'jpg';
            $name = uniqid('prod_paste_') . '.' . $ext;
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            if (file_put_contents(UPLOAD_PATH . $name, $dec)) return $name;
        }
        return null;
    }

    private function handleExtraImages(ProductModel $pm, int $productId): void {
        if (empty($_FILES['extra_images']) || empty($_FILES['extra_images']['name'][0])) return;
        $files = $_FILES['extra_images'];
        $count = count($files['name']);
        if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
            if ($files['size'][$i] > 5 * 1024 * 1024) continue;
            $fname = 'prod_extra_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_PATH . $fname))
                $pm->addImage($productId, $fname, $i);
        }
    }

    private function handleExtraImagesPresaved(ProductModel $pm, int $productId): void {
        if (empty($_POST['extra_presaved']) || !is_array($_POST['extra_presaved'])) return;
        foreach ($_POST['extra_presaved'] as $i => $raw) {
            $fname = basename($raw);
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fname)) continue;
            if (!file_exists(UPLOAD_PATH . $fname)) continue;
            $pm->addImage($productId, $fname, (int)$i + 100);
        }
    }

    private function handleExtraImagesB64(ProductModel $pm, int $productId): void {
        if (empty($_POST['extra_b64']) || !is_array($_POST['extra_b64'])) return;
        if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
        foreach ($_POST['extra_b64'] as $i => $b64) {
            $mime = $_POST['extra_mime'][$i] ?? 'image/jpeg';
            $raw  = preg_replace('/^data:[^;]+;base64,/i', '', $b64);
            $dec  = base64_decode(str_replace(' ', '+', $raw), true);
            if (!$dec || strlen($dec) < 100) continue;
            $extM = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
            $ext  = $extM[$mime] ?? 'jpg';
            $fname = 'prod_extra_' . uniqid() . '.' . $ext;
            if (file_put_contents(UPLOAD_PATH . $fname, $dec))
                $pm->addImage($productId, $fname, (int)$i);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Asset Manager  (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function assets($p = null): void {
        $this->checkAdmin();
        $dir = __DIR__ . '/../../assets/images/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $json      = $dir . 'approved.json';
        $approved  = file_exists($json) ? (json_decode(file_get_contents($json), true) ?: []) : [];
        $hasBackup = file_exists(__DIR__ . '/../Views/home/index.php.bak');
        $bannersFile = __DIR__ . '/../../storage/banners.json';
        $banners     = file_exists($bannersFile) ? (json_decode(file_get_contents($bannersFile), true) ?: []) : [];
        $pageTitle = 'Asset Manager';
        include __DIR__ . '/../Views/admin/assets.php';
    }

    public function bannerSave($p = null): void {
        $this->checkAdmin();
        header('Content-Type: application/json');
        $slot       = $_POST['slot'] ?? '';
        $validSlots = ['main-banner-1','main-banner-2','main-banner-3','side-banner-1','side-banner-2'];
        if (!in_array($slot, $validSlots, true)) {
            echo json_encode(['ok'=>false,'message'=>'Slot không hợp lệ']); exit;
        }
        // meta_only: just update label/title/link, keep existing image
        $metaOnly = !empty($_POST['meta_only']);
        $bannerDir = __DIR__ . '/../../assets/images/banners/';
        if (!is_dir($bannerDir)) mkdir($bannerDir, 0755, true);
        $savedUrl = ''; $filename = '';
        if ($metaOnly) {
            // Read existing img from banners.json
            $bfTmp = __DIR__ . '/../../storage/banners.json';
            $bjTmp = file_exists($bfTmp) ? (json_decode(file_get_contents($bfTmp), true) ?: []) : [];
            if (strpos($slot, 'main-banner-') === 0) {
                $idxTmp = (int)substr($slot, strlen('main-banner-')) - 1;
                $savedUrl = $bjTmp['main'][$idxTmp]['img'] ?? '';
            } else {
                $idxTmp = $slot === 'side-banner-1' ? 0 : 1;
                $savedUrl = $bjTmp['side'][$idxTmp]['img'] ?? '';
            }
            if (!$savedUrl) { echo json_encode(['ok'=>false,'message'=>'Chưa có ảnh để cập nhật metadata']); exit; }
        } elseif (!empty($_FILES['file']['tmp_name'])) {
            $f   = $_FILES['file'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
                echo json_encode(['ok'=>false,'message'=>'Định dạng không hợp lệ']); exit;
            }
            $filename = $slot . '.' . $ext;
            move_uploaded_file($f['tmp_name'], $bannerDir . $filename);
            $savedUrl = APP_URL . '/assets/images/banners/' . $filename . '?t=' . time();
        } elseif (!empty($_POST['image_b64'])) {
            $mime  = $_POST['image_mime'] ?? 'image/jpeg';
            $ext   = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
            $data  = base64_decode(preg_replace('#^data:[^;]+;base64,#', '', $_POST['image_b64']));
            $filename = $slot . '.' . $ext;
            file_put_contents($bannerDir . $filename, $data);
            $savedUrl = APP_URL . '/assets/images/banners/' . $filename . '?t=' . time();
        } elseif (!empty($_POST['url'])) {
            $data = @file_get_contents($_POST['url']);
            if ($data) {
                $filename = $slot . '.jpg';
                file_put_contents($bannerDir . $filename, $data);
                $savedUrl = APP_URL . '/assets/images/banners/' . $filename . '?t=' . time();
            } else { $savedUrl = $_POST['url']; }
        } else {
            echo json_encode(['ok'=>false,'message'=>'Không có ảnh']); exit;
        }
        // Ensure storage/ directory exists and is writable
        $storageDir = __DIR__ . '/../../storage/';
        if (!is_dir($storageDir)) {
            if (!mkdir($storageDir, 0755, true)) {
                error_log('[bannerSave] Cannot create storage dir: ' . $storageDir);
                echo json_encode(['ok'=>false,'message'=>'Không tạo được thư mục storage/']); exit;
            }
        }
        $bf    = realpath($storageDir) . DIRECTORY_SEPARATOR . 'banners.json';
        $bj    = file_exists($bf) ? (json_decode(file_get_contents($bf), true) ?: []) : [];
        $label = htmlspecialchars(strip_tags($_POST['label'] ?? ''), ENT_QUOTES);
        $title = htmlspecialchars(strip_tags($_POST['title'] ?? ''), ENT_QUOTES);
        $link  = filter_var($_POST['link'] ?? '', FILTER_SANITIZE_URL);
        $entry = ['img'=>$savedUrl,'label'=>$label,'title'=>$title,'url'=>$link,'updated_at'=>date('Y-m-d H:i:s')];
        if (strpos($slot, 'main-banner-') === 0) {
            $idx = (int)substr($slot, strlen('main-banner-')) - 1;
            if (!isset($bj['main'])) $bj['main'] = [];
            while (count($bj['main']) <= $idx) $bj['main'][] = [];
            $bj['main'][$idx] = array_merge($bj['main'][$idx] ?: [], $entry);
        } else {
            $idx = $slot === 'side-banner-1' ? 0 : 1;
            if (!isset($bj['side'])) $bj['side'] = [];
            while (count($bj['side']) <= $idx) $bj['side'][] = [];
            $bj['side'][$idx] = array_merge($bj['side'][$idx] ?: [], $entry);
        }
        $jsonOut = json_encode($bj, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        error_log('[bannerSave] writing to: ' . $bf);
        error_log('[bannerSave] content: ' . $jsonOut);
        $written = file_put_contents($bf, $jsonOut);
        if ($written === false) {
            error_log('[bannerSave] FAILED | is_writable(dir)=' . (is_writable(dirname($bf)) ? 'yes' : 'no'));
            echo json_encode(['ok'=>false,'message'=>'Không ghi được banners.json — path: '.$bf.' writable:' .(is_writable(dirname($bf))?'yes':'no')]); exit;
        }
        error_log('[bannerSave] OK slot=' . $slot . ' bytes=' . $written);
        echo json_encode(['ok'=>true,'url'=>$savedUrl,'path'=>$bf,'bytes'=>$written,'json_preview'=>substr($jsonOut,0,200)]); exit;
    }

    public function bannerDebug($p = null): void {
        $this->checkAdmin();
        header('Content-Type: application/json; charset=utf-8');
        $bf = __DIR__ . '/../../storage/banners.json';
        if (!file_exists($bf)) { echo json_encode(['_meta'=>['exists'=>false,'path'=>realpath(dirname($bf)).DIRECTORY_SEPARATOR.'banners.json','writable'=>is_writable(dirname($bf))]]); exit; }
        $content = file_get_contents($bf);
        $data    = json_decode($content, true) ?: [];
        $data['_meta'] = ['exists'=>true,'path'=>realpath($bf),'size'=>strlen($content),'mtime'=>date('Y-m-d H:i:s',filemtime($bf)),'writable'=>is_writable($bf)];
        echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); exit;
    }

    public function bannerRemove($p = null): void {
        $this->checkAdmin();
        header('Content-Type: application/json');
        $slot = $_POST['slot'] ?? '';
        $validSlots = ['main-banner-1','main-banner-2','main-banner-3','side-banner-1','side-banner-2'];
        if (!in_array($slot, $validSlots, true)) {
            echo json_encode(['ok'=>false,'message'=>'Slot không hợp lệ']); exit;
        }
        $bf = __DIR__ . '/../../storage/banners.json';
        $bj = file_exists($bf) ? (json_decode(file_get_contents($bf), true) ?: []) : [];
        if (strpos($slot, 'main-banner-') === 0) {
            $idx = (int)substr($slot, strlen('main-banner-')) - 1;
            if (isset($bj['main'][$idx])) $bj['main'][$idx] = [];
        } else {
            $idx = $slot === 'side-banner-1' ? 0 : 1;
            if (isset($bj['side'][$idx])) $bj['side'][$idx] = [];
        }
        file_put_contents($bf, json_encode($bj, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        echo json_encode(['ok'=>true]); exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // AI Assistant  (Admin only)
    // ─────────────────────────────────────────────────────────────────
    public function aiAssistant($p = null): void {
        // Buffer captures ALL output (PHP notices, require_once side-effects, etc.)
        // so nothing can bleed into the JSON response.
        ob_start();

        // ── GET: progress polling ─────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['progress'])) {
            $file = dirname(__DIR__, 2) . '/storage/ai_progress.json';
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo file_exists($file) ? file_get_contents($file) : '{"active":false}';
            return;
        }

        // ── POST: JSON API ────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            set_time_limit(120);
            error_reporting(0);
            ini_set('display_errors', 0);

            // Helper: send JSON and terminate (bypasses any outer ob_start from index.php)
            $sendJson = function($data) {
                while (ob_get_level()) ob_end_clean();
                if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
                $out = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                echo $out ?: '{"ok":false,"reply":"Lỗi mã hoá phản hồi","action":"chat"}';
                exit();
            };

            try {
                $this->checkAdmin();
            } catch (Throwable $e) {
                $sendJson(array('ok' => false, 'reply' => 'Không có quyền.', 'action' => 'chat'));
            }
            try {
                ob_start();
                require_once __DIR__ . '/TelegramBotController.php';
                ob_end_clean();
                $body = json_decode(file_get_contents('php://input'), true);
                $msg  = trim(($body['message'] ?? ''));
                if (!$msg) {
                    $sendJson(array('ok' => false, 'reply' => '', 'action' => 'chat'));
                }
                $bot  = new TelegramBotController();
                ob_start();
                $resp = $bot->processMessageWeb($msg);
                ob_end_clean();
                @file_put_contents(dirname(__DIR__, 2) . '/storage/ai_raw.txt',
                    json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                if (empty($resp) || $resp === false) {
                    $sendJson(array('ok' => false, 'reply' => 'API không phản hồi', 'action' => 'chat'));
                }
                $sendJson($resp);
            } catch (Throwable $e) {
                $sendJson(array('ok' => false, 'reply' => 'Lỗi: ' . $e->getMessage(), 'action' => 'chat'));
            }
        }

        // ── GET: render page ──────────────────────────────────────────
        try {
            $this->checkAdmin();
            require_once __DIR__ . '/TelegramBotController.php';
            $db             = Database::getInstance();
            $totalProducts  = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND is_deleted=0")->fetchColumn();
            $totalOrders    = (int)$db->query("SELECT COUNT(*) FROM orders WHERE is_deleted=0")->fetchColumn();
            $pendingOrders  = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending' AND is_deleted=0")->fetchColumn();
            $totalCustomers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role=0")->fetchColumn();
            $todayRevRow    = $db->fetch("SELECT COALESCE(SUM(total),0) AS r FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled' AND is_deleted=0");
            $todayRevenue   = (float)($todayRevRow['r'] ?? 0);
            $pageTitle      = 'AI Assistant';
            ob_end_clean();
            include __DIR__ . '/../Views/admin/ai_assistant.php';
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Admin API (JSON endpoints for admin UI)
    // ─────────────────────────────────────────────────────────────────
    public function api($action = null): void {
        $this->check();
        header('Content-Type: application/json');

        if ($action === 'new-orders-count') {
            $since   = trim($_GET['since'] ?? '');
            $db      = Database::getInstance();
            $sinceTs = $since
                ? date('Y-m-d H:i:s', (int)$since)
                : date('Y-m-d H:i:s', strtotime('-1 hour'));

            $row = $db->fetch(
                "SELECT COUNT(*) AS c FROM orders WHERE created_at > ? AND status='pending' AND is_deleted=0",
                array($sinceTs)
            );
            try {
                $orders = $db->fetchAll(
                    "SELECT o.id, o.order_code, o.fullname, o.total, o.created_at,
                            oi.product_name, oi.quantity, oi.price,
                            p.image AS product_image
                     FROM orders o
                     LEFT JOIN order_details oi ON oi.id = (
                         SELECT MIN(id) FROM order_details WHERE order_id = o.id
                     )
                     LEFT JOIN products p ON p.id = oi.product_id
                     WHERE o.created_at > ? AND o.status = 'pending' AND o.is_deleted = 0
                     ORDER BY o.created_at DESC LIMIT 8",
                    array($sinceTs)
                );
            } catch (Exception $e) {
                $orders = array();
                $row    = array('c' => 0);
            }
            echo json_encode(array(
                'success' => true,
                'count'   => (int)($row['c'] ?? 0),
                'orders'  => $orders,
            ));
            return;
        }

        if ($action === 'dashboard-stats') {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT COUNT(*) cnt, COALESCE(SUM(total),0) rev FROM orders
                 WHERE DATE(created_at)=CURDATE() AND status!='cancelled' AND is_deleted=0"
            );
            $pRow = $db->fetch(
                "SELECT COUNT(*) cnt, SUM(TIMESTAMPDIFF(MINUTE,created_at,NOW())>120) old_cnt
                 FROM orders WHERE status='pending' AND is_deleted=0"
            );
            echo json_encode(array(
                'success'     => true,
                'today_rev'   => (float)($row['rev']      ?? 0),
                'today_cnt'   => (int)($row['cnt']         ?? 0),
                'pending_cnt' => (int)($pRow['cnt']        ?? 0),
                'pending_old' => (int)($pRow['old_cnt']    ?? 0),
            ));
            return;
        }

        if ($action === 'confirm-all-pending' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $db      = Database::getInstance();
            $pending = $db->fetchAll("SELECT id FROM orders WHERE status='pending' AND is_deleted=0");
            foreach ($pending as $o) {
                $db->query("UPDATE orders SET status='confirmed',updated_at=NOW() WHERE id=?", array($o['id']));
                Logger::log('UPDATE','orders',$o['id'],array('status'=>'pending'),array('status'=>'confirmed'));
            }
            echo json_encode(array('success'=>true,'confirmed'=>count($pending)));
            return;
        }

        // ── remove-bg-python ──────────────────────────────────────
        if ($action === 'remove-bg-python') {
            $this->checkAdmin();
            ob_start();
            try {
                $b    = json_decode(file_get_contents('php://input'), true);
                if (!is_array($b)) $b = array();
                $data = $b['image'] ?? '';
                if (!$data) {
                    ob_end_clean();
                    echo json_encode(array('success'=>false,'message'=>'Thiếu dữ liệu ảnh')); return;
                }
                if (($pos = strpos($data, 'base64,')) !== false) $data = substr($data, $pos + 7);
                $bytes = base64_decode($data);
                if (!$bytes) {
                    ob_end_clean();
                    echo json_encode(array('success'=>false,'message'=>'Dữ liệu base64 không hợp lệ')); return;
                }
                $tmp  = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
                $fin  = $tmp . 'sc_in_'  . uniqid() . '.png';
                $fout = $tmp . 'sc_out_' . uniqid() . '.png';
                file_put_contents($fin, $bytes);
                $py     = defined('PYTHON_BIN') ? PYTHON_BIN : 'C:\\Users\\THIEN NHI\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe';
                $script = 'C:\\AppServ\\www\\tuanhuy_computer\\scripts\\remove_bg.py';
                $cmd    = escapeshellarg($py).' '.escapeshellarg($script).' '.escapeshellarg($fin).' '.escapeshellarg($fout).' 2>&1';
                $out    = shell_exec($cmd);
                @unlink($fin);
                if (!file_exists($fout)) {
                    ob_end_clean();
                    echo json_encode(array('success'=>false,'message'=>'rembg thất bại: '.trim((string)$out))); return;
                }
                $result = 'data:image/png;base64,'.base64_encode(file_get_contents($fout));
                @unlink($fout);
                ob_end_clean();
                echo json_encode(array('success'=>true,'result'=>$result));
            } catch (Exception $e) {
                $errLog = __DIR__ . '/../../storage/error.log';
                @file_put_contents($errLog, '['.date('Y-m-d H:i:s').'] remove-bg-python: '.$e->getMessage().PHP_EOL, FILE_APPEND);
                ob_end_clean();
                echo json_encode(array('success'=>false,'message'=>'Lỗi máy chủ: '.$e->getMessage()));
            }
            return;
        }

        // ── proxy-image (load external URL server-side) ───────────
        if ($action === 'proxy-image') {
            $this->checkAdmin();
            $url = trim($_GET['url'] ?? '');
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                echo json_encode(array('success'=>false,'message'=>'URL không hợp lệ')); return;
            }
            $ctx  = stream_context_create(array('http'=>array('timeout'=>15,'user_agent'=>'Mozilla/5.0')));
            $data = @file_get_contents($url, false, $ctx);
            if (!$data) { echo json_encode(array('success'=>false,'message'=>'Không tải được ảnh')); return; }
            $mime = 'image/jpeg';
            foreach ((array)($http_response_header ?? array()) as $h) {
                if (stripos($h, 'Content-Type:') === 0) { $mime = trim(explode(':', $h, 2)[1]); }
            }
            echo json_encode(array('success'=>true,'data'=>'data:'.$mime.';base64,'.base64_encode($data)));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
    }

    // ─────────────────────────────────────────────────────────────────
    // Export PDF  (print-ready standalone HTML page)
    // Route: /admin/export-pdf?type=orders|products|customers|inventory|stats&[filters]
    // ─────────────────────────────────────────────────────────────────
    public function exportPdf($p = null): void {
        $this->check();
        $db   = Database::getInstance();
        $type = $_GET['type'] ?? 'orders';

        $title   = '';
        $filters = '';
        $headers = [];
        $rows    = [];

        if ($type === 'orders') {
            $search   = trim($_GET['s']         ?? '');
            $status   = $_GET['status']         ?? '';
            $dateFrom = trim($_GET['date_from'] ?? '');
            $dateTo   = trim($_GET['date_to']   ?? '');
            $statusLabels = ['pending'=>'Chờ XN','pending_payment'=>'Chờ TT','confirmed'=>'Đã XN',
                             'shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy'];
            $title   = 'Danh sách đơn hàng';
            $parts   = [];
            if ($search) $parts[] = 'Tìm: "'.htmlspecialchars($search).'"';
            if ($status) $parts[] = 'Trạng thái: '.($statusLabels[$status] ?? $status);
            if ($dateFrom) $parts[] = 'Từ: '.$dateFrom;
            if ($dateTo)   $parts[] = 'Đến: '.$dateTo;
            $filters = $parts ? implode(' | ', $parts) : 'Tất cả';

            $where = 'WHERE o.is_deleted=0'; $params = [];
            if ($search) { $where .= ' AND (o.order_code LIKE ? OR o.fullname LIKE ? OR o.phone LIKE ?)'; array_push($params, "%$search%", "%$search%", "%$search%"); }
            if ($status)   { $where .= ' AND o.status=?';            $params[] = $status; }
            if ($dateFrom) { $where .= ' AND DATE(o.created_at)>=?'; $params[] = $dateFrom; }
            if ($dateTo)   { $where .= ' AND DATE(o.created_at)<=?'; $params[] = $dateTo; }

            $data = $db->fetchAll(
                "SELECT o.order_code, o.fullname, o.phone, o.city, o.total, o.status,
                        o.payment_method, o.created_at,
                        (SELECT COUNT(*) FROM order_details WHERE order_id=o.id) AS items
                 FROM orders o $where ORDER BY o.created_at DESC", $params
            );
            $headers = ['Mã đơn','Khách hàng','SĐT','TP','Sản phẩm','Tổng tiền','Trạng thái','TT','Ngày đặt'];
            foreach ($data as $r) {
                $rows[] = [
                    htmlspecialchars($r['order_code']),
                    htmlspecialchars($r['fullname']),
                    htmlspecialchars($r['phone']),
                    htmlspecialchars($r['city'] ?? ''),
                    (int)$r['items'].' sp',
                    number_format((float)$r['total'],0,',','.').'đ',
                    $statusLabels[$r['status']] ?? $r['status'],
                    strtoupper($r['payment_method'] ?? ''),
                    date('d/m/Y H:i', strtotime($r['created_at'])),
                ];
            }

        } elseif ($type === 'products') {
            $search = trim($_GET['s']   ?? '');
            $catId  = (int)($_GET['cat'] ?? 0);
            $title  = 'Danh sách sản phẩm';
            $parts  = [];
            if ($search) $parts[] = 'Tìm: "'.htmlspecialchars($search).'"';
            if ($catId)  $parts[] = 'Danh mục ID: '.$catId;
            $filters = $parts ? implode(' | ', $parts) : 'Tất cả';

            $where = 'WHERE p.is_deleted=0'; $params = [];
            if ($search) { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
            if ($catId)  { $where .= ' AND p.category_id=?'; $params[] = $catId; }

            $data = $db->fetchAll(
                "SELECT p.name, p.sku, c.name AS cat_name, p.price, p.sale_price,
                        p.stock, p.is_active, p.created_at
                 FROM products p
                 LEFT JOIN categories c ON p.category_id=c.id
                 $where ORDER BY p.created_at DESC", $params
            );
            $headers = ['Tên sản phẩm','SKU','Danh mục','Giá gốc','Giá bán','Tồn kho','Trạng thái','Ngày tạo'];
            foreach ($data as $r) {
                $rows[] = [
                    htmlspecialchars($r['name']),
                    htmlspecialchars($r['sku'] ?? ''),
                    htmlspecialchars($r['cat_name'] ?? ''),
                    number_format((float)$r['price'],0,',','.').'đ',
                    $r['sale_price'] ? number_format((float)$r['sale_price'],0,',','.').'đ' : '-',
                    (int)$r['stock'],
                    $r['is_active'] ? 'Hiển thị' : 'Ẩn',
                    date('d/m/Y', strtotime($r['created_at'])),
                ];
            }

        } elseif ($type === 'customers') {
            $search  = trim($_GET['s']       ?? '');
            $segment = $_GET['segment']      ?? '';
            $title   = 'Danh sách khách hàng';
            $parts   = [];
            if ($search)  $parts[] = 'Tìm: "'.htmlspecialchars($search).'"';
            if ($segment) $parts[] = 'Phân khúc: '.htmlspecialchars($segment);
            $filters = $parts ? implode(' | ', $parts) : 'Tất cả';

            $where = ['u.role=0']; $wParams = [];
            if ($search) { $where[] = '(u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)'; array_push($wParams, "%$search%", "%$search%", "%$search%"); }
            $havingStr = '';
            if ($segment === 'vip')      $havingStr = 'HAVING COALESCE(SUM(o.total),0)>=10000000';
            if ($segment === 'new')      { $where[] = 'u.created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)'; }
            if ($segment === 'locked')   { $where[] = 'u.is_active=0'; }
            if ($segment === 'inactive') $havingStr = 'HAVING (MAX(o.created_at)<DATE_SUB(NOW(),INTERVAL 90 DAY) OR (COUNT(o.id)=0 AND MIN(u.created_at)<DATE_SUB(NOW(),INTERVAL 30 DAY)))';
            $ws = implode(' AND ', $where);
            $data = $db->fetchAll(
                "SELECT u.fullname, u.email, u.phone, u.city,
                        COUNT(DISTINCT o.id) AS order_count,
                        COALESCE(SUM(o.total),0) AS total_spent,
                        MAX(o.created_at) AS last_order_at,
                        u.created_at, IF(u.is_active,'Hoạt động','Bị khóa') AS status
                 FROM users u
                 LEFT JOIN orders o ON o.user_id=u.id AND o.is_deleted=0 AND o.status!='cancelled'
                 WHERE $ws GROUP BY u.id $havingStr ORDER BY u.created_at DESC", $wParams
            );
            $headers = ['Họ tên','Email','SĐT','Thành phố','Số đơn','Tổng chi tiêu','Lần mua cuối','Ngày ĐK','Trạng thái'];
            foreach ($data as $r) {
                $rows[] = [
                    htmlspecialchars($r['fullname']),
                    htmlspecialchars($r['email']),
                    htmlspecialchars($r['phone'] ?? ''),
                    htmlspecialchars($r['city'] ?? ''),
                    (int)$r['order_count'],
                    number_format((float)$r['total_spent'],0,',','.').'đ',
                    $r['last_order_at'] ? date('d/m/Y', strtotime($r['last_order_at'])) : '-',
                    date('d/m/Y', strtotime($r['created_at'])),
                    htmlspecialchars($r['status']),
                ];
            }

        } elseif ($type === 'inventory') {
            $search  = trim($_GET['s']      ?? '');
            $catId   = (int)($_GET['cat']   ?? 0);
            $statusF = trim($_GET['status'] ?? '');
            $title   = 'Báo cáo tồn kho';
            $parts   = [];
            if ($search)  $parts[] = 'Tìm: "'.htmlspecialchars($search).'"';
            if ($statusF) $parts[] = ['out'=>'Hết hàng','low'=>'Sắp hết','ok'=>'Còn hàng'][$statusF] ?? '';
            $filters = $parts ? implode(' | ', $parts) : 'Tất cả';

            $where = 'WHERE p.is_deleted=0'; $params = [];
            if ($search) { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
            if ($catId)  { $where .= ' AND p.category_id=?'; $params[] = $catId; }
            if ($statusF === 'out') $where .= ' AND i.stock_quantity<=0';
            elseif ($statusF === 'low') $where .= ' AND i.stock_quantity>0 AND i.stock_quantity<=i.min_stock';
            elseif ($statusF === 'ok')  $where .= ' AND i.stock_quantity>i.min_stock';
            $data = $db->fetchAll(
                "SELECT p.name, p.sku, c.name AS cat_name,
                        i.stock_quantity, i.min_stock, i.last_restocked
                 FROM inventory i
                 JOIN products p ON i.product_id=p.id
                 JOIN categories c ON p.category_id=c.id
                 $where ORDER BY i.stock_quantity ASC", $params
            );
            $headers = ['Sản phẩm','SKU','Danh mục','Tồn kho','Tối thiểu','Cập nhật cuối'];
            foreach ($data as $r) {
                $rows[] = [
                    htmlspecialchars($r['name']),
                    htmlspecialchars($r['sku'] ?? ''),
                    htmlspecialchars($r['cat_name']),
                    (int)$r['stock_quantity'],
                    (int)$r['min_stock'],
                    $r['last_restocked'] ? date('d/m/Y', strtotime($r['last_restocked'])) : '-',
                ];
            }

        } elseif ($type === 'stats') {
            $title   = 'Thống kê doanh số '.date('Y');
            $filters = 'Năm '.date('Y');
            $data    = $db->fetchAll(
                "SELECT MONTH(created_at) m, COUNT(*) cnt, COALESCE(SUM(total),0) rev
                 FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND status!='cancelled' AND is_deleted=0
                 GROUP BY m ORDER BY m"
            );
            $headers = ['Tháng','Số đơn','Doanh thu','TB/đơn'];
            foreach ($data as $r) {
                $avg = $r['cnt'] > 0 ? round($r['rev'] / $r['cnt']) : 0;
                $rows[] = [
                    'Tháng '.$r['m'],
                    (int)$r['cnt'],
                    number_format((float)$r['rev'],0,',','.').'đ',
                    number_format($avg,0,',','.').'đ',
                ];
            }
        } else {
            header('Location:'.APP_URL.'/admin'); exit;
        }

        $rowCount = count($rows);
        // Output standalone print page
        header('Content-Type: text/html; charset=utf-8');
        ?><!DOCTYPE html>
<html lang="vi"><head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?> — Tuấn Huy Computer</title>
<style>
@page{size:A4 landscape;margin:1cm}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:11px;color:#111;background:#fff}
.ph{padding:12px 0 8px;border-bottom:2px solid #111;margin-bottom:10px;display:flex;justify-content:space-between;align-items:flex-end}
.ph-brand{font-size:18px;font-weight:900;letter-spacing:-.5px}
.ph-brand em{color:#E30000;font-style:normal}
.ph-meta{text-align:right;font-size:10px;color:#555;line-height:1.6}
.pt{font-size:14px;font-weight:800;margin-bottom:3px;color:#111}
.pf{font-size:10px;color:#555;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th{background:#111;color:#fff;padding:5px 7px;text-align:left;font-size:10px;font-weight:700;white-space:nowrap}
td{padding:4px 7px;border-bottom:1px solid #e8e8e8;vertical-align:middle;font-size:10.5px}
tr:nth-child(even) td{background:#f9f9f9}
.pfoot{margin-top:8px;font-size:9px;color:#888;text-align:right;border-top:1px solid #ddd;padding-top:5px}
@media print{
  body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  @page{size:A4 landscape;margin:1cm}
}
</style>
</head><body>
<div class="ph">
  <div>
    <div class="ph-brand">Tuấn Huy <em>Computer</em></div>
    <div style="font-size:9px;color:#777;margin-top:1px">Chuyên linh kiện – Gaming PC – Laptop chính hãng</div>
  </div>
  <div class="ph-meta">
    In ngày: <?= date('d/m/Y H:i') ?><br>
    Người in: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
  </div>
</div>
<div class="pt"><?= htmlspecialchars($title) ?></div>
<div class="pf">Bộ lọc: <?= $filters ?> — Tổng: <?= $rowCount ?> bản ghi</div>
<table>
  <thead><tr><?php foreach($headers as $h): ?><th><?= $h ?></th><?php endforeach; ?></tr></thead>
  <tbody>
  <?php foreach($rows as $row): ?>
    <tr><?php foreach($row as $cell): ?><td><?= $cell ?></td><?php endforeach; ?></tr>
  <?php endforeach; ?>
  <?php if(empty($rows)): ?>
    <tr><td colspan="<?= count($headers) ?>" style="text-align:center;color:#999;padding:20px">Không có dữ liệu</td></tr>
  <?php endif; ?>
  </tbody>
</table>
<div class="pfoot">Tuấn Huy Computer &mdash; Xuất <?= date('d/m/Y H:i:s') ?></div>
<script>window.onload=function(){window.print();}</script>
</body></html><?php
        exit;
    }
}
