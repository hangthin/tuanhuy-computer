<?php
/**
 * AI Telegram Bot Daemon
 *
 * Chạy liên tục, dùng long-polling 25s — bot phản hồi gần tức thì khi nhận tin.
 * Không cần mở web, không cần cron.
 *
 * Khởi động: php app/bot_daemon.php      (hoặc double-click start_bot.bat)
 * Dừng:      Ctrl+C
 */

define('STDIN_DEFINED', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/Helpers/TelegramNotifier.php';
require_once __DIR__ . '/Controllers/TelegramBotController.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_implicit_flush(true);

$bot = new TelegramBotController();
$bot->startDaemon();
