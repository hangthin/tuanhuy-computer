<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// ── Global error / exception handler ─────────────────────────────────────────
set_exception_handler(function($e) {
    $msg  = $e->getMessage();
    $file = $e->getFile();
    $line = $e->getLine();
    $url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
          . ($_SERVER['REQUEST_URI'] ?? '/');

    error_log('[500] ' . $msg . ' in ' . $file . ':' . $line);

    // AI analysis + notification alert (non-blocking, best-effort)
    try {
        $aiFile   = __DIR__ . '/app/Helpers/AIInsight.php';
        $tgFile   = __DIR__ . '/app/Helpers/TelegramNotifier.php';
        $zaloFile = __DIR__ . '/app/Helpers/ZaloNotifier.php';
        $aiSuggestion = '';
        if (file_exists($aiFile)) {
            require_once $aiFile;
            $aiSuggestion = AIInsight::analyzeError($msg, str_replace(__DIR__, '', $file), $line) ?? '';
        }
        $errSummary = $msg . ' (' . basename($file) . ':' . $line . ')';
        if (file_exists($tgFile)) {
            require_once $tgFile;
            TelegramNotifier::notifyError500($errSummary, $url, $aiSuggestion);
        }
        if (file_exists($zaloFile)) {
            require_once $zaloFile;
            if (class_exists('ZaloNotifier') && ZaloNotifier::isConfigured()) {
                ZaloNotifier::notifyError500($errSummary, $url, $aiSuggestion);
            }
        }
    } catch (Exception $inner) {
        error_log('[ErrorHandler] ' . $inner->getMessage());
    }

    // Show user-friendly 500 page
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $isDev = defined('APP_URL') && strpos(APP_URL, 'localhost') !== false;
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Lỗi hệ thống</title>'
       . '<style>body{font-family:sans-serif;background:#0f0f0f;color:#ddd;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}'
       . '.box{background:#1a1a1a;border:1px solid #333;border-radius:12px;padding:2rem;max-width:520px;text-align:center}'
       . 'h1{color:#ef4444;font-size:1.4rem}p{color:#888;font-size:.9rem;line-height:1.6}'
       . 'a{color:#e30000;text-decoration:none}pre{background:#111;padding:1rem;border-radius:7px;text-align:left;font-size:.75rem;color:#f87171;overflow:auto}</style></head><body>'
       . '<div class="box"><h1>⚠️ Đã xảy ra lỗi</h1>'
       . '<p>Hệ thống gặp sự cố. Đội kỹ thuật đã được thông báo.</p>'
       . ($isDev ? '<pre>' . htmlspecialchars($msg . "\n" . $file . ':' . $line) . '</pre>' : '')
       . '<p><a href="' . (defined('APP_URL') ? APP_URL : '/') . '">← Về trang chủ</a></p></div></body></html>';
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!($errno & error_reporting())) return false;
    // Convert E_ERROR / E_USER_ERROR to exceptions for the handler above to catch
    if (in_array($errno, array(E_ERROR, E_USER_ERROR))) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    return false; // let PHP handle warnings/notices normally
}, E_ALL);

$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$urlPath    = parse_url($requestUri, PHP_URL_PATH);
$basePath   = '/tuanhuy_computer';
if (strpos($urlPath, $basePath) === 0) {
    $urlPath = substr($urlPath, strlen($basePath));
}
$path     = trim($urlPath, '/');
if ($path === '' || $path === 'index.php') $path = '';
$segments = $path !== '' ? explode('/', $path) : array();

$controller = (isset($segments[0]) && $segments[0] !== '') ? $segments[0] : 'home';
$actionRaw  = (isset($segments[1]) && $segments[1] !== '') ? $segments[1] : 'index';
// Convert kebab-case URL segments to camelCase method names (e.g. cancel-order → cancelOrder)
$action = preg_replace_callback('/-([a-z])/', function($m){ return strtoupper($m[1]); }, $actionRaw);
$param      = isset($segments[2]) ? $segments[2] : null;

// Smart routing
// /admin/ai/generator -> AdminController->ai('generator')
if ($controller === 'admin' && $action === 'ai') {
    $param = isset($segments[2]) ? $segments[2] : 'generator';
}
// /admin/logs -> AdminController->logs()
if ($controller === 'admin' && $action === 'logs') {
    $param = null;
}
// /api/auth/login -> ApiController->auth('login')
// /api/cart/add   -> ApiController->cart('add')
// /api/ai/generate -> ApiController->ai('generate')
// action = segments[1], param = segments[2]
// đã đúng mặc định

// /products/laptop -> ProductController->index('laptop')
if ($controller === 'products' && $action !== 'detail' && $action !== 'index') {
    $param  = $action;
    $action = 'index';
}

$routes = array(
    'home'     => array('file' => 'app/Controllers/HomeController.php',     'class' => 'HomeController'),
    'products' => array('file' => 'app/Controllers/ProductController.php',  'class' => 'ProductController'),
    'cart'     => array('file' => 'app/Controllers/CartController.php',     'class' => 'CartController'),
    'checkout' => array('file' => 'app/Controllers/CheckoutController.php', 'class' => 'CheckoutController'),
    'auth'     => array('file' => 'app/Controllers/AuthController.php',     'class' => 'AuthController'),
    'account'  => array('file' => 'app/Controllers/AccountController.php',  'class' => 'AccountController'),
    'admin'    => array('file' => 'app/Controllers/AdminController.php',    'class' => 'AdminController'),
    'api'      => array('file' => 'app/Controllers/ApiController.php',      'class' => 'ApiController'),
    'search'   => array('file' => 'app/Controllers/SearchController.php',   'class' => 'SearchController'),
);

if (!isset($routes[$controller])) {
    $controller = 'home'; $action = 'page404';
}

$file = __DIR__ . '/' . $routes[$controller]['file'];
if (!file_exists($file)) die('Controller not found: '.$routes[$controller]['file']);

require_once $file;
$cls = $routes[$controller]['class'];
$obj = new $cls();

if (method_exists($obj, $action)) {
    $obj->$action($param);
} elseif (method_exists($obj, 'index')) {
    $obj->index($param);
} else {
    die('Method not found: '.$action.' in '.$cls);
}
