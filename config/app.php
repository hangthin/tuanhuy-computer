<?php
// config/app.php
define('APP_URL',        'http://localhost/tuanhuy_computer');
define('APP_NAME',       'Tuấn Huy Computer');
define('UPLOAD_PATH',    __DIR__ . '/../uploads/products/');
define('UPLOAD_URL',     APP_URL . '/uploads/products/');
define('ITEMS_PER_PAGE', 12);
define('AI_API_KEY',       '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');  // Groq API key - console.groq.com
define('AI_ACCOUNT_ID',   '');
define('AI_MODEL', 'llama-3.2-11b-vision-preview');  // Groq vision model
define('BING_SEARCH_KEY',    '');  // Bing Image Search — portal.azure.com (miễn phí 1000/tháng)
define('SERPAPI_KEY',        '435b06248db6e3879ae6866728d21ba94f30564376c3737111631db7c74a99b5');  // SerpApi Google Images — serpapi.com (miễn phí 250/tháng)
define('REMOVEBG_KEY',       'Ud9roGTXJyrzds4paC7K388Z');  // remove.bg — remove.bg/dashboard#api-key (miễn phí 50 lần/tháng)
define('PIXABAY_KEY',        '55110038-3a27159819805fc1d3758fe86');  // Pixabay
define('PEXELS_KEY',         '7WhO2yhf9xp7vE4X0m9sNRmJFBke2URHdA1NSMJsxOX6UQk64InX3GDt');  // Pexels — pexels.com/api (miễn phí 20.000/tháng)
define('GOOGLE_SEARCH_KEY',  'AIzaSyAC30AiAs36tb9HnGDkDZZQlg1jvrgWgO8');  // Google Custom Search — cần bật billing
define('GOOGLE_SEARCH_CX',   '3411273be375240e9');  // Google Programmable Search Engine ID

// ── Zalo OA ───────────────────────────────────────────────────────────────────
// Hướng dẫn: oa.zalo.me → Cài đặt → Tích hợp API → tạo access token
define('ZALO_OA_TOKEN',  '');   // Access token OA (hết hạn sau 90 ngày, cần renew)
define('ZALO_ADMIN_ID',  '');   // Zalo User ID của Admin (xem tại oa.zalo.me → Người quan tâm)
define('ZALO_ADMIN_PHONE','');  // SĐT admin (chỉ để tham chiếu, không dùng trực tiếp với API)

// ── Telegram Bot ──────────────────────────────────────────────────────────────
// Hướng dẫn: nhắn @BotFather trên Telegram → /newbot → lấy token
// Chat ID: nhắn tin cho bot rồi truy cập https://api.telegram.org/bot{TOKEN}/getUpdates
define('TELEGRAM_BOT_TOKEN', '8723472812:AAHqJjAXt4jsKAYkY8X5lnfzhyNc6Fh0YJY');  // @TuanHuyComputerBot
define('TELEGRAM_ADMIN_CHAT', '7329986368'); // Nh Thin — admin

// ── Email (SMTP) ──────────────────────────────────────────────────────────────
// Dùng Gmail App Password: myaccount.google.com → Security → App passwords
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      'nhthin366@gmail.com');  // ← email Gmail của bạn, vd: shop@gmail.com
define('MAIL_PASS',      'uflflbwsxwujslhd');  // ← Gmail App Password (16 ký tự, không phải mật khẩu thường)
define('MAIL_FROM',      'nhthin366@gmail.com');
define('MAIL_FROM_NAME', APP_NAME);

if (session_status() === PHP_SESSION_NONE) {
    session_name('TH_SESS');
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . 'đ';
}
function setFlash($type, $msg) {
    $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f;
    }
    return null;
}
function isLoggedIn()  { return isset($_SESSION['user_id']); }
function isAdmin()     { return isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1; }
function isStaff()     { return isset($_SESSION['user_role']) && in_array((int)$_SESSION['user_role'], [1,2,3]); }
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login'); exit;
    }
}
function requireAdmin() {
    requireLogin();
    if (!isStaff()) { header('Location: ' . APP_URL . '/'); exit; }
}
function sanitize($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}
function makeSlug($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $map = array(
        'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ặ'=>'a',
        'ằ'=>'a','ẳ'=>'a','ẵ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a',
        'ậ'=>'a','đ'=>'d','è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e',
        'ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e','ì'=>'i','í'=>'i','ỉ'=>'i',
        'ĩ'=>'i','ị'=>'i','ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o',
        'ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o',
        'ở'=>'o','ỡ'=>'o','ợ'=>'o','ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
        'ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u','ỳ'=>'y','ý'=>'y',
        'ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
    );
    $str = strtr($str, $map);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}
function getCartCount() {
    $db = Database::getInstance();
    if (isLoggedIn()) {
        $row = $db->fetch("SELECT SUM(quantity) as cnt FROM cart WHERE user_id=?",
            array($_SESSION['user_id']));
    } else {
        $row = $db->fetch("SELECT SUM(quantity) as cnt FROM cart WHERE session_id=?",
            array(session_id()));
    }
    return (int)(isset($row['cnt']) ? $row['cnt'] : 0);
}
function calcCartSubtotal($items) {
    $total = 0;
    foreach ($items as $i) $total += (float)$i['unit_price'] * (int)$i['quantity'];
    return $total;
}
