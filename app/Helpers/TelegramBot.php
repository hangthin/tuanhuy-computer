<?php
/**
 * TelegramBot — nhận lệnh từ Telegram và thực thi trên hệ thống
 *
 * Polling: JS admin page gọi /api/telegram/poll mỗi 5 giây
 * Security: chỉ xử lý tin nhắn từ TELEGRAM_ADMIN_CHAT
 */
class TelegramBot {

    private static $offsetFile = '';

    private static function offsetFile() {
        if (!self::$offsetFile) {
            self::$offsetFile = __DIR__ . '/../../storage/telegram_offset.txt';
        }
        return self::$offsetFile;
    }

    // ─────────────────────────────────────────────────────────────────
    // Poll Telegram getUpdates and process each message
    // ─────────────────────────────────────────────────────────────────
    public static function poll() {
        $token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
        if (!$token) return array('ok' => false, 'message' => 'Bot chưa cấu hình token.');

        $offset = 0;
        $f = self::offsetFile();
        if (file_exists($f)) $offset = (int)file_get_contents($f);

        $url = 'https://api.telegram.org/bot' . $token
             . '/getUpdates?offset=' . $offset . '&limit=20&timeout=0';

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) return array('ok' => false, 'message' => 'cURL: ' . $err);

        $data = json_decode($res, true);
        if (!$data || empty($data['ok'])) {
            return array('ok' => false, 'message' => 'Telegram API error: ' . $res);
        }

        if (empty($data['result'])) return array('ok' => true, 'processed' => 0, 'log' => array());

        $adminChat = defined('TELEGRAM_ADMIN_CHAT') ? (string)TELEGRAM_ADMIN_CHAT : '';
        $log       = array();

        foreach ($data['result'] as $update) {
            // Save next offset
            file_put_contents($f, (int)$update['update_id'] + 1);

            if (empty($update['message']['text'])) continue;

            $msg    = $update['message'];
            $chatId = (string)$msg['chat']['id'];
            $text   = trim($msg['text']);

            // Security: reject non-admin chats
            if ($adminChat && $chatId !== $adminChat) {
                self::reply($chatId, '⛔ Bạn không có quyền dùng bot này.');
                continue;
            }

            $reply  = self::dispatch($chatId, $text);
            $action = self::describeAction($text);
            $log[]  = array(
                'in'     => $text,
                'action' => $action['label'],
                'icon'   => $action['icon'],
                'out'    => strip_tags($reply),
                'time'   => date('H:i:s'),
            );
        }

