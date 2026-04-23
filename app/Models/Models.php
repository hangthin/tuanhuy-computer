<?php
// UserModel, CartModel, OrderModel, CategoryModel

class UserModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function findByEmail($email) { return $this->db->fetch("SELECT * FROM users WHERE email=?",array($email)); }
    public function findById($id) { return $this->db->fetch("SELECT * FROM users WHERE id=?",array($id)); }
    public function create($d) {
        $this->db->query("INSERT INTO users (fullname,email,phone,password,role,city) VALUES (?,?,?,?,0,?)",
            array($d['fullname'],$d['email'],$d['phone'],password_hash($d['password'],PASSWORD_BCRYPT),isset($d['city'])?$d['city']:''));
        return (int)$this->db->lastInsertId();
    }
    public function updateLastLogin($id) { $this->db->query("UPDATE users SET last_login=NOW() WHERE id=?",array($id)); }
    public function update($id,$d) {
        $this->db->query("UPDATE users SET fullname=?,phone=?,city=?,district=?,address=? WHERE id=?",
            array($d['fullname'],$d['phone']??'',$d['city']??'',$d['district']??'',$d['address']??'',$id));
    }
    public function getAll($search='',$page=1,$limit=20) {
        $where='role=0'; $params=array();
        if($search){$where.=" AND (fullname LIKE ? OR email LIKE ?)";$params=array("%{$search}%","%{$search}%");}
        $offset=($page-1)*$limit;
        return $this->db->fetchAll("SELECT * FROM users WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",$params);
    }
    public function count($search='') {
        $where='role=0'; $params=array();
        if($search){$where.=" AND (fullname LIKE ? OR email LIKE ?)";$params=array("%{$search}%","%{$search}%");}
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE {$where}",$params)->fetchColumn();
    }
    public function setActive($id,$val) { $this->db->query("UPDATE users SET is_active=? WHERE id=?",array($val,$id)); }

    public function getAllStaff($search='',$page=1,$limit=20) {
        $where='role IN (1,2,3)'; $params=array();
        if($search){$where.=" AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";$params=array("%{$search}%","%{$search}%","%{$search}%");}
        $offset=($page-1)*$limit;
        return $this->db->fetchAll("SELECT * FROM users WHERE {$where} ORDER BY role ASC, created_at DESC LIMIT {$limit} OFFSET {$offset}",$params);
    }
    public function countStaff($search='') {
        $where='role IN (1,2,3)'; $params=array();
        if($search){$where.=" AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";$params=array("%{$search}%","%{$search}%","%{$search}%");}
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE {$where}",$params)->fetchColumn();
    }
    public function createStaff($d) {
        $this->db->query("INSERT INTO users (fullname,email,phone,password,role) VALUES (?,?,?,?,?)",
            array($d['fullname'],$d['email'],$d['phone']??'',password_hash($d['password'],PASSWORD_BCRYPT),(int)$d['role']));
        return (int)$this->db->lastInsertId();
    }
    public function updateStaff($id,$d) {
        $this->db->query("UPDATE users SET fullname=?,phone=?,role=? WHERE id=?",
            array($d['fullname'],$d['phone']??'',(int)$d['role'],$id));
    }
    public function updatePassword($id,$hash) {
        $this->db->query("UPDATE users SET password=? WHERE id=?",array($hash,$id));
    }
    public function findByGoogleId($googleId) {
        return $this->db->fetch("SELECT * FROM users WHERE google_id=?", array($googleId));
    }
    public function linkGoogleId($id, $googleId) {
        $this->db->query("UPDATE users SET google_id=? WHERE id=?", array($googleId, $id));
    }
    public function createViaGoogle($d) {
        $this->db->query(
            "INSERT INTO users (fullname, email, avatar, google_id, password, role, is_active) VALUES (?,?,?,?,?,0,1)",
            array($d['fullname'], $d['email'], $d['avatar'] ?? '', $d['google_id'], '')
        );
        return (int)$this->db->lastInsertId();
    }

    public static function generateOtp($email, $type = 'forgot') {
        $db = Database::getInstance();
        $cnt = (int)$db->query(
            "SELECT COUNT(*) FROM password_otps WHERE email=? AND type=? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            array($email, $type)
        )->fetchColumn();
        if ($cnt >= 5) return false;
        $db->query("UPDATE password_otps SET used=1 WHERE email=? AND type=? AND used=0", array($email, $type));
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $db->query(
            "INSERT INTO password_otps (email, otp, type, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))",
            array($email, $otp, $type)
        );
        return $otp;
    }

    public static function verifyOtp($email, $otp, $type = 'forgot') {
        $db = Database::getInstance();
        $row = $db->fetch(
            "SELECT id FROM password_otps WHERE email=? AND otp=? AND type=? AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            array($email, $otp, $type)
        );
        if (!$row) return false;
        $db->query("UPDATE password_otps SET used=1 WHERE id=?", array($row['id']));
        return true;
    }
}

class CartModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    private function buildWhere() {
        if (isLoggedIn()) return array('WHERE user_id=?', array($_SESSION['user_id']));
        return array('WHERE session_id=?', array(session_id()));
    }

    public function getItems() {
        list($w,$p) = $this->buildWhere();
        return $this->db->fetchAll(
            "SELECT c.*, p.name, p.slug, p.image, p.stock, COALESCE(p.sale_price,p.price) AS unit_price
             FROM cart c JOIN products p ON c.product_id=p.id {$w}", $p);
    }

    public function add($productId,$qty=1) {
        list($w,$p) = $this->buildWhere();
        $key = isLoggedIn() ? array('user_id'=>$_SESSION['user_id'],'session_id'=>null) : array('user_id'=>null,'session_id'=>session_id());
        $exists = $this->db->fetch("SELECT id,quantity FROM cart {$w} AND product_id=?", array_merge($p,array($productId)));
        if ($exists) {
            $this->db->query("UPDATE cart SET quantity=quantity+?,updated_at=NOW() WHERE id=?",array($qty,$exists['id']));
        } else {
            $this->db->query("INSERT INTO cart (user_id,session_id,product_id,quantity) VALUES (?,?,?,?)",
                array($key['user_id'],$key['session_id'],$productId,$qty));
        }
    }

    public function update($cartId,$qty) { $this->db->query("UPDATE cart SET quantity=?,updated_at=NOW() WHERE id=?",array($qty,$cartId)); }
    public function remove($cartId)      { $this->db->query("DELETE FROM cart WHERE id=?",array($cartId)); }

    public function clear() {
        list($w,$p) = $this->buildWhere();
        $this->db->query("DELETE FROM cart {$w}",$p);
    }

    public function mergeGuestCart($userId) {
        $sid = session_id();
        $guests = $this->db->fetchAll("SELECT * FROM cart WHERE session_id=?",array($sid));
        foreach ($guests as $item) {
            $ex = $this->db->fetch("SELECT id,quantity FROM cart WHERE user_id=? AND product_id=?",array($userId,$item['product_id']));
            if ($ex) {
                $this->db->query("UPDATE cart SET quantity=quantity+? WHERE id=?",array($item['quantity'],$ex['id']));
            } else {
                $this->db->query("INSERT INTO cart (user_id,product_id,quantity) VALUES (?,?,?)",array($userId,$item['product_id'],$item['quantity']));
            }
        }
        $this->db->query("DELETE FROM cart WHERE session_id=?",array($sid));
    }
}

class OrderModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create($d) {
        $code = 'THC'.date('Ymd').rand(100,999);
        $this->db->query(
            "INSERT INTO orders (order_code,user_id,fullname,email,phone,address,city,district,ward,subtotal,shipping_fee,discount,coupon_code,total,payment_method,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            array($code,$d['user_id']??null,$d['fullname'],$d['email']??'',$d['phone'],
                  $d['address'],$d['city']??'',$d['district']??'',$d['ward']??'',
                  $d['subtotal'],$d['shipping_fee']??0,$d['discount']??0,$d['coupon_code']??null,$d['total'],
                  $d['payment_method']??'cod',$d['notes']??''));
        return (int)$this->db->lastInsertId();
    }

    public function addItem($orderId,$item) {
        $this->db->query(
            "INSERT INTO order_details (order_id,product_id,product_name,product_sku,price,quantity,subtotal)
             VALUES (?,?,?,?,?,?,?)",
            array($orderId,$item['product_id'],$item['name'],$item['sku']??'',$item['price'],$item['quantity'],$item['subtotal']));
        $this->db->query("UPDATE products SET stock=stock-?,sold=sold+? WHERE id=?",array($item['quantity'],$item['quantity'],$item['product_id']));
        $this->db->query("UPDATE inventory SET stock_quantity=stock_quantity-? WHERE product_id=?",array($item['quantity'],$item['product_id']));
    }

    public function getWithItems($id) {
        $o = $this->db->fetch("SELECT * FROM orders WHERE id=?",array($id));
        if ($o) $o['items'] = $this->db->fetchAll(
            "SELECT od.*, p.image, p.category_id FROM order_details od
             LEFT JOIN products p ON od.product_id=p.id
             WHERE od.order_id=?", array($id));
        return $o;
    }

    public function getByCode($code) { return $this->db->fetch("SELECT * FROM orders WHERE order_code=?",array($code)); }

    public function getUserOrders($uid) {
        return $this->db->fetchAll(
            "SELECT o.*, COUNT(od.id) AS item_count FROM orders o
             LEFT JOIN order_details od ON o.id=od.order_id
             WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC", array($uid));
    }

    public function getAll($search='',$status='',$page=1,$limit=20) {
        $where=array('1=1'); $params=array();
        if($search){$where[]='(o.order_code LIKE ? OR o.fullname LIKE ? OR o.phone LIKE ?)';$params[]="%{$search}%";$params[]="%{$search}%";$params[]="%{$search}%";}
        if($status){$where[]='o.status=?';$params[]=$status;}
        $ws=implode(' AND ',$where); $offset=($page-1)*$limit;
        return $this->db->fetchAll(
            "SELECT o.*, COUNT(od.id) AS item_count FROM orders o
             LEFT JOIN order_details od ON o.id=od.order_id
             WHERE {$ws} GROUP BY o.id ORDER BY o.created_at DESC LIMIT {$limit} OFFSET {$offset}", $params);
    }

    public function count($search='',$status='') {
        $where=array('1=1'); $params=array();
        if($search){$where[]='(order_code LIKE ? OR fullname LIKE ?)';$params[]="%{$search}%";$params[]="%{$search}%";}
        if($status){$where[]='status=?';$params[]=$status;}
        $ws=implode(' AND ',$where);
        return (int)$this->db->query("SELECT COUNT(*) FROM orders WHERE {$ws}",$params)->fetchColumn();
    }

    public function updateStatus($id,$status) { $this->db->query("UPDATE orders SET status=?,updated_at=NOW() WHERE id=?",array($status,$id)); }

    public function getStats() {
        return array(
            'today'         => $this->db->fetch("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'"),
            'month'         => $this->db->fetch("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND status!='cancelled'"),
            'year'          => $this->db->fetch("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND status!='cancelled'"),
            'total'         => $this->db->fetch("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev FROM orders WHERE status!='cancelled'"),
            'monthly_chart' => $this->db->fetchAll("SELECT MONTH(created_at) AS m, COALESCE(SUM(total),0) AS rev, COUNT(*) AS cnt FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND status!='cancelled' GROUP BY m ORDER BY m"),
            'top_products'  => $this->db->fetchAll("SELECT p.name, SUM(od.quantity) AS total_sold, SUM(od.subtotal) AS revenue FROM order_details od JOIN products p ON od.product_id=p.id JOIN orders o ON od.order_id=o.id WHERE o.status!='cancelled' GROUP BY p.id ORDER BY total_sold DESC LIMIT 5"),
        );
    }
}

class CategoryModel {
    private $db;
    // Icon mặc định theo slug — fallback khi DB lưu emoji bị lỗi (utf8 → utf8mb4)
    private static $iconMap = array(
        'may-tinh-pc' => '🖥️',
        'laptop'      => '💻',
        'man-hinh'    => '📺',
        'chuot'       => '🖱️',
        'ban-phim'    => '⌨️',
        'ram'         => '💾',
        'cpu'         => '⚡',
        'card-do-hoa' => '🎮',
        'ssd-o-cung'  => '💿',
        'mainboard'   => '🔧',
        'phu-kien'    => '🎧',
    );
    public function __construct() { $this->db = Database::getInstance(); }

    private function applyIcons($rows) {
        foreach ($rows as &$row) {
            $slug = $row['slug'] ?? '';
            // Nếu icon trong DB hợp lệ (không rỗng, không chứa '?') thì giữ nguyên
            if (empty($row['icon']) || strpos($row['icon'], '?') !== false) {
                $row['icon'] = isset(self::$iconMap[$slug]) ? self::$iconMap[$slug] : '📦';
            }
        }
        return $rows;
    }

    public function getAll() {
        $rows = $this->db->fetchAll(
            "SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c LEFT JOIN products p ON c.id=p.category_id AND p.is_active=1 AND p.is_deleted=0
             WHERE c.is_active=1 GROUP BY c.id ORDER BY c.sort_order");
        return $this->applyIcons($rows);
    }
    public function getBySlug($slug) { return $this->db->fetch("SELECT * FROM categories WHERE slug=?",array($slug)); }
    public function getById($id)     { return $this->db->fetch("SELECT * FROM categories WHERE id=?",array($id)); }
    public function getForAdmin()    { return $this->applyIcons($this->db->fetchAll("SELECT * FROM categories ORDER BY sort_order")); }

    public function create($d) {
        $this->db->query("INSERT INTO categories (name,slug,icon,description,sort_order) VALUES (?,?,?,?,?)",
            array($d['name'],$d['slug'],isset($d['icon'])?$d['icon']:'',isset($d['description'])?$d['description']:'',isset($d['sort_order'])?$d['sort_order']:0));
    }
    public function update($id,$d) {
        $this->db->query("UPDATE categories SET name=?,slug=?,icon=?,description=?,sort_order=?,is_active=? WHERE id=?",
            array($d['name'],$d['slug'],isset($d['icon'])?$d['icon']:'',isset($d['description'])?$d['description']:'',isset($d['sort_order'])?$d['sort_order']:0,isset($d['is_active'])?$d['is_active']:1,$id));
    }
}
