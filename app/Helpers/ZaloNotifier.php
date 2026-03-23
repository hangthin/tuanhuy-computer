<?php
/**
 * ZaloNotifier — Gửi thông báo qua Zalo OA API
 *
 * Cấu hình cần thiết trong config/app.php:
 *   define('ZALO_OA_TOKEN',  '...');  // Lấy từ oa.zalo.me → Cài đặt → Tích hợp API
 *   define('ZALO_ADMIN_ID',  '...');  // User ID Zalo của Admin
 *                                     // (xem tại oa.zalo.me → Người quan tâm → click vào admin)
 *
 * Lưu ý: Người nhận phải đã quan tâm OA trước khi có thể nhận tin.
 */
class ZaloNotifier {

    /**
     * Gửi tin nhắn Zalo đến một người dùng
     *
     * @param string $userId  Zalo User ID của người nhận (không phải số điện thoại)
     * @param string $message Nội dung tin nhắn (tối đa 2000 ký tự)
     * @return bool
     */
    public static function sendZaloMessage($userId, $message) {
        $token = defined('ZALO_OA_TOKEN') && ZALO_OA_TOKEN ? ZALO_OA_TOKEN : '';
        if (!$token || !$userId) return false;

        $message = mb_substr($message, 0, 2000, 'UTF-8');

        $payload = json_encode(array(
            'recipient' => array('user_id' => (string)$userId),
            'message'   => array('text'    => $message),
        ), JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://openapi.zalo.me/v2.0/oa/message/cs');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'access_token: ' . $token,
            ),
            CURLOPT_POSTFIELDS => $payload,
        ));
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) return false;
        $data = json_decode($resp, true);
        return isset($data['error']) && $data['error'] === 0;
    }

    /** Gửi tin nhắn đến Admin được cấu hình trong ZALO_ADMIN_ID */
    public static function sendToAdmin($message) {
        $adminId = defined('ZALO_ADMIN_ID') && ZALO_ADMIN_ID ? ZALO_ADMIN_ID : '';
        return self::sendZaloMessage($adminId, $message);
    }

    /** Thông báo khi tài khoản bị khóa tự động */
    public static function notifyLockout($lockedUserName, $lockedEmail, $reason = '') {
        $msg = "🔒 CẢNH BÁO BẢO MẬT — Tuấn Huy Computer\n\n"
             . "Tài khoản đã bị khóa tự động:\n"
             . "👤 " . $lockedUserName . "\n"
             . "📧 " . $lockedEmail . "\n"
             . ($reason ? "❓ Lý do: " . $reason . "\n" : "")
             . "🕐 Thời gian: " . date('d/m/Y H:i:s') . "\n\n"
             . "→ Vào Admin Panel để mở khóa nếu cần.";
        return self::sendToAdmin($msg);
    }

    /** Thông báo báo cáo AI hàng ngày */
    public static function notifyDailyReport($reportText) {
        $msg = "📊 BÁO CÁO HÀNG NGÀY — " . date('d/m/Y') . "\n"
             . "Tuấn Huy Computer\n\n"
             . mb_substr(strip_tags($reportText), 0, 1800, 'UTF-8');
        return self::sendToAdmin($msg);
    }

    /** Thông báo lỗi 500 kèm phân tích AI */
    public static function notifyError500($errorMessage, $url = '', $aiSuggestion = '') {
        $msg = "🚨 LỖI HỆ THỐNG 500 — Tuấn Huy Computer\n\n"
             . "🕐 " . date('d/m/Y H:i:s') . "\n"
             . ($url ? "🔗 URL: " . mb_substr($url, 0, 100) . "\n" : '')
             . "❌ Lỗi: " . mb_substr($errorMessage, 0, 300, 'UTF-8') . "\n"
             . ($aiSuggestion ? "\n💡 AI phân tích:\n" . mb_substr($aiSuggestion, 0, 600, 'UTF-8') : '');
        return self::sendToAdmin($msg);
    }

    /** Kiểm tra cấu hình Zalo có đầy đủ không */
    public static function isConfigured() {
        return defined('ZALO_OA_TOKEN') && ZALO_OA_TOKEN
            && defined('ZALO_ADMIN_ID') && ZALO_ADMIN_ID;
    }
}
