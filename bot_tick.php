<?php
/**
 * bot_tick.php — chạy một lần, xử lý tin nhắn pending rồi thoát.
 * Dùng với Task Scheduler (chạy mỗi 1 phút).
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/Helpers/TelegramBot.php';
require_once __DIR__ . '/app/Helpers/TelegramNotifier.php';

set_time_limit(30);

$result = TelegramBot::poll();

if (!empty($result['log'])) {
    $logFile = __DIR__ . '/storage/bot_log.txt';
    $lines   = '';
    foreach ($result['log'] as $e) {
        $lines .= '[' . $e['time'] . '] ' . $e['icon'] . ' ' . $e['action'] . ' | IN: ' . $e['in'] . "\n";
    }
    file_put_contents($logFile, $lines, FILE_APPEND | LOCK_EX);
}
