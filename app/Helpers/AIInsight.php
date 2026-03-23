<?php
/**
 * AIInsight — Phân tích nhật ký hoạt động bằng Groq AI
 * Dùng cùng AI_API_KEY đã cấu hình trong config/app.php
 */
class AIInsight {

    private static $cacheFile = null;

    // ── Cache ────────────────────────────────────────────────────────

    private static function getCacheFile() {
        if (self::$cacheFile === null) {
            $dir = __DIR__ . '/../../storage';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            self::$cacheFile = $dir . '/ai_insight_cache.json';
        }
        return self::$cacheFile;
    }

    /** Trả về cache nếu còn trong TTL (mặc định 6 giờ), null nếu hết hạn */
    public static function getCached($ttlHours = 6) {
        $file = self::getCacheFile();
        if (!file_exists($file)) return null;
        $data = json_decode(file_get_contents($file), true);
        if (!$data || empty($data['text']) || empty($data['generated_at'])) return null;
        if (time() - strtotime($data['generated_at']) > $ttlHours * 3600) return null;
        return $data;
    }

    /** Tạo báo cáo mới và lưu cache */
    public static function generateAndCache($hoursBack = 24) {
        $result = self::getAdminInsight($hoursBack);
        $data = array(
            'text'         => $result['text'],
            'generated_at' => date('Y-m-d H:i:s'),
            'hours_back'   => $hoursBack,
            'success'      => $result['success'],
        );
        @file_put_contents(self::getCacheFile(), json_encode($data, JSON_UNESCAPED_UNICODE));
        return $data;
    }

    // ── Core ─────────────────────────────────────────────────────────

