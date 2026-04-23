<?php
/**
 * Cron — gọi từ cron-job.org mỗi phút để giữ server thức 24/7
 * URL: https://[your-domain]/cron.php?token=tuanhuy_cron_2024
 *
 * Nhiệm vụ:
 *  1. Keep-alive (ngăn Render/server ngủ)
 *  2. Tự đăng ký Telegram webhook lần đầu (nếu chưa có)
 *  3. Báo cáo AI hằng ngày lúc 08:00
 *  4. Dọn ảnh orphan (~1% requests)
 */

$secret = getenv('TELEGRAM_CRON_SECRET') ?: 'tuanhuy_cron_2024';
$token  = $_GET['token'] ?? '';
if (!$token || $token !== $secret) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Forbidden']);
    exit;
}

define('APP_DIR', __DIR__);
ini_set('display_errors', 0);
error_reporting(0);

$result = array('ok' => true, 'ts' => date('Y-m-d H:i:s'), 'tasks' => array());

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/app.php';
    require_once __DIR__ . '/app/Helpers/TelegramNotifier.php';
    require_once __DIR__ . '/app/Controllers/TelegramBotController.php';

    header('Content-Type: application/json; charset=utf-8');

    $bot = new TelegramBotController();

    // ── 1. Tự đăng ký webhook (chỉ gọi API Telegram khi cần) ─────────
    // Kiểm tra file flag để tránh gọi API mỗi phút
    $storageDir  = __DIR__ . '/storage';
    if (!is_dir($storageDir)) @mkdir($storageDir, 0755, true);
    $webhookFlag = $storageDir . '/webhook_registered.json';
    $flagData    = file_exists($webhookFlag) ? json_decode(file_get_contents($webhookFlag), true) : array();
    $lastCheck   = (int)($flagData['ts'] ?? 0);

    // Kiểm tra lại mỗi 6 giờ hoặc khi flag chưa có
    if (time() - $lastCheck > 21600 || empty($flagData['active'])) {
        $wh = $bot->registerWebhookIfNeeded();
        $result['tasks']['webhook'] = $wh['status'] ?? 'checked';

        // Lưu flag
        @file_put_contents($webhookFlag, json_encode(array(
            'ts'     => time(),
            'active' => in_array($wh['status'] ?? '', array('already_active', 'registered')),
            'url'    => $wh['url'] ?? '',
        )));
    } else {
        $result['tasks']['webhook'] = 'active (cached)';
    }

    // ── 2. Báo cáo AI hằng ngày ───────────────────────────────────────
    $schedHour = defined('REPORT_SCHEDULE_HOUR') ? (int)REPORT_SCHEDULE_HOUR : 8;
    if ((int)date('H') === $schedHour && (int)date('i') < 5) {
        $schedFile = $storageDir . '/ai_report_sent.json';
        $sentLog   = (file_exists($schedFile) ? json_decode(file_get_contents($schedFile), true) : null) ?: array();
        $todayKey  = date('Y-m-d') . '_h' . $schedHour;

        if (empty($sentLog[$todayKey])) {
            try {
                require_once __DIR__ . '/app/Helpers/AIInsight.php';
                if (TelegramNotifier::isConfigured()) {
                    $rpt  = AIInsight::generateAndCache(24);
                    $text = trim($rpt['text'] ?? '');
                    if ($text) {
                        TelegramNotifier::sendToAdmin(
                            "🧠 <b>Báo cáo AI hằng ngày</b> — " . date('d/m/Y H:i') . "\n\n" . $text
                        );
                        $sentLog[$todayKey] = date('Y-m-d H:i:s');
                        if (count($sentLog) > 14) $sentLog = array_slice($sentLog, -14, 14, true);
                        @file_put_contents($schedFile, json_encode($sentLog, JSON_PRETTY_PRINT));
                        $result['tasks']['ai_report'] = 'sent';
                    }
                }
            } catch (Throwable $re) {
                $result['tasks']['ai_report'] = 'error: ' . $re->getMessage();
            }
        } else {
            $result['tasks']['ai_report'] = 'already_sent_today';
        }
    }

    // ── 3. Dọn ảnh orphan (~1% requests) ─────────────────────────────
    if (mt_rand(1, 100) === 1) {
        try {
            $db         = Database::getInstance();
            $refs       = array();
            foreach ($db->fetchAll("SELECT image FROM products WHERE image IS NOT NULL AND image != ''") as $r) {
                $refs[basename($r['image'])] = true;
            }
            foreach ($db->fetchAll("SELECT image FROM product_images WHERE image IS NOT NULL AND image != ''") as $r) {
                $refs[basename($r['image'])] = true;
            }
            $uploadDir = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/uploads/products/';
            $cutoff    = time() - 90 * 86400;
            $deleted   = 0;
            foreach (glob($uploadDir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: array() as $file) {
                if (!isset($refs[basename($file)]) && filemtime($file) < $cutoff) {
                    @unlink($file);
                    $deleted++;
                }
            }
            $result['tasks']['image_cleanup'] = "deleted {$deleted}";
        } catch (Throwable $ce) {
            $result['tasks']['image_cleanup'] = 'error: ' . $ce->getMessage();
        }
    }

} catch (Throwable $e) {
    $result['ok']    = false;
    $result['error'] = $e->getMessage();
    header('Content-Type: application/json; charset=utf-8');
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
