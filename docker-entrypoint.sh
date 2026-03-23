#!/bin/bash
# Tạo config/database.php từ environment variables
cat > /var/www/html/config/database.php <<EOF
<?php
class Database {
    private static \$instance = null;
    private \$pdo;
    private function __construct() {
        \$host   = getenv('DB_HOST')   ?: '127.0.0.1';
        \$dbname = getenv('DB_NAME')   ?: 'tuanhuy_computer';
        \$user   = getenv('DB_USER')   ?: 'root';
        \$pass   = getenv('DB_PASS')   ?: '';
        \$port   = getenv('DB_PORT')   ?: '3306';
        \$dsn    = "mysql:host=\$host;port=\$port;dbname=\$dbname;charset=utf8mb4";
        \$this->pdo = new PDO(\$dsn, \$user, \$pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    public static function getInstance() {
        if (!self::\$instance) self::\$instance = new self();
        return self::\$instance;
    }
    public function query(\$sql, \$params = []) {
        \$stmt = \$this->pdo->prepare(\$sql);
        \$stmt->execute(\$params);
        return \$stmt;
    }
    public function fetch(\$sql, \$params = []) {
        return \$this->query(\$sql, \$params)->fetch();
    }
    public function fetchAll(\$sql, \$params = []) {
        return \$this->query(\$sql, \$params)->fetchAll();
    }
    public function lastInsertId() { return \$this->pdo->lastInsertId(); }
}
EOF

# Tạo config/app.php từ environment variables
cat > /var/www/html/config/app.php <<EOF
<?php
define('APP_URL',        getenv('APP_URL')        ?: 'http://localhost');
define('APP_NAME',       'Tuấn Huy Computer');
define('UPLOAD_PATH',    __DIR__ . '/../uploads/products/');
define('UPLOAD_URL',     APP_URL . '/uploads/products/');
define('ITEMS_PER_PAGE', 12);
define('AI_API_KEY',     getenv('AI_API_KEY')     ?: '');
define('AI_ACCOUNT_ID',  '');
define('AI_MODEL',       'llama-3.2-11b-vision-preview');
define('BING_SEARCH_KEY',    '');
define('SERPAPI_KEY',        getenv('SERPAPI_KEY')    ?: '');
define('REMOVEBG_KEY',       getenv('REMOVEBG_KEY')   ?: '');
define('PIXABAY_KEY',        getenv('PIXABAY_KEY')    ?: '');
define('PEXELS_KEY',         getenv('PEXELS_KEY')     ?: '');
define('GOOGLE_SEARCH_KEY',  getenv('GOOGLE_SEARCH_KEY') ?: '');
define('GOOGLE_SEARCH_CX',   getenv('GOOGLE_SEARCH_CX')  ?: '');
define('ZALO_OA_TOKEN',  '');
define('ZALO_ADMIN_ID',  '');
define('ZALO_ADMIN_PHONE','');
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '');
define('TELEGRAM_ADMIN_CHAT', getenv('TELEGRAM_ADMIN_CHAT') ?: '');
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      getenv('MAIL_USER') ?: '');
define('MAIL_PASS',      getenv('MAIL_PASS') ?: '');
define('MAIL_FROM',      getenv('MAIL_USER') ?: '');
define('MAIL_FROM_NAME', APP_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_name('TH_SESS');
    session_start();
}
ini_set('display_errors', 0);
error_reporting(0);
function formatPrice(\$price) { return number_format((float)\$price, 0, ',', '.') . 'đ'; }
function setFlash(\$type, \$msg) { \$_SESSION['flash'] = array('type' => \$type, 'msg' => \$msg); }
function getFlash() { if (isset(\$_SESSION['flash'])) { \$f = \$_SESSION['flash']; unset(\$_SESSION['flash']); return \$f; } return null; }
function isLoggedIn()  { return isset(\$_SESSION['user_id']); }
function isAdmin()     { return isset(\$_SESSION['user_role']) && (int)\$_SESSION['user_role'] === 1; }
function isStaff()     { return isset(\$_SESSION['user_role']) && in_array((int)\$_SESSION['user_role'], [1,2,3]); }
function requireLogin() { if (!isLoggedIn()) { header('Location: ' . APP_URL . '/auth/login'); exit; } }
function requireAdmin() { requireLogin(); if (!isStaff()) { header('Location: ' . APP_URL . '/'); exit; } }
function sanitize(\$str) { return htmlspecialchars(trim((string)\$str), ENT_QUOTES, 'UTF-8'); }
function makeSlug(\$str) {
    \$str = mb_strtolower(\$str, 'UTF-8');
    \$map = array('à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a','đ'=>'d','è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e','ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i','ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o','ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u','ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y');
    \$str = strtr(\$str, \$map);
    \$str = preg_replace('/[^a-z0-9\s-]/', '', \$str);
    \$str = preg_replace('/[\s-]+/', '-', \$str);
    return trim(\$str, '-');
}
function getCartCount() {
    \$db = Database::getInstance();
    if (isLoggedIn()) { \$row = \$db->fetch("SELECT SUM(quantity) as cnt FROM cart WHERE user_id=?", array(\$_SESSION['user_id'])); }
    else { \$row = \$db->fetch("SELECT SUM(quantity) as cnt FROM cart WHERE session_id=?", array(session_id())); }
    return (int)(isset(\$row['cnt']) ? \$row['cnt'] : 0);
}
function calcCartSubtotal(\$items) { \$total = 0; foreach (\$items as \$i) \$total += (float)\$i['unit_price'] * (int)\$i['quantity']; return \$total; }
EOF

chown -R www-data:www-data /var/www/html/config
chmod 640 /var/www/html/config/app.php /var/www/html/config/database.php

# Start Apache
exec apache2-foreground
