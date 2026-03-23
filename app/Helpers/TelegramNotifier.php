<?php
/**
 * TelegramNotifier — gửi thông báo qua Telegram Bot API
 *
 * Cấu hình trong config/app.php:
 *   TELEGRAM_BOT_TOKEN  — token từ @BotFather
 *   TELEGRAM_ADMIN_CHAT — Chat ID của admin (user ID hoặc group ID)
 *
 * Lấy Chat ID:
 *   1. Nhắn /start cho bot
 *   2. Mở https://api.telegram.org/bot{TOKEN}/getUpdates
 *   3. Tìm "chat":{"id": ...}
 */
class TelegramNotifier {

    /** Gửi tin nhắn tới một chat ID cụ thể */
    public static function send($chatId, $text) {
        $token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
        if (!$token || !$chatId) return false;

        $url     = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = json_encode(array(
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ));

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res  = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('[TelegramNotifier] cURL error: ' . $err);
            return false;
        }

        $data = json_decode($res, true);
        if (!isset($data['ok']) || !$data['ok']) {
            error_log('[TelegramNotifier] API error: ' . $res);
            return false;
        }

        return true;
    }

    /** Gửi cho admin (dùng TELEGRAM_ADMIN_CHAT) */
    public static function sendToAdmin($text) {
        $chatId = defined('TELEGRAM_ADMIN_CHAT') ? TELEGRAM_ADMIN_CHAT : '';
        return self::send($chatId, $text);
    }

    /** Kiểm tra đã cấu hình đủ chưa */
    public static function isConfigured() {
        return defined('TELEGRAM_BOT_TOKEN') && TELEGRAM_BOT_TOKEN !== ''
            && defined('TELEGRAM_ADMIN_CHAT') && TELEGRAM_ADMIN_CHAT !== '';
    }

    // ─────────────────────────────────────────────────────────────────
    // Pre-built notification templates
    // ─────────────────────────────────────────────────────────────────

    /** Thông báo tài khoản bị khóa do vi phạm bảo mật */
    public static function notifyLockout($name, $email, $reason = '') {
        $site = defined('APP_NAME') ? APP_NAME : 'Admin';
        $msg  = "🔒 <b>[{$site}] Tài khoản bị khóa</b>\n\n"
              . "👤 <b>{$name}</b> ({$email})\n"
              . "📋 Lý do: " . ($reason ?: 'Truy cập trái phép nhiều lần') . "\n"
              . "🕐 Thời gian: " . date('H:i:s d/m/Y') . "\n\n"
              . "⚠️ Vào Admin → Nhân sự để mở khóa nếu cần.";
        return self::sendToAdmin($msg);
    }

    /** Gửi báo cáo AI hàng ngày */
    public static function notifyDailyReport($reportText) {
        $site    = defined('APP_NAME') ? APP_NAME : 'Admin';
        $preview = mb_substr(strip_tags($reportText), 0, 600, 'UTF-8');
        if (mb_strlen($reportText, 'UTF-8') > 600) $preview .= '…';
        $msg = "🤖 <b>[{$site}] Báo cáo AI</b>\n"
             . "📅 " . date('H:i d/m/Y') . "\n\n"
             . $preview;
        return self::sendToAdmin($msg);
    }

    /** Thông báo lỗi 500 */
    public static function notifyError500($errorMsg, $url = '', $aiSuggestion = '') {
        $site = defined('APP_NAME') ? APP_NAME : 'Admin';
        $msg  = "🚨 <b>[{$site}] Lỗi 500</b>\n\n"
              . "❌ " . htmlspecialchars(mb_substr($errorMsg, 0, 300, 'UTF-8')) . "\n";
        if ($url)          $msg .= "🔗 " . htmlspecialchars(mb_substr($url, 0, 150, 'UTF-8')) . "\n";
        if ($aiSuggestion) $msg .= "\n💡 <b>AI gợi ý:</b>\n" . htmlspecialchars(mb_substr($aiSuggestion, 0, 400, 'UTF-8'));
        $msg .= "\n🕐 " . date('H:i:s d/m/Y');
        return self::sendToAdmin($msg);
    }

    /** Thông báo đơn hàng mới */
    public static function notifyNewOrder($orderId, $customerName, $total) {
        $site = defined('APP_NAME') ? APP_NAME : 'Admin';
        $msg  = "🛒 <b>[{$site}] Đơn hàng mới #" . $orderId . "</b>\n\n"
              . "👤 " . htmlspecialchars($customerName) . "\n"
              . "💰 " . number_format((float)$total, 0, ',', '.') . "đ\n"
              . "🕐 " . date('H:i d/m/Y');
        return self::sendToAdmin($msg);
    }

    /** Thông báo hàng tồn kho thấp */
    public static function notifyLowStock($productName, $stock) {
        $site = defined('APP_NAME') ? APP_NAME : 'Admin';
        $msg  = "📦 <b>[{$site}] Cảnh báo tồn kho</b>\n\n"
              . "🏷️ " . htmlspecialchars($productName) . "\n"
              . "⚠️ Còn lại: <b>{$stock}</b> sản phẩm\n"
              . "🕐 " . date('H:i d/m/Y');
        return self::sendToAdmin($msg);
    }
}
