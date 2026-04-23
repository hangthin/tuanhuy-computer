<?php
/**
 * bot_tick.php — chạy một lần, xử lý tin nhắn pending rồi thoát.
 * Dùng với Task Scheduler (chạy mỗi 1 phút).
 * Ưu tiên AI Bot Controller; fallback TelegramBot nếu AI bot không load được.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/Helpers/TelegramNotifier.php';

set_time_limit(55);

$aiControllerFile = __DIR__ . '/Controllers/TelegramBotController.php';

if (file_exists($aiControllerFile)) {
    require_once $aiControllerFile;
    $aiBot = new TelegramBotController();
    $aiBot->tick();
} else {
    // Fallback to legacy bot
    require_once __DIR__ . '/Helpers/TelegramBot.php';
    $result = TelegramBot::poll();
    if (!empty($result['log'])) {
        $logFile = __DIR__ . '/../storage/bot_log.txt';
        $lines   = '';
        foreach ($result['log'] as $e) {
            $lines .= '[' . $e['time'] . '] ' . $e['icon'] . ' ' . $e['action'] . ' | IN: ' . $e['in'] . "\n";
        }
        file_put_contents($logFile, $lines, FILE_APPEND | LOCK_EX);
    }
}
