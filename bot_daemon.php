<?php
/**
 * Telegram Bot Daemon
 * Chạy liên tục, poll Telegram mỗi 3 giây, xử lý lệnh độc lập với trình duyệt.
 *
 * Khởi động:  php bot_daemon.php
 * Dừng:       Ctrl+C hoặc đóng cửa sổ CMD
 */

// Bootstrap
define('STDIN_DEFINED', true); // marker for CLI mode
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/Helpers/TelegramBot.php';
require_once __DIR__ . '/app/Helpers/TelegramNotifier.php';

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_implicit_flush(true);

$token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
if (!$token) {
    echo "[ERROR] TELEGRAM_BOT_TOKEN chưa cấu hình.\n";
    exit(1);
}

$site = defined('APP_NAME') ? APP_NAME : 'Bot';
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo " {$site} — Telegram Bot Daemon\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo " Bot token: ..." . substr($token, -8) . "\n";
echo " Poll interval: 3 giây\n";
echo " Dừng: Ctrl+C\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Gửi thông báo khởi động
TelegramNotifier::sendToAdmin("🟢 <b>{$site} Bot</b> đã khởi động\n🕐 " . date('H:i:s d/m/Y'));

$total = 0;
while (true) {
    try {
        $result = TelegramBot::poll();

        if (!$result['ok']) {
            echo "[" . date('H:i:s') . "] ERROR: " . ($result['message'] ?? 'unknown') . "\n";
            sleep(10); // back-off khi lỗi
            continue;
        }

        if ($result['processed'] > 0) {
            $total += $result['processed'];
            foreach ($result['log'] as $entry) {
                echo "[" . $entry['time'] . "] "
                    . $entry['icon'] . " " . $entry['action'] . "\n"
                    . "  IN : " . $entry['in'] . "\n"
                    . "  OUT: " . mb_substr($entry['out'], 0, 100, 'UTF-8') . "\n\n";
            }
            echo "  [Tổng đã xử lý: {$total} tin nhắn]\n";
        }
    } catch (Exception $e) {
        echo "[" . date('H:i:s') . "] EXCEPTION: " . $e->getMessage() . "\n";
        sleep(5);
    }

    sleep(3);
}