    /** Truy vấn action_logs và trả về phân tích AI dạng tiếng Việt */
    public static function getAdminInsight($hoursBack = 24) {
        $apiKey = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
        if (!$apiKey) {
            return array('success' => false, 'text' => '⚠️ Chưa cấu hình AI_API_KEY trong config/app.php.');
        }

        try {
            $db = Database::getInstance();

            $logs = $db->fetchAll(
                "SELECT action, table_name, user_name, user_role, old_data, new_data, ip_address, created_at
                 FROM action_logs
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                 ORDER BY created_at DESC
                 LIMIT 300",
                array($hoursBack)
            );

            // Thêm thống kê đơn hàng & doanh thu
            $revStats = $db->fetch(
                "SELECT COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue
                 FROM orders
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR) AND status != 'cancelled'",
                array($hoursBack)
            );

            if (empty($logs) && (!$revStats || $revStats['orders'] == 0)) {
                return array('success' => true, 'text' => "ℹ️ Không có hoạt động nào trong {$hoursBack} giờ qua.");
            }

            $prompt = self::buildPrompt($logs, $revStats, $hoursBack);
            $text   = self::callGroq($prompt);

            return array('success' => true, 'text' => $text);

        } catch (Exception $e) {
            return array('success' => false, 'text' => '❌ Lỗi: ' . $e->getMessage());
        }
    }

    /** Phân tích lỗi PHP và đề xuất sửa */
    public static function analyzeError($errorMessage, $file = '', $line = 0) {
        $apiKey = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
        if (!$apiKey) return null;

        $prompt = "Đây là lỗi PHP trong ứng dụng e-commerce (PHP + MySQL, không dùng framework):\n\n"
                . "Lỗi: {$errorMessage}\n"
                . ($file ? "File: {$file}" . ($line ? " dòng {$line}" : '') . "\n" : '')
                . "\nHãy:\n1. Giải thích nguyên nhân lỗi bằng tiếng Việt (2-3 câu)\n"
                . "2. Đưa ra cách sửa cụ thể\n"
                . "3. Cho biết mức độ nghiêm trọng: THẤP / TRUNG BÌNH / CAO\n"
                . "Trả lời ngắn gọn, thực tế.";

        return self::callGroq($prompt, 512);
    }

    // ── Prompt builder ───────────────────────────────────────────────

    private static function buildPrompt($logs, $revStats, $hoursBack) {
        $actionCounts  = array();
        $tableCounts   = array();
        $userActivity  = array();
        $priceChanges  = array();
        $accessDenied  = array();
        $loginEvents   = array();
        $deleteEvents  = array();
        $errorCount    = 0;

        foreach ($logs as $log) {
            $action = $log['action'];
            $table  = $log['table_name'];
            $user   = $log['user_name'];
            $role   = (int)$log['user_role'];
            $rLabel = $role===1 ? 'Admin' : ($role===2 ? 'Manager' : ($role===3 ? 'Staff' : 'User'));
            $uKey   = "{$user}({$rLabel})";
            $time   = date('H:i', strtotime($log['created_at']));

            $actionCounts[$action] = ($actionCounts[$action] ?? 0) + 1;
            $tableCounts[$table]   = ($tableCounts[$table]   ?? 0) + 1;
            $userActivity[$uKey]   = ($userActivity[$uKey]   ?? 0) + 1;

            if ($action === 'UPDATE' && $table === 'products') {
                $old = $log['old_data'] ? json_decode($log['old_data'], true) : null;
                $new = $log['new_data'] ? json_decode($log['new_data'], true) : null;
                if ($old && $new && isset($old['price']) && isset($new['price'])) {
                    $diff = (float)$new['price'] - (float)$old['price'];
                    $pct  = $old['price'] > 0 ? round($diff / $old['price'] * 100, 1) : 0;
                    $priceChanges[] = "{$user} [{$time}]: " . number_format($old['price']) . " → " . number_format($new['price']) . " ({$pct}%)";
                }
            }

            if ($action === 'ACCESS_DENIED') {
                $accessDenied[] = "{$user}({$rLabel}) cố truy cập {$table} lúc {$time} từ " . ($log['ip_address'] ?? '?');
                $errorCount++;
            }

            if ($action === 'LOGIN') {
                $loginEvents[] = "{$user}({$rLabel}) lúc {$time} IP:" . ($log['ip_address'] ?? '?');
            }

            if ($action === 'DELETE') {
                $deleteEvents[] = "{$user}({$rLabel}) xóa từ {$table} lúc {$time}";
            }
        }

        arsort($userActivity);

        $lines = array();
        $lines[] = "=== BÁO CÁO HOẠT ĐỘNG {$hoursBack} GIỜ QUA ===";
        $lines[] = "Tổng hành động ghi nhận: " . count($logs);

        if ($revStats && $revStats['orders'] > 0) {
            $lines[] = "\n📦 DOANH THU: " . $revStats['orders'] . " đơn hàng, tổng " . number_format($revStats['revenue']) . "đ";
        }

        $lines[] = "\n📊 HÀNH ĐỘNG:";
        foreach ($actionCounts as $a => $c) $lines[] = "  {$a}: {$c}";

        $lines[] = "\n🗄️ BẢNG DỮ LIỆU:";
        foreach ($tableCounts as $t => $c) $lines[] = "  {$t}: {$c} lần";

        $lines[] = "\n👤 HOẠT ĐỘNG NHÂN SỰ (top 8):";
        foreach (array_slice($userActivity, 0, 8, true) as $u => $c) $lines[] = "  {$u}: {$c} hành động";

        if (!empty($priceChanges)) {
            $lines[] = "\n💰 THAY ĐỔI GIÁ:";
            foreach (array_slice($priceChanges, 0, 8) as $p) $lines[] = "  - {$p}";
        }

        if (!empty($accessDenied)) {
            $lines[] = "\n🔐 TRUY CẬP TRÁI PHÉP:";
            foreach (array_slice($accessDenied, 0, 6) as $s) $lines[] = "  ⚠️ {$s}";
        }

        if (!empty($loginEvents)) {
            $lines[] = "\n🔑 ĐĂNG NHẬP:";
            foreach (array_slice($loginEvents, 0, 6) as $l) $lines[] = "  {$l}";
        }

        if (!empty($deleteEvents)) {
            $lines[] = "\n🗑️ XÓA DỮ LIỆU:";
            foreach (array_slice($deleteEvents, 0, 6) as $d) $lines[] = "  {$d}";
        }

        $summary = implode("\n", $lines);

        return "Dưới đây là nhật ký hoạt động hệ thống cửa hàng máy tính Tuấn Huy.\n\n"
             . $summary
             . "\n\nHãy viết báo cáo phân tích tiếng Việt gồm:\n"
             . "1. 📋 Tóm tắt tổng quan\n"
             . "2. ⚠️ Điểm bất thường hoặc đáng chú ý\n"
             . "3. 💡 Đề xuất cho Admin\n"
             . "Ngắn gọn, rõ ràng, dùng emoji, tối đa 350 từ.";
    }

    // ── Groq call ────────────────────────────────────────────────────

    public static function callGroq($prompt, $maxTokens = 1500) {
        $apiKey = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
        if (!$apiKey) return 'Chưa cấu hình AI_API_KEY.';

        $messages = array(
            array('role' => 'system', 'content' =>
                'Bạn là trợ lý phân tích quản trị cho cửa hàng máy tính Tuấn Huy Computer. '
                . 'Luôn trả lời bằng tiếng Việt, ngắn gọn, thực tế, dùng emoji phù hợp.'),
            array('role' => 'user', 'content' => $prompt),
        );

        $models = array('llama-3.3-70b-versatile', 'mixtral-8x7b-32768');

        foreach ($models as $model) {
            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ),
                CURLOPT_POSTFIELDS => json_encode(array(
                    'model'       => $model,
                    'max_tokens'  => $maxTokens,
                    'temperature' => 0.3,
                    'messages'    => $messages,
                ), JSON_UNESCAPED_UNICODE),
            ));
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200) {
                $data = json_decode($resp, true);
                if (isset($data['choices'][0]['message']['content'])) {
                    return trim($data['choices'][0]['message']['content']);
                }
            }
            if ($code === 401 || $code === 403) break; // sai key
        }

        return '❌ Không thể kết nối AI. Kiểm tra AI_API_KEY và thử lại.';
    }
}
