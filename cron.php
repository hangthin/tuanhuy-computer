<?php
/**
 * Telegram Bot Cron - gọi từ cron-job.org mỗi phút
 * URL: https://tuanhuy-computer.onrender.com/cron.php?token=tuanhuy_cron_2024
 */

// Bảo mật: kiểm tra secret token TRƯỚC KHI làm bất cứ gì
$secret = getenv('TELEGRAM_CRON_SECRET') ?: 'tuanhuy_cron_2024';
$token  = $_GET['token'] ?? '';
if (!$token || $token !== $secret) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Forbidden']);
    exit;
}

// Load config (không qua router)
define('APP_DIR', __DIR__);
ini_set('display_errors', 0);
error_reporting(0);

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/app.php';
    require_once __DIR__ . '/app/Helpers/TelegramBot.php';

    header('Content-Type: application/json; charset=utf-8');
    $result = TelegramBot::poll();
    echo json_encode($result);
} catch (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