        return array('ok' => true, 'processed' => count($log), 'log' => $log);
    }

    // ─────────────────────────────────────────────────────────────────
    // Describe what action was taken (for log display)
    // ─────────────────────────────────────────────────────────────────
    private static function describeAction($text) {
        $t = mb_strtolower(trim($text), 'UTF-8');
        if ($t[0] === '/') $t = substr($t, 1);

        if (in_array($t, array('start','help','giúp','giup','menu','?')))
            return array('icon'=>'📋','label'=>'Xem danh sách lệnh');
        if (in_array($t, array('thống kê','thong ke','stats','dashboard')))
            return array('icon'=>'📊','label'=>'Truy vấn thống kê tổng quan');
        if (strpos($t,'doanh thu')!==false || $t==='revenue')
            return array('icon'=>'💰','label'=>'Truy vấn doanh thu');
        if (strpos($t,'báo cáo')!==false || strpos($t,'bao cao')!==false || $t==='report')
            return array('icon'=>'🤖','label'=>'Tạo báo cáo AI (Groq)');
        if (preg_match('/(xác nhận|xac nhan|confirm)\s*#?(\d+)/iu', $text, $m))
            return array('icon'=>'✅','label'=>'Xác nhận đơn hàng #'.$m[2]);
        if (preg_match('/(hủy|huy|cancel)\s*(đơn|don|order)?\s*#?(\d+)/iu', $text, $m))
            return array('icon'=>'❌','label'=>'Hủy đơn hàng #'.$m[3]);
        if (preg_match('/(đơn|don|order)\s*#?(\d+)/iu', $text, $m))
            return array('icon'=>'🔍','label'=>'Xem chi tiết đơn #'.$m[2]);
        if ((strpos($t,'đơn hàng')!==false || strpos($t,'don hang')!==false || $t==='orders') && !preg_match('/\d+/',$t))
            return array('icon'=>'🛒','label'=>'Truy vấn 5 đơn hàng mới nhất');
        if (strpos($t,'tồn kho')!==false || strpos($t,'ton kho')!==false || $t==='stock')
            return array('icon'=>'📦','label'=>'Kiểm tra tồn kho thấp');
        if (strpos($t,'sản phẩm')!==false || strpos($t,'san pham')!==false || $t==='products')
            return array('icon'=>'🏷️','label'=>'Truy vấn danh sách sản phẩm');
        if (strpos($t,'khách hàng')!==false || strpos($t,'khach hang')!==false || $t==='customers')
            return array('icon'=>'👤','label'=>'Truy vấn khách hàng mới');
        if (preg_match('/^(khóa|khoa)\s+(.+)/iu', $text, $m))
            return array('icon'=>'🔒','label'=>'Khóa tài khoản: '.mb_substr(trim($m[2]),0,30,'UTF-8'));
        if (preg_match('/^(mở khóa|mo khoa|mở|mo)\s+(.+)/iu', $text, $m))
            return array('icon'=>'🔓','label'=>'Mở khóa tài khoản: '.mb_substr(trim($m[2]),0,30,'UTF-8'));
        return array('icon'=>'💬','label'=>'Hỏi AI: '.mb_substr($text,0,40,'UTF-8'));
    }

    // ─────────────────────────────────────────────────────────────────
    // Command dispatcher
    // ─────────────────────────────────────────────────────────────────
    private static function dispatch($chatId, $text) {
        $t = mb_strtolower(trim($text), 'UTF-8');
        // Strip leading slash
        if (strlen($t) > 0 && $t[0] === '/') $t = substr($t, 1);

        // Help / Start
        if (in_array($t, array('start','help','giúp','giup','menu','?'))) {
            return self::cmdHelp($chatId);
        }
        // Stats
        if (in_array($t, array('thống kê','thong ke','stats','dashboard'))) {
            return self::cmdStats($chatId);
        }
        // Revenue
        if (strpos($t,'doanh thu') !== false || $t === 'revenue') {
            return self::cmdRevenue($chatId);
        }
        // AI Report
        if (strpos($t,'báo cáo') !== false || strpos($t,'bao cao') !== false || $t === 'report') {
            return self::cmdReport($chatId);
        }
        // Orders list
        if ((strpos($t,'đơn hàng') !== false || strpos($t,'don hang') !== false || $t === 'orders')
            && !preg_match('/\d+/', $t)) {
            return self::cmdOrders($chatId);
        }
        // Order detail: "đơn 123" or "order #123"
        if (preg_match('/(đơn|don|order)\s*#?(\d+)/iu', $text, $m)) {
            return self::cmdOrderDetail($chatId, (int)$m[2]);
        }
        // Low stock
        if (strpos($t,'tồn kho') !== false || strpos($t,'ton kho') !== false
            || strpos($t,'hàng thấp') !== false || strpos($t,'hang thap') !== false
            || $t === 'stock') {
            return self::cmdStock($chatId);
        }
        // Products
        if (strpos($t,'sản phẩm') !== false || strpos($t,'san pham') !== false
            || $t === 'products') {
            return self::cmdProducts($chatId);
        }
        // Customers
        if (strpos($t,'khách hàng') !== false || strpos($t,'khach hang') !== false
            || $t === 'customers') {
            return self::cmdCustomers($chatId);
        }
        // Lock user
        if (preg_match('/^(khóa|khoa)\s+(.+)/iu', $text, $m)) {
            return self::cmdLock($chatId, trim($m[2]), true);
        }
        // Unlock user
        if (preg_match('/^(mở khóa|mo khoa|mở|mo)\s+(.+)/iu', $text, $m)) {
            return self::cmdLock($chatId, trim($m[2]), false);
        }
        // Confirm order: "xác nhận đơn 123"
        if (preg_match('/(xác nhận|xac nhan|confirm)\s*#?(\d+)/iu', $text, $m)) {
            return self::cmdConfirmOrder($chatId, (int)$m[2]);
        }
        // Cancel order: "hủy đơn 123"
        if (preg_match('/(hủy|huy|cancel)\s*(đơn|don|order)?\s*#?(\d+)/iu', $text, $m)) {
            return self::cmdCancelOrder($chatId, (int)$m[3]);
        }
        // Unknown → AI chat
        return self::cmdAI($chatId, $text);
    }

    // ─────────────────────────────────────────────────────────────────
    // Command handlers
    // ─────────────────────────────────────────────────────────────────
    private static function cmdHelp($chatId) {
        $site = defined('APP_NAME') ? APP_NAME : 'Admin';
        $msg  = "🤖 <b>{$site} Bot</b>\n\n"
              . "<b>📊 Thống kê</b>\n"
              . "  <code>thống kê</code> — Dashboard tổng quan\n"
              . "  <code>doanh thu</code> — Doanh thu hôm nay & tháng\n"
              . "  <code>báo cáo</code> — Tạo báo cáo AI\n\n"
              . "<b>🛒 Đơn hàng</b>\n"
              . "  <code>đơn hàng</code> — 5 đơn mới nhất\n"
              . "  <code>đơn 123</code> — Chi tiết đơn #123\n"
              . "  <code>xác nhận 123</code> — Xác nhận đơn\n"
              . "  <code>hủy đơn 123</code> — Hủy đơn\n\n"
              . "<b>📦 Kho hàng</b>\n"
              . "  <code>tồn kho</code> — Sản phẩm sắp hết\n"
              . "  <code>sản phẩm</code> — Danh sách sản phẩm\n\n"
              . "<b>👤 Tài khoản</b>\n"
              . "  <code>khách hàng</code> — Khách mới\n"
              . "  <code>khóa [tên/email]</code> — Khóa tài khoản\n"
              . "  <code>mở khóa [tên/email]</code> — Mở khóa\n\n"
              . "💬 Hoặc hỏi bất cứ điều gì bằng tiếng Việt!";
        return self::reply($chatId, $msg);
    }

    private static function cmdStats($chatId) {
        try {
            $db   = Database::getInstance();
            $rev  = $db->fetch("SELECT COALESCE(SUM(total),0) AS t FROM orders WHERE DATE(created_at)=CURDATE() AND status NOT IN ('cancelled')");
            $ord  = $db->fetch("SELECT COUNT(*) AS t FROM orders WHERE DATE(created_at)=CURDATE()");
            $pend = $db->fetch("SELECT COUNT(*) AS t FROM orders WHERE status='pending'");
            $prod = $db->fetch("SELECT COUNT(*) AS t FROM products WHERE is_active=1 AND is_deleted=0");
            $cust = $db->fetch("SELECT COUNT(*) AS t FROM users WHERE role=0");
            $low  = $db->fetch("SELECT COUNT(*) AS t FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0 WHERE i.stock_quantity<=i.min_stock");

            $msg = "📊 <b>Dashboard — " . date('d/m/Y') . "</b>\n\n"
                 . "💰 Doanh thu hôm nay: <b>" . number_format((float)$rev['t'],0,',','.') . "đ</b>\n"
                 . "🛒 Đơn hôm nay: <b>" . $ord['t'] . "</b>\n"
                 . "⏳ Chờ xử lý: <b>" . $pend['t'] . " đơn</b>\n"
                 . "📦 Sản phẩm: <b>" . $prod['t'] . "</b>\n"
                 . "👤 Khách hàng: <b>" . $cust['t'] . "</b>\n"
                 . "⚠️ Hàng sắp hết: <b>" . $low['t'] . " sản phẩm</b>";
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdRevenue($chatId) {
        try {
            $db    = Database::getInstance();
            $today = $db->fetch("SELECT COALESCE(SUM(total),0) AS t, COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE() AND status NOT IN ('cancelled')");
            $week  = $db->fetch("SELECT COALESCE(SUM(total),0) AS t FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) AND status NOT IN ('cancelled')");
            $month = $db->fetch("SELECT COALESCE(SUM(total),0) AS t FROM orders WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW()) AND status NOT IN ('cancelled')");
            $top   = $db->fetchAll("SELECT od.product_name AS name, SUM(od.quantity) AS qty FROM order_details od JOIN orders o ON od.order_id=o.id WHERE DATE(o.created_at)=CURDATE() GROUP BY od.product_id ORDER BY qty DESC LIMIT 3");

            $msg = "💰 <b>Doanh thu</b>\n\n"
                 . "📅 Hôm nay: <b>" . number_format((float)$today['t'],0,',','.') . "đ</b> (" . $today['c'] . " đơn)\n"
                 . "📅 7 ngày: <b>" . number_format((float)$week['t'],0,',','.') . "đ</b>\n"
                 . "📅 Tháng này: <b>" . number_format((float)$month['t'],0,',','.') . "đ</b>\n";

            if ($top) {
                $msg .= "\n🏆 <b>Top sản phẩm hôm nay:</b>\n";
                foreach ($top as $i => $p) {
                    $msg .= ($i+1) . '. ' . mb_substr($p['name'],0,35,'UTF-8') . ' (' . $p['qty'] . ' cái)' . "\n";
                }
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdReport($chatId) {
        self::reply($chatId, '⏳ Đang tạo báo cáo AI, vui lòng chờ...');
        try {
            require_once __DIR__ . '/AIInsight.php';
            $result = AIInsight::generateAndCache(24);
            if ($result['success']) {
                $preview = mb_substr($result['text'], 0, 1000, 'UTF-8');
                if (mb_strlen($result['text'], 'UTF-8') > 1000) $preview .= "\n\n[... xem đầy đủ tại Admin Panel]";
                $msg = "🤖 <b>Báo cáo AI</b> — 24h qua\n\n" . $preview;
            } else {
                $msg = '❌ Không tạo được báo cáo: ' . ($result['message'] ?? 'Unknown error');
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdOrders($chatId) {
        try {
            $db    = Database::getInstance();
            $rows  = $db->fetchAll(
                "SELECT o.id, o.fullname, o.total, o.status, o.created_at
                 FROM orders o
                 ORDER BY o.created_at DESC LIMIT 5"
            );
            $statusMap = array(
                'pending'    => '⏳ Chờ',
                'confirmed'  => '✅ Xác nhận',
                'processing' => '🔧 Xử lý',
                'shipping'   => '🚚 Giao',
                'delivered'  => '✔️ Hoàn',
                'cancelled'  => '❌ Hủy',
            );
            $msg = "🛒 <b>5 đơn hàng mới nhất:</b>\n\n";
            foreach ($rows as $r) {
                $s    = $statusMap[$r['status']] ?? $r['status'];
                $name = mb_substr($r['fullname'] ?? 'Khách', 0, 20, 'UTF-8');
                $msg .= "#<b>{$r['id']}</b> {$name} — " . number_format((float)$r['total'],0,',','.') . "đ {$s}\n";
            }
            $msg .= "\nDùng <code>đơn [số]</code> để xem chi tiết.";
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdOrderDetail($chatId, $id) {
        try {
            $db  = Database::getInstance();
            $ord = $db->fetch("SELECT * FROM orders WHERE id=?", array($id));
            if (!$ord) return self::reply($chatId, "❌ Không tìm thấy đơn #{$id}");

            $items = $db->fetchAll(
                "SELECT quantity, price AS unit_price, product_name AS name FROM order_details WHERE order_id=?",
                array($id)
            );
            $statusMap = array(
                'pending'=>'⏳ Chờ xác nhận','confirmed'=>'✅ Đã xác nhận',
                'processing'=>'🔧 Đang xử lý','shipping'=>'🚚 Đang giao',
                'delivered'=>'✔️ Đã giao','cancelled'=>'❌ Đã hủy',
            );
            $s       = $statusMap[$ord['status']] ?? $ord['status'];
            $address = implode(', ', array_filter(array($ord['address'] ?? '', $ord['ward'] ?? '', $ord['district'] ?? '', $ord['city'] ?? '')));
            $msg = "📋 <b>Đơn hàng #{$id}</b>\n"
                 . "👤 " . htmlspecialchars($ord['fullname'] ?? 'Khách') . " | " . ($ord['phone'] ?? '') . "\n"
                 . "📍 " . mb_substr($address, 0, 60, 'UTF-8') . "\n"
                 . "💰 " . number_format((float)$ord['total'],0,',','.') . "đ | {$s}\n"
                 . "🕐 " . date('H:i d/m/Y', strtotime($ord['created_at'])) . "\n\n"
                 . "<b>Sản phẩm:</b>\n";
            foreach ($items as $it) {
                $msg .= "• " . mb_substr($it['name'],0,35,'UTF-8')
                      . " x{$it['quantity']} — " . number_format((float)$it['unit_price'],0,',','.') . "đ\n";
            }
            if ($ord['status'] === 'pending') {
                $msg .= "\n💡 <code>xác nhận {$id}</code> để xác nhận đơn này";
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdConfirmOrder($chatId, $id) {
        try {
            $db  = Database::getInstance();
            $ord = $db->fetch("SELECT id, status FROM orders WHERE id=?", array($id));
            if (!$ord) return self::reply($chatId, "❌ Không tìm thấy đơn #{$id}");
            if ($ord['status'] !== 'pending') {
                return self::reply($chatId, "⚠️ Đơn #{$id} đang ở trạng thái <b>{$ord['status']}</b>, không thể xác nhận.");
            }
            $db->query("UPDATE orders SET status='confirmed', updated_at=NOW() WHERE id=?", array($id));
            $msg = "✅ Đã xác nhận đơn hàng <b>#{$id}</b> thành công!";
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdCancelOrder($chatId, $id) {
        try {
            $db  = Database::getInstance();
            $ord = $db->fetch("SELECT id, status FROM orders WHERE id=?", array($id));
            if (!$ord) return self::reply($chatId, "❌ Không tìm thấy đơn #{$id}");
            if (in_array($ord['status'], array('delivered','cancelled'))) {
                return self::reply($chatId, "⚠️ Đơn #{$id} đã <b>{$ord['status']}</b>, không thể hủy.");
            }
            $db->query("UPDATE orders SET status='cancelled', updated_at=NOW() WHERE id=?", array($id));
            $msg = "❌ Đã hủy đơn hàng <b>#{$id}</b>.";
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdStock($chatId) {
        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT p.name, i.stock_quantity, i.min_stock
                 FROM inventory i JOIN products p ON i.product_id=p.id AND p.is_deleted=0
                 WHERE i.stock_quantity<=i.min_stock ORDER BY i.stock_quantity LIMIT 10"
            );
            if (!$rows) return self::reply($chatId, "✅ Kho hàng đầy đủ, không có sản phẩm nào sắp hết!");

            $msg = "📦 <b>Hàng sắp hết kho:</b>\n\n";
            foreach ($rows as $r) {
                $icon = $r['stock_quantity'] <= 0 ? '🔴' : '🟡';
                $msg .= "{$icon} " . mb_substr($r['name'],0,40,'UTF-8')
                      . " — còn <b>{$r['stock_quantity']}</b> (tối thiểu: {$r['min_stock']})\n";
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdProducts($chatId) {
        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT p.name, p.price, p.is_active, i.stock_quantity
                 FROM products p LEFT JOIN inventory i ON p.id=i.product_id
                 WHERE p.is_deleted=0 ORDER BY p.created_at DESC LIMIT 8"
            );
            $msg = "🏷️ <b>Sản phẩm mới nhất:</b>\n\n";
            foreach ($rows as $r) {
                $active = $r['is_active'] ? '✅' : '⛔';
                $msg .= "{$active} " . mb_substr($r['name'],0,35,'UTF-8')
                      . " — " . number_format((float)$r['price'],0,',','.') . "đ"
                      . " (kho: " . (int)$r['stock_quantity'] . ")\n";
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdCustomers($chatId) {
        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT fullname, email, phone, created_at FROM users WHERE role=0 ORDER BY created_at DESC LIMIT 5"
            );
            $total = $db->fetch("SELECT COUNT(*) AS t FROM users WHERE role=0");
            $today = $db->fetch("SELECT COUNT(*) AS t FROM users WHERE role=0 AND DATE(created_at)=CURDATE()");
            $msg   = "👤 <b>Khách hàng</b> (tổng: {$total['t']} | hôm nay: +{$today['t']}):\n\n";
            foreach ($rows as $r) {
                $msg .= "• " . htmlspecialchars($r['fullname'])
                      . " | " . date('d/m', strtotime($r['created_at'])) . "\n";
            }
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdLock($chatId, $query, $lock) {
        if (!$query) {
            $action = $lock ? 'khóa' : 'mở khóa';
            return self::reply($chatId, "⚠️ Vui lòng nhập tên hoặc email cần {$action}.\nVD: <code>" . ($lock?'khóa':'mở khóa') . " user@email.com</code>");
        }
        try {
            $db  = Database::getInstance();
            $u   = $db->fetch(
                "SELECT id, fullname, email, is_active, role FROM users WHERE (email LIKE ? OR fullname LIKE ?) AND role<4 LIMIT 1",
                array("%{$query}%", "%{$query}%")
            );
            if (!$u) return self::reply($chatId, "❌ Không tìm thấy tài khoản: <b>{$query}</b>");
            if ($u['role'] == 1) return self::reply($chatId, "⛔ Không thể khóa tài khoản Admin.");

            $newState = $lock ? 0 : 1;
            if ((int)$u['is_active'] === (int)(!$lock)) {
                $state = $lock ? 'đã khóa rồi' : 'đã mở rồi';
                return self::reply($chatId, "ℹ️ Tài khoản <b>{$u['fullname']}</b> {$state}.");
            }

            $db->query("UPDATE users SET is_active=? WHERE id=?", array($newState, $u['id']));
            $icon = $lock ? '🔒' : '🔓';
            $action = $lock ? 'Đã khóa' : 'Đã mở khóa';
            $msg  = "{$icon} <b>{$action}</b> tài khoản:\n"
                  . "👤 {$u['fullname']} ({$u['email']})";
        } catch (Exception $e) {
            $msg = '❌ Lỗi: ' . $e->getMessage();
        }
        return self::reply($chatId, $msg);
    }

    private static function cmdAI($chatId, $text) {
        try {
            require_once __DIR__ . '/AIInsight.php';
            $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
            if (!$apiKey) return self::reply($chatId, '❌ AI chưa cấu hình API key.');

            $site  = defined('APP_NAME') ? APP_NAME : 'shop';
            $prompt = "Bạn là trợ lý AI của hệ thống quản trị {$site}. "
                    . "Trả lời ngắn gọn bằng tiếng Việt, tối đa 300 từ. "
                    . "Câu hỏi: {$text}";

            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(array(
                    'model'    => 'llama-3.3-70b-versatile',
                    'messages' => array(
                        array('role' => 'system', 'content' => 'Bạn là trợ lý admin thương mại điện tử. Trả lời ngắn gọn, súc tích bằng tiếng Việt.'),
                        array('role' => 'user',   'content' => $text),
                    ),
                    'max_tokens' => 400,
                    'temperature' => 0.6,
                )),
                CURLOPT_HTTPHEADER     => array(
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $res  = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($res, true);
            $reply = $data['choices'][0]['message']['content'] ?? '';
            if (!$reply) return self::reply($chatId, '🤔 AI không trả lời được. Thử lại sau.');

            return self::reply($chatId, '🤖 ' . trim($reply));
        } catch (Exception $e) {
            return self::reply($chatId, '❌ Lỗi AI: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────
    private static function reply($chatId, $text) {
        require_once __DIR__ . '/TelegramNotifier.php';
        TelegramNotifier::send($chatId, $text);
        return $text;
    }
}
