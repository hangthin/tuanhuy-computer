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
        $lowStock       = $db->fetchAll(
            "SELECT p.name, i.stock_quantity
             FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0
             WHERE i.stock_quantity<=i.min_stock ORDER BY i.stock_quantity LIMIT 5"
        );
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
                if ($img) { $pm->updateImage($id, $img); $d['image'] = $img; }
                $pm->update($id, $d);
                $this->handleExtraImages($pm, $id);
                $this->handleExtraImagesB64($pm, $id);
                $this->handleExtraImagesPresaved($pm, $id);

                // Luôn lưu name để nhật ký hiển thị đúng dù name không thay đổi
                $logOld = array_merge(['name' => $oldData['name'] ?? ''], $oldData);
                $logNew = array_merge(['name' => $oldData['name'] ?? ''], $d);
                Logger::log('UPDATE', 'products', $id, $logOld, $logNew);

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
            if (!RoleGuard::canDelete()) {
                RoleGuard::deny('Bạn không có quyền xóa ảnh.');
            }
            $imgId = (int)($_GET['img_id']    ?? 0);
            $pid   = (int)($_GET['product_id'] ?? 0);
            if ($imgId > 0) $pm->deleteImage($imgId);
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
                if ($old) Logger::update('categories', $id, $old, array_merge($old, $data));
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
            Logger::update('users', $id, $old, array_merge($old, $data));
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
                    ['is_active' => $u['is_active'] ? 0 : 1]
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
        $um     = new UserModel();
        $search = $_GET['s']    ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));

        if ($action === 'toggle') {
            if (RoleGuard::role() === RoleGuard::STAFF) {
                RoleGuard::deny('Staff không có quyền thay đổi trạng thái khách hàng.');
            }
            $id = (int)($_GET['id'] ?? 0);
            $u  = $um->findById($id);
            if ($u) {
                $um->setActive($id, $u['is_active'] ? 0 : 1);
                Logger::update('users', $id,
                    ['is_active' => $u['is_active']],
                    ['is_active' => $u['is_active'] ? 0 : 1]
                );
            }
            setFlash('success', 'Cập nhật!');
            header('Location:' . APP_URL . '/admin/customers'); exit;
        }

        $customers       = $um->getAll($search, $page);
        $totalCustomers  = $um->count($search);
        $totalPagesAdmin = (int)ceil($totalCustomers / 20);
        $pageTitle       = 'Quản lý khách hàng';
        include __DIR__.'/../Views/admin/customers.php';
    }

    // ─────────────────────────────────────────────────────────────────
    // Orders
    // ─────────────────────────────────────────────────────────────────
    public function orders($action = null): void {
        $this->check();
        $om     = new OrderModel();
        $search = $_GET['s']      ?? '';
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));

        if ($action === 'status') {
            if (RoleGuard::role() === RoleGuard::STAFF) {
                RoleGuard::deny('Staff không có quyền cập nhật trạng thái đơn hàng.');
            }
            $id        = (int)($_GET['id'] ?? 0);
            $newStatus = sanitize($_POST['status'] ?? 'pending');
            $oldOrder  = $om->getWithItems($id);
            $om->updateStatus($id, $newStatus);

            if ($oldOrder) {
                Logger::log('UPDATE', 'orders', $id,
                    ['order_code' => $oldOrder['order_code'] ?? '', 'fullname' => $oldOrder['fullname'] ?? '', 'status' => $oldOrder['status']],
                    ['order_code' => $oldOrder['order_code'] ?? '', 'fullname' => $oldOrder['fullname'] ?? '', 'status' => $newStatus]
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

            setFlash('success', 'Cập nhật!');
            header('Location:' . APP_URL . '/admin/orders'); exit;
        }

        if ($action === 'detail') {
            $id    = (int)($_GET['id'] ?? 0);
            $order = $om->getWithItems($id);
            $pageTitle = 'Chi tiết đơn';
            include __DIR__.'/../Views/admin/order_detail.php'; return;
        }

        $orders          = $om->getAll($search, $status, $page);
        $totalPagesAdmin = (int)ceil($om->count($search, $status) / 20);
        $pageTitle       = 'Quản lý đơn hàng';
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
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $pid    = (int)($_POST['product_id'] ?? 0);
            $qty    = (int)($_POST['quantity']    ?? 0);
            $oldRow  = $db->fetch("SELECT stock_quantity FROM inventory WHERE product_id=?", [$pid]);
            $product = $db->fetch("SELECT name FROM products WHERE id=?", [$pid]);
            $db->query("UPDATE inventory SET stock_quantity=?,last_restocked=NOW() WHERE product_id=?", [$qty, $pid]);
            $db->query("UPDATE products SET stock=? WHERE id=?", [$qty, $pid]);
            if ($oldRow) {
                Logger::log('UPDATE', 'inventory', $pid,
                    ['product_name' => $product['name'] ?? '', 'stock_quantity' => $oldRow['stock_quantity']],
                    ['product_name' => $product['name'] ?? '', 'stock_quantity' => $qty]
                );
            }
            setFlash('success', 'Đã cập nhật kho!');
            header('Location:' . APP_URL . '/admin/inventory'); exit;
        }
        $inventory = $db->fetchAll(
            "SELECT i.*,p.name,p.sku,c.name AS cat_name
             FROM inventory i
             JOIN products p ON i.product_id=p.id AND p.is_deleted=0
             JOIN categories c ON p.category_id=c.id
             ORDER BY i.stock_quantity ASC LIMIT 100"
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
             FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND status!='cancelled'
             GROUP BY m ORDER BY m"
        );
        $topCats = $db->fetchAll(
            "SELECT c.name, SUM(od.quantity) total_sold
             FROM order_details od
             JOIN products p ON od.product_id=p.id
             JOIN categories c ON p.category_id=c.id
             JOIN orders o ON od.order_id=o.id
             WHERE o.status!='cancelled'
             GROUP BY c.id ORDER BY total_sold DESC LIMIT 6"
        );
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
            'is_featured' => !empty($_POST['is_featured']),
            'is_new'      => !empty($_POST['is_new']),
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
}
