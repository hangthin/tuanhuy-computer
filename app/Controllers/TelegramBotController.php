<?php
/**
 * TelegramBotController — Fully autonomous AI Admin via Telegram
 * Uses Groq AI to understand Vietnamese free-form messages and execute actions.
 *
 * Entry points:
 *   tick()             — polling mode (called from bot_tick.php)
 *   webhook()          — webhook mode (called from /telegram/webhook route)
 *   processMessageWeb($text) — web panel mode (called from AdminController)
 */
require_once __DIR__ . '/../Models/Models.php';
require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Helpers/TelegramNotifier.php';

class TelegramBotController {

    private $db;
    private $token;
    private $adminChatId;
    private $pendingFile;

    // Web mode: collect output instead of sending to Telegram
    private $webMode   = false;
    private $webOutput = array();

    private $tgDebugLog;

    public function __construct() {
        $this->db          = Database::getInstance();
        $this->token       = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
        $this->adminChatId = defined('TELEGRAM_ADMIN_CHAT') ? (string)TELEGRAM_ADMIN_CHAT : '';
        $storageDir        = __DIR__ . '/../../storage';
        if (!is_dir($storageDir)) @mkdir($storageDir, 0775, true);
        $this->pendingFile  = $storageDir . '/ai_pending.json';
        $this->tgDebugLog   = $storageDir . '/telegram_debug.log';
    }

    // ── Polling tick (bot_tick.php) ───────────────────────────────────
    public function tick() {
        if (!$this->token) {
            $this->debugLog('TICK SKIP: no token');
            return;
        }
        $offset  = $this->getOffset();
        $updates = $this->fetchUpdates($offset);
        $this->debugLog('TICK offset=' . $offset . ' updates=' . count($updates));
        foreach ($updates as $up) {
            $this->processUpdate($up);
            $this->saveOffset((int)$up['update_id'] + 1);
        }
    }

    // ── Webhook entry — EC2 24/7 handler ─────────────────────────────
    public function handleWebhook() {
        // Always return 200 immediately so Telegram doesn't retry
        http_response_code(200);
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $this->webhookLog('IN ' . mb_substr($body ?: 'EMPTY', 0, 400, 'UTF-8'));

        $data = $body ? json_decode($body, true) : null;
        if (!$data) {
            $this->webhookLog('SKIP json_decode failed');
            echo '{"ok":true}';
            return;
        }

        $msg    = $data['message'] ?? $data['edited_message'] ?? null;
        $chatId = (string)($msg['chat']['id'] ?? '');
        $text   = trim($msg['text'] ?? '');

        // Security: only admin chat
        if (!$chatId || ($this->adminChatId && $chatId !== $this->adminChatId)) {
            $this->webhookLog('BLOCKED chatId=' . $chatId);
            if ($chatId) {
                $this->sendTelegram($chatId, '⛔ Không có quyền truy cập.');
            }
            echo '{"ok":true}';
            return;
        }

        if (!$text) {
            $this->webhookLog('SKIP non-text message');
            $this->sendTelegram($chatId, '⚠️ Chỉ hỗ trợ tin nhắn văn bản.');
            echo '{"ok":true}';
            return;
        }

        $this->webhookLog('MSG chatId=' . $chatId . ' text=' . mb_substr($text, 0, 100, 'UTF-8'));

        // Process via AI and get reply
        try {
            $result = $this->processMessageWeb($text);
            $reply  = $result['reply'] ?? 'Đã xử lý.';
            $this->sendTelegram($chatId, $reply);
            $this->webhookLog('REPLY ok len=' . mb_strlen($reply, 'UTF-8'));
        } catch (Exception $e) {
            $this->webhookLog('EXCEPTION ' . $e->getMessage());
            $this->sendTelegram($chatId, '⚠️ Lỗi xử lý: ' . $e->getMessage());
        }

        echo '{"ok":true}';
    }

    // ── Send message via Telegram API ─────────────────────────────────
    private function sendTelegram($chatId, $text) {
        if (!$this->token || !$chatId || !$text) return false;
        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/sendMessage');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            )),
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res = curl_exec($ch);
        curl_close($ch);
        return $res && !empty(json_decode($res, true)['ok']);
    }

    // ── Webhook-specific logger ───────────────────────────────────────
    private function webhookLog($msg) {
        $logFile = __DIR__ . '/../../storage/webhook_debug.log';
        @file_put_contents($logFile,
            '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    // ── Legacy webhook entry (kept for compatibility) ─────────────────
    public function webhook() {
        $body   = file_get_contents('php://input');
        $this->debugLog('WEBHOOK raw=' . mb_substr($body ?: 'EMPTY', 0, 300, 'UTF-8'));
        $update = json_decode($body, true);
        if (!$update) {
            $this->debugLog('WEBHOOK json_decode failed');
            return;
        }
        $this->processUpdate($update);
    }

    // ── Tự detect URL thật của server (kể cả khi APP_URL = localhost) ─
    private function detectBaseUrl() {
        $appUrl = defined('APP_URL') ? APP_URL : '';
        // Nếu APP_URL đã là HTTPS và không phải localhost thì dùng luôn
        if ($appUrl && strpos($appUrl, 'localhost') === false && strpos($appUrl, '127.0.0.1') === false) {
            return rtrim($appUrl, '/');
        }
        // Fallback: build từ HTTP headers (hoạt động trên Render/Oracle/VPS)
        $proto = 'https';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $proto = 'https';
        }
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        if (!$host) return $appUrl; // không detect được, trả về APP_URL gốc
        return $proto . '://' . $host;
    }

    // ── Đăng ký webhook với Telegram (GET /telegram/setup) ───────────
    public function setup() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->token) {
            echo json_encode(array('ok' => false, 'error' => 'TELEGRAM_BOT_TOKEN chưa cấu hình'));
            return;
        }

        $webhookUrl = $this->detectBaseUrl() . '/telegram/webhook';

        // Gọi setWebhook
        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/setWebhook');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'url'             => $webhookUrl,
                'allowed_updates' => array('message', 'edited_message'),
                'drop_pending_updates' => true,
            )),
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $data = $res ? json_decode($res, true) : null;
        $ok   = !empty($data['ok']);

        $this->debugLog("SETUP webhook url={$webhookUrl} ok=" . ($ok ? '1' : '0'));

        if ($ok) {
            TelegramNotifier::send($this->adminChatId, "✅ Webhook đã đăng ký!\n🔗 {$webhookUrl}\n🕐 " . date('H:i:s d/m/Y'));
        }

        echo json_encode(array(
            'ok'          => $ok,
            'webhook_url' => $webhookUrl,
            'telegram'    => $data,
            'curl_error'  => $err ?: null,
        ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ── Tự đăng ký webhook nếu chưa có (gọi từ cron) ────────────────
    public function registerWebhookIfNeeded() {
        if (!$this->token) return array('skipped' => 'no_token');

        // Kiểm tra webhook hiện tại
        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/getWebhookInfo');
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_SSL_VERIFYPEER => false));
        $res = curl_exec($ch);
        curl_close($ch);

        $info       = $res ? json_decode($res, true) : null;
        $currentUrl = $info['result']['url'] ?? '';
        $lastError  = $info['result']['last_error_message'] ?? '';
        $baseUrl    = $this->detectBaseUrl();
        $targetUrl  = $baseUrl . '/telegram/webhook';

        // Đã đăng ký đúng URL và không có lỗi → không cần làm gì
        if ($currentUrl === $targetUrl && !$lastError) {
            return array('status' => 'already_active', 'url' => $currentUrl);
        }

        // Chưa có webhook hoặc URL sai hoặc có lỗi → đăng ký lại
        $this->debugLog("registerWebhookIfNeeded: current=" . ($currentUrl ?: 'none') . " target={$targetUrl} lastError={$lastError}");

        $ch2 = curl_init('https://api.telegram.org/bot' . $this->token . '/setWebhook');
        curl_setopt_array($ch2, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'url'                  => $targetUrl,
                'allowed_updates'      => array('message', 'edited_message'),
                'drop_pending_updates' => false,
            )),
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res2 = curl_exec($ch2);
        curl_close($ch2);

        $result = $res2 ? json_decode($res2, true) : null;
        $ok     = !empty($result['ok']);
        $this->debugLog("setWebhook ok=" . ($ok ? '1' : '0') . " url={$targetUrl}");

        if ($ok) {
            TelegramNotifier::sendToAdmin("✅ Webhook tự đăng ký thành công!\n🔗 {$targetUrl}\n🕐 " . date('H:i:s d/m/Y'));
        }

        return array('status' => $ok ? 'registered' : 'failed', 'url' => $targetUrl, 'telegram' => $result);
    }

    // ── Kiểm tra trạng thái webhook (GET /telegram/verify) ───────────
    public function verify() {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->token) {
            echo json_encode(array('ok' => false, 'error' => 'TELEGRAM_BOT_TOKEN not configured'));
            return;
        }

        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/getWebhookInfo');
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_SSL_VERIFYPEER => false));
        $res = curl_exec($ch);
        curl_close($ch);

        $info        = $res ? json_decode($res, true) : null;
        $webhookUrl  = $info['result']['url'] ?? '';
        $isActive    = !empty($webhookUrl);
        $lastError   = $info['result']['last_error_message'] ?? '';

        $result = array(
            'webhook_active'   => $isActive,
            'webhook_url'      => $webhookUrl ?: '(chưa đăng ký)',
            'pending_updates'  => $info['result']['pending_update_count'] ?? 0,
            'last_error'       => $lastError ?: 'none',
            'setup_url'        => rtrim(defined('APP_URL') ? APP_URL : '', '/') . '/telegram/setup',
            'admin_chat_id'    => $this->adminChatId ?: '(not set)',
        );

        if (!$isActive) {
            $result['hint'] = 'Gọi /telegram/setup để đăng ký webhook tự động';
        }

        if ($this->adminChatId) {
            $status = $isActive ? '✅ Webhook active' : '⚠️ Webhook chưa đăng ký';
            TelegramNotifier::send($this->adminChatId, "{$status}\n🔗 " . ($webhookUrl ?: 'none') . "\n🕐 " . date('H:i:s d/m/Y'));
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ── Web panel entry (AdminController) ────────────────────────────
    public function processMessageWeb($text) {
        $this->webMode   = true;
        $this->webOutput = array();

        $text = trim($text);
        if (!$text) return array('ok' => false, 'reply' => '');

        // Handle confirmation / cancellation BEFORE calling AI
        $low = mb_strtolower($text, 'UTF-8');
        if (in_array($low, array('xác nhận','xac nhan','ok','yes','đồng ý','dong y','xacnhan'))) {
            if (file_exists($this->pendingFile)) {
                $pending = json_decode(file_get_contents($this->pendingFile), true);
                $this->clearPending();
                if ($pending) {
                    $pending['requires_confirm'] = false;
                    $this->executeAction($pending, null);
                    return array(
                        'ok'    => true,
                        'action'=> $pending['action'] ?? 'confirmed',
                        'reply' => implode("\n\n", array_filter($this->webOutput)) ?: 'Đã thực hiện thành công.',
                        'steps' => $pending['steps'] ?? array(),
                    );
                }
            }
            return array('ok' => true, 'action' => 'chat', 'reply' => 'Không có thao tác nào đang chờ xác nhận.', 'steps' => array());
        }
        if (in_array($low, array('hủy','huy','cancel','no','không','khong'))) {
            $this->clearPending();
            return array('ok' => true, 'action' => 'chat', 'reply' => 'Đã hủy thao tác.', 'steps' => array());
        }

        // Pre-classify unambiguous commands BEFORE calling AI (avoids AI hallucinating chat replies)
        $forced = $this->preClassify($text);
        if ($forced) {
            $this->debugLog("processMessageWeb preClassify action=" . $forced['action']);
            $this->executeAction($forced, null);
            $reply = implode("\n\n", array_filter($this->webOutput));
            // Strip HTML tags for web output (web panel renders its own UI)
            $reply = strip_tags($reply);
            if (!$reply) $reply = $forced['reply_vn'] ?? 'Hoàn thành.';
            return array('ok' => true, 'action' => $forced['action'], 'reply' => $reply, 'steps' => $forced['steps'] ?? array());
        }

        $context = $this->buildContext();
        $aiJson  = $this->callAI($text, $context);
        $this->debugLog("processMessageWeb AI action=" . ($aiJson['action'] ?? 'null'));

        if (!$aiJson) {
            return array('ok' => false, 'reply' => 'AI không phản hồi. Kiểm tra lại AI_API_KEY.');
        }

        if (!empty($aiJson['requires_confirm'])) {
            $this->storePending($aiJson);
            return array(
                'ok'              => true,
                'action'          => 'confirm_request',
                'reply'           => $aiJson['reply_vn'] ?? 'Thao tác cần xác nhận.',
                'steps'           => $aiJson['steps'] ?? array(),
                'requires_confirm'=> true,
            );
        }

        $this->executeAction($aiJson, null);

        // Separate thinking markers from regular output
        $thinkingData = null;
        $chatLines    = array();
        foreach ($this->webOutput as $line) {
            if (strncmp($line, '__THINKING__', 12) === 0) {
                $thinkingData = json_decode(substr($line, 12), true);
            } else {
                $chatLines[] = $line;
            }
        }

        $reply = implode("\n\n", array_filter($chatLines));
        if (!$reply) $reply = $aiJson['reply_vn'] ?? 'Hoàn thành.';

        return array(
            'ok'      => true,
            'action'  => $aiJson['action'] ?? 'chat',
            'reply'   => $reply,
            'steps'   => $aiJson['steps'] ?? array(),
            'thinking'=> $thinkingData['thinking'] ?? ($aiJson['thinking'] ?? ''),
            'plan'    => $thinkingData['plan']     ?? ($aiJson['plan']     ?? ''),
        );
    }

    // ── Process a single Telegram update ─────────────────────────────
    private function processUpdate($update) {
        $msg    = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$msg) return;
        $chatId = (string)($msg['chat']['id'] ?? '');
        $text   = trim($msg['text'] ?? '');

        $this->debugLog("IN update_id=" . ($update['update_id'] ?? '?') . " chatId={$chatId} text=" . mb_substr($text, 0, 120, 'UTF-8'));

        if ($this->adminChatId && $chatId !== $this->adminChatId) {
            $this->debugLog("BLOCKED chatId={$chatId} != adminChatId={$this->adminChatId}");
            $this->sendMsg($chatId, '⛔ Bạn không có quyền dùng bot này.');
            return;
        }
        if (!$text) {
            $this->debugLog("SKIP empty text (sticker/photo/etc)");
            $this->sendMsg($chatId, '⚠️ Bot chỉ xử lý tin nhắn văn bản. Vui lòng gõ lệnh.');
            return;
        }

        $replySent = false;
        try {
            $low = mb_strtolower($text, 'UTF-8');

            // Handle confirmation / cancellation
            if (in_array($low, array('xác nhận','xac nhan','ok','yes','đồng ý','dong y'))) {
                $this->executePendingTelegram($chatId);
                $replySent = true;
                return;
            }
            if (in_array($low, array('hủy','huy','cancel','không','khong','no'))) {
                $this->clearPending();
                $this->sendMsg($chatId, '✖ Đã hủy thao tác.');
                $replySent = true;
                return;
            }

            // Pre-classify before calling AI
            $forced = $this->preClassify($text);
            if ($forced) {
                $this->debugLog("preClassify matched action=" . $forced['action']);
                $this->executeAction($forced, $chatId);
                $replySent = true;
                return;
            }

            $this->sendMsg($chatId, '⏳ Đang phân tích...');
            $context = $this->buildContext();
            $aiJson  = $this->callAI($text, $context);
            $this->debugLog("AI action=" . ($aiJson['action'] ?? 'null'));

            if (!$aiJson) {
                $this->sendMsg($chatId, '⚠️ AI không phản hồi. Thử lại sau.');
                $replySent = true;
                return;
            }

            if (!empty($aiJson['requires_confirm'])) {
                $this->storePending($aiJson);
                $m  = "⚠️ <b>Xác nhận thao tác</b>\n\n";
                $m .= htmlspecialchars($aiJson['reply_vn'] ?? 'Thao tác này cần xác nhận.');
                $m .= "\n\nGửi <b>xác nhận</b> để tiếp tục hoặc <b>hủy</b> để bỏ qua.";
                $this->sendMsg($chatId, $m);
                $replySent = true;
                return;
            }

            $this->executeAction($aiJson, $chatId);
            $replySent = true;

        } catch (Exception $e) {
            $this->debugLog("EXCEPTION: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            error_log('[TelegramBot] ' . $e->getMessage());
            $this->sendMsg($chatId, '⚠️ Đã xảy ra lỗi xử lý. Vui lòng thử lại.');
            $replySent = true;
        }

        // Fallback: should never reach here without replying
        if (!$replySent) {
            $this->debugLog("FALLBACK no reply sent for: " . mb_substr($text, 0, 80, 'UTF-8'));
            $this->sendMsg($chatId, '⏳ Đang xử lý yêu cầu của bạn...');
        }
    }

    // ── Build DB context for the AI system prompt ─────────────────────
    private function buildContext() {
        try {
            $db       = $this->db;
            $totalP   = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND is_deleted=0")->fetchColumn();
            $totalO   = (int)$db->query("SELECT COUNT(*) FROM orders WHERE is_deleted=0")->fetchColumn();
            $pendingO = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending' AND is_deleted=0")->fetchColumn();
            $todayRev = (float)($db->fetch("SELECT COALESCE(SUM(total),0) AS r FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled' AND is_deleted=0") ?? array())['r'];
            $recentP  = $db->fetchAll("SELECT id,name,price FROM products WHERE is_active=1 AND is_deleted=0 ORDER BY id DESC LIMIT 8");
            $lowStock = $db->fetchAll("SELECT p.name, COALESCE(i.stock_quantity, p.stock) AS stock FROM products p LEFT JOIN inventory i ON p.id=i.product_id WHERE p.is_deleted=0 AND COALESCE(i.stock_quantity, p.stock)<=5 LIMIT 5");

            $ctx  = "Site: " . (defined('APP_NAME') ? APP_NAME : 'Tuấn Huy Computer') . "\n";
            $ctx .= "SP: {$totalP} | Đơn: {$totalO} | Chờ: {$pendingO} | DT hôm nay: " . number_format($todayRev,0,',','.') . "đ\n";
            if ($recentP) {
                $ctx .= "SP gần đây: ";
                foreach ($recentP as $p) $ctx .= "[#{$p['id']} {$p['name']} " . number_format((float)$p['price'],0,',','.') . "đ] ";
                $ctx .= "\n";
            }
            if ($lowStock) {
                $ctx .= "Hàng sắp hết: ";
                foreach ($lowStock as $s) $ctx .= "[{$s['name']} còn {$s['stock']}] ";
                $ctx .= "\n";
            }
            return $ctx;
        } catch (Exception $e) {
            return "Site: " . (defined('APP_NAME') ? APP_NAME : 'Tuấn Huy Computer');
        }
    }

    // ── Call AI (Groq llama-3.3-70b) ─────────────────────────────────
    private function callAI($text, $context = '') {
        $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
        if (!$apiKey) return null;

        $schema = "DB SCHEMA (MySQL):\n"
            . "products(id,name,slug,short_desc,description,specs JSON,image,price,stock,category_id,brand_id,is_active TINYINT,is_deleted TINYINT,created_at)\n"
            . "product_images(id,product_id,image,sort_order)\n"
            . "orders(id,order_code,user_id,fullname,phone,email,total,status,is_deleted,created_at) status IN(pending,confirmed,processing,shipping,delivered,cancelled)\n"
            . "order_details(id,order_id,product_id,product_name,quantity,price)\n"
            . "users(id,fullname,email,phone,role INT,is_active,created_at) role:0=customer,1=admin,2=manager,3=staff\n"
            . "inventory(id,product_id,stock_quantity,min_stock)\n"
            . "categories(id,name,slug,parent_id) | brands(id,name)\n\n"
            . "AVAILABLE EXECUTORS:\n"
            . "report(period:today|week|month) — doanh thu, đơn hàng, khách mới\n"
            . "find_images(id,query) — tìm + tải ảnh đúng cho 1 SP (AI vision xác nhận trước khi lưu)\n"
            . "verify_images() — kiểm tra TẤT CẢ SP có ảnh bằng AI vision, tự tìm + đổi ảnh sai\n"
            . "  → dùng khi: 'có hình sai','hình không đúng','kiểm tra hình','quét hình','rà ảnh','hình lộn','ảnh nhầm'\n"
            . "remove_bg(id) — xóa nền ảnh SP (yêu cầu xác nhận)\n"
            . "fill_specs(id) — AI viết description+specs cho 1 SP\n"
            . "create_product(name,price,stock,category_id,description)\n"
            . "update_product(id,fields:{name,price,stock,description,is_active,specs})\n"
            . "delete_product(id) — xóa mềm (yêu cầu xác nhận)\n"
            . "search(q,type:product|order|customer)\n"
            . "full_auto(limit,filter) — xử lý hàng loạt SP theo filter\n"
            . "bulk(filter,action) — bulk action\n"
            . "chat — trả lời câu hỏi từ context DB\n\n"
            . "FILTER OBJECT (dùng cho full_auto và bulk):\n"
            . "{\"need_image\":true, \"need_specs\":true, \"active_only\":true, \"limit\":20, \"category_id\":null}\n"
            . "need_image=true → chỉ xử lý SP chưa có ảnh (file không tồn tại)\n"
            . "need_specs=true → chỉ xử lý SP thiếu specs (<3 keys)\n"
            . "null = không quan tâm điều kiện đó\n\n"
            . "NGUYÊN TẮC CHỌN EXECUTOR:\n"
            . "- Người dùng nói về ảnh bị sai/nhầm/không đúng/lộn → verify_images (không phải find_images)\n"
            . "- Người dùng muốn tìm ảnh cho SP cụ thể → find_images(id=X)\n"
            . "- Người dùng muốn kiểm tra/quét toàn bộ ảnh → verify_images\n\n"
            . "STATS HIỆN TẠI:\n{$context}";

        $_appName = defined('APP_NAME') ? APP_NAME : 'Tuấn Huy Computer';
        $sys = "Bạn là AI admin tự chủ của {$_appName}. Tư duy độc lập, hiểu ngữ cảnh tiếng Việt (có/không dấu).\n\n"
             . $schema . "\n\n"
             . "Trả về JSON (không markdown, không giải thích ngoài JSON):\n"
             . "{\n"
             . "  \"thinking\": \"Phân tích: người dùng muốn gì, dữ liệu nào cần thiết\",\n"
             . "  \"plan\": \"Kế hoạch ngắn gọn các bước thực hiện\",\n"
             . "  \"action\": \"tên executor\",\n"
             . "  \"params\": {},\n"
             . "  \"filter\": {\"need_image\":null,\"need_specs\":null,\"active_only\":true,\"limit\":20},\n"
             . "  \"reply_vn\": \"Câu trả lời hoặc thông báo bắt đầu\",\n"
             . "  \"steps\": [\"bước 1\",\"bước 2\"],\n"
             . "  \"requires_confirm\": false\n"
             . "}\n\n"
             . "QUAN TRỌNG: PHẢI chọn đúng executor và điền filter phù hợp. KHÔNG dùng chat khi người dùng ra lệnh hành động.";

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'model'           => 'llama-3.3-70b-versatile',
                'messages'        => array(
                    array('role' => 'system', 'content' => $sys),
                    array('role' => 'user',   'content' => $text),
                ),
                'max_tokens'      => 1200,
                'temperature'     => 0.2,
                'response_format' => array('type' => 'json_object'),
            )),
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        // Log raw response for diagnosis
        $debugLog = __DIR__ . '/../../storage/ai_debug.log';
        @file_put_contents($debugLog,
            '[' . date('Y-m-d H:i:s') . '] ' . ($curlErr ? 'CURL_ERR='.$curlErr : 'OK') .
            ' raw=' . substr($res ?: '', 0, 800) . "\n",
            FILE_APPEND | LOCK_EX);

        $data    = $res ? json_decode($res, true) : null;
        $content = $data['choices'][0]['message']['content'] ?? '';

        // Retry once on empty response
        if (!$content) {
            sleep(1);
            $ch2 = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch2, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(array(
                    'model'           => 'llama-3.3-70b-versatile',
                    'messages'        => array(
                        array('role' => 'system', 'content' => $sys),
                        array('role' => 'user',   'content' => $text),
                    ),
                    'max_tokens'      => 1200,
                    'temperature'     => 0.2,
                    'response_format' => array('type' => 'json_object'),
                )),
                CURLOPT_HTTPHEADER     => array(
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $res2 = curl_exec($ch2);
            curl_close($ch2);
            @file_put_contents($debugLog, '[' . date('Y-m-d H:i:s') . '] RETRY raw=' . substr($res2 ?: '', 0, 800) . "\n", FILE_APPEND | LOCK_EX);
            $data2   = $res2 ? json_decode($res2, true) : null;
            $content = $data2['choices'][0]['message']['content'] ?? '';
            if (!$content) return null;
        }

        $parsed = json_decode($content, true);
        if (!$parsed || empty($parsed['action'])) {
            if (preg_match('/\{.+\}/s', $content, $m)) $parsed = json_decode($m[0], true);
        }
        if (!$parsed) {
            // JSON bị cắt hoặc không hợp lệ — trả về chat an toàn
            $safe = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/u', '', $content);
            return array('action' => 'chat', 'reply_vn' => $safe ?: 'AI phản hồi không hợp lệ. Thử lại.', 'requires_confirm' => false);
        }
        // Sanitize string fields
        array_walk_recursive($parsed, function(&$v) {
            if (is_string($v)) $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
        });
        return $parsed;
    }

    // ── Dispatch action ───────────────────────────────────────────────
    private function executeAction($aiResp, $chatId) {
        $type   = $aiResp['action']   ?? 'chat';
        $params = $aiResp['params']   ?? array();
        $reply  = $aiResp['reply_vn'] ?? '';

        // Merge AI filter into params so executors can read it
        if (!empty($aiResp['filter']) && is_array($aiResp['filter'])) {
            $params['_filter'] = $aiResp['filter'];
            // Honour limit from filter if params has none
            if (!isset($params['limit']) && isset($aiResp['filter']['limit'])) {
                $params['limit'] = $aiResp['filter']['limit'];
            }
        }

        // Log AI reasoning to output (terminal in web panel, message in Telegram)
        if (!empty($aiResp['thinking'])) {
            $this->outThinking($chatId, $aiResp['thinking'], $aiResp['plan'] ?? '');
        }

        switch ($type) {
            case 'ai_insight':      $this->execAiInsight($params, $chatId, $reply);      break;
            case 'report':          $this->execReport($params, $chatId, $reply);         break;
            case 'find_images':     $this->execFindImages($params, $chatId, $reply);     break;
            case 'remove_bg':       $this->execRemoveBg($params, $chatId, $reply);       break;
            case 'fill_specs':      $this->execFillSpecs($params, $chatId, $reply);      break;
            case 'create_product':  $this->execCreateProduct($params, $chatId, $reply);  break;
            case 'update_product':  $this->execUpdateProduct($params, $chatId, $reply);  break;
            case 'delete_product':  $this->execDeleteProduct($params, $chatId, $reply);  break;
            case 'search':          $this->execSearch($params, $chatId, $reply);         break;
            case 'full_auto':       $this->execFullAuto($params, $chatId, $reply);       break;
            case 'bulk':            $this->execBulk($params, $chatId, $reply);           break;
            case 'verify_images':   $this->execVerifyImages($params, $chatId, $reply);   break;
            default:                $this->out($chatId, $reply ?: 'Hoàn thành.');        break;
        }
    }

    // ── Output: AI thinking (goes to terminal log, not chat) ──────────
    private function outThinking($chatId, $thinking, $plan) {
        if ($this->webMode) {
            // Special marker so JS can route to terminal instead of chat
            $this->webOutput[] = '__THINKING__' . json_encode(array('thinking' => $thinking, 'plan' => $plan));
        } elseif ($chatId) {
            // In Telegram: just send as a short italic note
            TelegramNotifier::send($chatId, "💭 " . mb_substr($thinking, 0, 200, 'UTF-8'));
        }
    }

    // ── EXECUTOR: report ──────────────────────────────────────────────
    private function execReport($params, $chatId, $aiReply) {
        try {
            $period = $params['period'] ?? 'today';
            $db     = $this->db;

            if ($period === 'week') {
                $where = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                $label = "7 ngày qua";
            } elseif ($period === 'month') {
                $where = "YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())";
                $label = "Tháng này";
            } else {
                $where = "DATE(created_at)=CURDATE()";
                $label = "Hôm nay";
            }

            $rev  = $db->fetch("SELECT COALESCE(SUM(total),0) AS r, COUNT(*) AS c FROM orders WHERE {$where} AND status!='cancelled' AND is_deleted=0");
            $pend = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending' AND is_deleted=0")->fetchColumn();
            $newU = $db->fetch("SELECT COUNT(*) AS c FROM users WHERE role=0 AND {$where}");
            $topP = $db->fetchAll("SELECT p.name, SUM(od.quantity) AS sold FROM order_details od JOIN products p ON p.id=od.product_id JOIN orders o ON o.id=od.order_id WHERE {$where} AND o.is_deleted=0 GROUP BY p.id ORDER BY sold DESC LIMIT 3");

            $esc = function($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

            $msg  = "📊 <b>Báo cáo — {$label}</b>\n\n";
            $msg .= "💰 Doanh thu: <b>" . number_format((float)$rev['r'], 0, ',', '.') . "đ</b>\n";
            $msg .= "📦 Đơn hàng: <b>{$rev['c']}</b> (chờ xử lý: {$pend})\n";
            $msg .= "👤 Khách mới: <b>{$newU['c']}</b>\n";
            if ($topP) {
                $msg .= "\n🏆 Top sản phẩm:\n";
                foreach ($topP as $i => $t) {
                    $msg .= ($i + 1) . ". " . $esc($t['name']) . " x{$t['sold']}\n";
                }
            }
            $msg .= "\n🕐 " . date('H:i d/m/Y');
            $this->debugLog("execReport period={$period} rev={$rev['r']} orders={$rev['c']}");
            $this->out($chatId, $msg);
        } catch (Exception $e) {
            $this->debugLog("execReport EXCEPTION: " . $e->getMessage());
            $this->out($chatId, '❌ Lỗi báo cáo: ' . $e->getMessage());
        }
    }

    // ── EXECUTOR: ai_insight — full AIInsight report ──────────────────
    private function execAiInsight($params, $chatId, $aiReply) {
        require_once __DIR__ . '/../Helpers/AIInsight.php';
        $hours = max(1, min(168, (int)($params['hours'] ?? 24)));
        $this->out($chatId, "⏳ Đang phân tích {$hours}h qua, vui lòng chờ...");
        try {
            $data = AIInsight::generateAndCache($hours);
            $text = trim($data['text'] ?? '');
            if (!$text) {
                $this->out($chatId, '❌ Không tạo được báo cáo. Kiểm tra AI_API_KEY.');
                return;
            }
            $header = "🧠 <b>Báo cáo AI</b> — {$hours}h qua — " . date('H:i d/m/Y');
            $this->out($chatId, $header . "\n\n" . $text);
        } catch (Exception $e) {
            $this->out($chatId, '❌ Lỗi: ' . $e->getMessage());
        }
    }

    // ── EXECUTOR: find_images ─────────────────────────────────────────
    private function execFindImages($params, $chatId, $aiReply) {
        $storageDir = __DIR__ . '/../../storage';
        $dbg = (is_dir($storageDir) && is_writable($storageDir))
             ? $storageDir . '/ai_img_debug.txt'
             : sys_get_temp_dir() . '/ai_img_debug.txt';
        $log = function($msg) use ($dbg) {
            error_log('[find_images] ' . $msg);
            @file_put_contents($dbg, '['.date('H:i:s').'] '.$msg."\n", FILE_APPEND | LOCK_EX);
        };

        set_time_limit(300);

        $id = (int)($params['id'] ?? 0);

        // ── Batch/resume mode: process up to 5 products, save offset ─
        if (!$id) {
            $progressFile = __DIR__ . '/../../storage/auto_progress.json';
            $prog   = file_exists($progressFile) ? (json_decode(file_get_contents($progressFile), true) ?? array()) : array();
            $offset = (int)($prog['offset'] ?? 0);
            $all    = $this->db->fetchAll(
                "SELECT id, name, image FROM products WHERE is_active=1 AND is_deleted=0 ORDER BY id ASC"
            );
            $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';
            $batch = array();
            foreach (array_slice($all, $offset) as $row) {
                $imgVal = trim($row['image'] ?? '');
                if ($imgVal === '' || !file_exists($uploadPath . $imgVal)) $batch[] = $row;
                if (count($batch) >= 5) break;
            }
            if (empty($batch)) {
                @unlink($progressFile);
                $this->out($chatId, "Tất cả SP đã có ảnh (offset {$offset}).");
                return;
            }
            $done = 0;
            foreach ($batch as $row) {
                $this->execFindImages(array('id' => $row['id']), $chatId, '');
                $done++;
            }
            $newOffset = $offset + count($all); // mark done if exhausted, else advance
            $remaining = count($all) - $offset - count($batch);
            if ($remaining > 0) {
                @file_put_contents($progressFile, json_encode(array('offset' => $offset + 5), JSON_UNESCAPED_UNICODE), LOCK_EX);
                $this->out($chatId, "Xong batch {$done} SP. Còn ~{$remaining} SP. Gọi lại để tiếp tục.");
            } else {
                @unlink($progressFile);
                $this->out($chatId, "Hoàn thành! Đã xử lý hết {$done} SP trong batch cuối.");
            }
            return;
        }

        $p = $this->db->fetch("SELECT name FROM products WHERE id=?", array($id));
        if (!$p) { $this->out($chatId, "Không tìm thấy SP #{$id}"); return; }

        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadPath)) @mkdir($uploadPath, 0755, true);

        $googleKey = (defined('GOOGLE_SEARCH_KEY') && GOOGLE_SEARCH_KEY) ? GOOGLE_SEARCH_KEY : '';
        $googleCx  = (defined('GOOGLE_SEARCH_CX')  && GOOGLE_SEARCH_CX)  ? GOOGLE_SEARCH_CX  : '';
        $log("Google key=".($googleKey?'ok':'MISSING')." cx=".($googleCx?'ok':'MISSING'));
        if (!$googleKey || !$googleCx) {
            $this->out($chatId, "Chưa cấu hình GOOGLE_SEARCH_KEY/CX."); return;
        }

        // Strip Vietnamese category prefixes to get clean brand+model for search
        $cleanName = preg_replace(
            '/^(PC\s+Gaming\s+Mini|PC\s+Gaming|PC\s+Văn\s+phòng|PC\s+Workstation|PC\s+Đồ\s+họa|PC\s+Mini|'
          . 'Laptop\s+Gaming|Laptop\s+Văn\s+phòng|Laptop|Màn\s+hình\s+Gaming|Màn\s+hình|'
          . 'Tản\s+nhiệt|Nguồn\s+máy\s+tính|Nguồn|Vỏ\s+Case|Case|Bàn\s+phím\s+Gaming|Bàn\s+phím|'
          . 'Chuột\s+Gaming|Chuột|Tai\s+nghe\s+Gaming|Tai\s+nghe|Card\s+đồ\s+họa|'
          . 'Bo\s+mạch\s+chủ|Mainboard|Ổ\s+cứng\s+SSD|Ổ\s+cứng|GPU|CPU|RAM|SSD|HDD)\s+/ui',
            '', trim($params['query'] ?? '') ?: $p['name']
        );
        // Truncate to 60 chars max (Google CSE works better with shorter queries)
        $cleanName = trim(mb_substr($cleanName, 0, 60, 'UTF-8'));

        // Try 3 progressively simpler queries
        $queries = array(
            $cleanName . ' product photo',
            $cleanName,
            implode(' ', array_slice(explode(' ', $cleanName), 0, 4)), // first 4 words only
        );

        $items = array();
        foreach ($queries as $qi => $q) {
            $q = trim($q);
            if (!$q) continue;
            $log("Query[{$qi}]: \"{$q}\"");
            $this->out($chatId, "Tìm ảnh: \"{$q}\"...");
            $apiUrl = 'https://www.googleapis.com/customsearch/v1?'
                    . 'key=' . urlencode($googleKey)
                    . '&cx=' . urlencode($googleCx)
                    . '&q='  . urlencode($q)
                    . '&searchType=image&num=5&imgType=photo&imgSize=medium';
            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false, CURLOPT_HTTPHEADER => array('Accept: application/json'),
            ));
            $resp    = curl_exec($ch);
            $gCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            $log("HTTP {$gCode}" . ($curlErr ? " ERR:{$curlErr}" : '') . " raw=".substr($resp?:'EMPTY',0,200));
            if (!$curlErr && $gCode === 200) {
                $data = json_decode($resp, true);
                foreach (($data['items'] ?? array()) as $item) {
                    if (!empty($item['link'])) $items[] = $item['link'];
                }
            }
            $log("items=".count($items));
            if (!empty($items)) break; // found results — stop trying
        }

        if (empty($items)) {
            $log("All queries returned 0 results");
            $this->out($chatId, "Không tìm được ảnh Google cho: {$p['name']}");
            return;
        }

        foreach ($items as $i => $imgUrl) {
            $log("Item[{$i}] url={$imgUrl}");
            $ch2 = curl_init($imgUrl);
            curl_setopt_array($ch2, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FOLLOWLOCATION => true,
            ));
            $imgData = curl_exec($ch2);
            $imgCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            $size    = $imgData ? strlen($imgData) : 0;
            curl_close($ch2);
            $log("Item[{$i}] HTTP {$imgCode} size={$size}");
            if (!$imgData || $size < 5000) { $log("skip: too small"); continue; }
            if (!$this->validateImageWithAI($imgData, $p['name'])) {
                $log("Item[{$i}] AI FAILED — next"); continue;
            }
            $fname = 'ai_g_'.$id.'_'.uniqid().'.jpg';
            file_put_contents($uploadPath.$fname, $imgData);
            $this->db->query("UPDATE products SET image=? WHERE id=?", array($fname, $id));
            $log("SAVED {$fname}");
            $this->out($chatId, "Đã lưu ảnh SP #{$id} ({$p['name']}) ✓AI");
            return;
        }

        $log("All items failed validation");
        $this->out($chatId, "Không lưu được ảnh cho: {$p['name']}");
    }

    // ── EXECUTOR: remove_bg ───────────────────────────────────────────
    private function execRemoveBg($params, $chatId, $aiReply) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) { $this->out($chatId, 'Thiếu ID sản phẩm.'); return; }

        $p = $this->db->fetch("SELECT name, image FROM products WHERE id=?", array($id));
        if (!$p || !$p['image']) { $this->out($chatId, "SP #{$id} chưa có ảnh."); return; }

        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';
        if (!file_exists($uploadPath . $p['image'])) { $this->out($chatId, "File ảnh không tồn tại."); return; }

        $this->out($chatId, "Đang xóa nền: {$p['name']}...");

        $result = $this->callApi('/api/ai/remove-bg', array('filename' => $p['image']));
        if (!$result || empty($result['success']) || empty($result['filename'])) {
            $this->out($chatId, "Xóa nền thất bại: " . ($result['message'] ?? 'Lỗi không xác định'));
            return;
        }

        $this->db->query("UPDATE products SET image=? WHERE id=?", array($result['filename'], $id));
        $this->out($chatId, "Đã xóa nền SP #{$id} — {$p['name']} → {$result['filename']}");
    }

    // ── EXECUTOR: fill_specs ──────────────────────────────────────────
    private function execFillSpecs($params, $chatId, $aiReply) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) { $this->out($chatId, 'Thiếu ID sản phẩm.'); return; }

        $p = $this->db->fetch("SELECT id, name FROM products WHERE id=?", array($id));
        if (!$p) { $this->out($chatId, "Không tìm thấy SP #{$id}"); return; }

        $result = $this->callApi('/api/ai/generate-from-name', array('product_name' => $p['name']));
        if (!$result || empty($result['success']) || empty($result['data'])) {
            $this->out($chatId, "AI không sinh được thông tin cho: {$p['name']}. " . ($result['message'] ?? ''));
            return;
        }

        $d = $result['data'];
        $sets = array(); $vals = array();
        if (!empty($d['description']))                          { $sets[] = 'description=?';  $vals[] = $d['description']; }
        if (!empty($d['short_desc']))                           { $sets[] = 'short_desc=?';   $vals[] = $d['short_desc']; }
        if (!empty($d['specs']) && is_array($d['specs']))       { $sets[] = 'specs=?';        $vals[] = json_encode($d['specs'], JSON_UNESCAPED_UNICODE); }
        if (!empty($d['price']) && (float)$d['price'] > 0)     { $sets[] = 'price=?';        $vals[] = (float)$d['price']; }
        if (isset($d['sale_price']) && (float)$d['sale_price'] >= 0) { $sets[] = 'sale_price=?'; $vals[] = (float)$d['sale_price']; }

        if (!$sets) { $this->out($chatId, "Không có dữ liệu để cập nhật cho SP #{$id}"); return; }
        $vals[] = $id;
        $this->db->query("UPDATE products SET " . implode(',', $sets) . " WHERE id=?", $vals);
        $this->out($chatId, "Đã cập nhật " . count($sets) . " trường cho SP #{$id} — {$p['name']}");
    }

    // ── EXECUTOR: create_product ──────────────────────────────────────
    private function execCreateProduct($params, $chatId, $aiReply) {
        $name = trim($params['name'] ?? '');
        if (!$name) { $this->out($chatId, 'Thieu ten san pham.'); return; }

        $slug       = function_exists('makeSlug') ? makeSlug($name) : preg_replace('/[^a-z0-9-]+/', '-', strtolower($name));
        $existing   = $this->db->fetch("SELECT id FROM products WHERE slug=?", array($slug));
        if ($existing) $slug .= '-' . time();

        $price      = max(0, (float)($params['price']       ?? 0));
        $categoryId = (int)($params['category_id'] ?? 0);
        if ($categoryId > 0) {
            $exists = $this->db->fetch("SELECT id FROM categories WHERE id=? AND is_active=1", array($categoryId));
            if (!$exists) $categoryId = 0;
        }
        if ($categoryId <= 0) {
            $first = $this->db->fetch("SELECT id FROM categories WHERE is_active=1 ORDER BY id ASC LIMIT 1");
            $categoryId = $first ? (int)$first['id'] : 1;
        }
        $stock      = max(0, (int)($params['stock']  ?? 0));
        $desc       = trim($params['description'] ?? '');

        $this->db->query(
            "INSERT INTO products (name, slug, description, price, stock, category_id, is_active, is_deleted, created_at) VALUES (?,?,?,?,?,?,1,0,NOW())",
            array($name, $slug, $desc, $price, $stock, $categoryId)
        );
        $newId = $this->db->lastInsertId();

        if ($newId) {
            $url = (defined('APP_URL') ? APP_URL : '') . '/products/' . $slug;
            $this->out($chatId, "Da tao san pham '{$name}' ID:{$newId}\nURL: {$url}");
        } else {
            $this->out($chatId, 'Khong the tao san pham.');
        }
    }

    // ── EXECUTOR: update_product ──────────────────────────────────────
    private function execUpdateProduct($params, $chatId, $aiReply) {
        $id     = (int)($params['id'] ?? 0);
        $fields = $params['fields'] ?? array();
        if (!$id || !$fields) { $this->out($chatId, 'Thieu ID hoac fields.'); return; }

        $allowed = array('name','price','stock','description','is_active','specs','category_id','brand_id');
        $sets = array(); $vals = array();
        foreach ($fields as $k => $v) {
            if (in_array($k, $allowed)) { $sets[] = "{$k}=?"; $vals[] = $v; }
        }
        if (!$sets) { $this->out($chatId, 'Khong co truong hop le.'); return; }

        $vals[] = $id;
        $this->db->query("UPDATE products SET " . implode(',', $sets) . " WHERE id=?", $vals);
        $this->out($chatId, "Da cap nhat SP #{$id}: " . implode(', ', array_keys($fields)));
    }

    // ── EXECUTOR: delete_product ──────────────────────────────────────
    private function execDeleteProduct($params, $chatId, $aiReply) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) { $this->out($chatId, 'Thieu ID san pham.'); return; }

        $p = $this->db->fetch("SELECT name FROM products WHERE id=?", array($id));
        if (!$p) { $this->out($chatId, "Khong tim thay SP #{$id}"); return; }

        $this->db->query("UPDATE products SET is_deleted=1 WHERE id=?", array($id));
        $this->out($chatId, "Da xoa SP #{$id} — {$p['name']}");
    }

    // ── EXECUTOR: search ──────────────────────────────────────────────
    private function execSearch($params, $chatId, $aiReply) {
        $q    = trim($params['q'] ?? '');
        $type = $params['type'] ?? 'product';
        if (!$q) { $this->out($chatId, $aiReply ?: 'Thieu tu khoa tim kiem.'); return; }

        try {
            if ($type === 'order') {
                $rows = $this->db->fetchAll(
                    "SELECT id,order_code,fullname,total,status FROM orders WHERE (order_code LIKE ? OR fullname LIKE ?) AND is_deleted=0 LIMIT 5",
                    array("%{$q}%", "%{$q}%")
                );
                if (!$rows) { $this->out($chatId, "Khong tim thay don hang nao cho: {$q}"); return; }
                $msg = "Ket qua don hang '{$q}':\n\n";
                foreach ($rows as $r) $msg .= "#{$r['id']} {$r['order_code']} {$r['fullname']} — " . number_format((float)$r['total'],0,',','.') . "d | {$r['status']}\n";
            } elseif ($type === 'customer') {
                $rows = $this->db->fetchAll(
                    "SELECT id,fullname,email,phone FROM users WHERE role=0 AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?) LIMIT 5",
                    array("%{$q}%", "%{$q}%", "%{$q}%")
                );
                if (!$rows) { $this->out($chatId, "Khong tim thay khach hang nao cho: {$q}"); return; }
                $msg = "Khach hang '{$q}':\n\n";
                foreach ($rows as $r) $msg .= "#{$r['id']} {$r['fullname']} — {$r['email']} | {$r['phone']}\n";
            } else {
                $rows = $this->db->fetchAll(
                    "SELECT id,name,price,stock FROM products WHERE (name LIKE ? OR description LIKE ?) AND is_deleted=0 LIMIT 5",
                    array("%{$q}%", "%{$q}%")
                );
                if (!$rows) { $this->out($chatId, "Khong tim thay san pham nao cho: {$q}"); return; }
                $msg = "San pham '{$q}':\n\n";
                foreach ($rows as $r) $msg .= "#{$r['id']} {$r['name']} — " . number_format((float)$r['price'],0,',','.') . "d | Kho: {$r['stock']}\n";
            }
            $this->out($chatId, $msg);
        } catch (Exception $e) {
            $this->out($chatId, 'Loi tim kiem: ' . $e->getMessage());
        }
    }

    // ── EXECUTOR: full_auto ───────────────────────────────────────────
    private function execFullAuto($params, $chatId, $aiReply) {
        $f          = isset($params['_filter']) && is_array($params['_filter']) ? $params['_filter'] : array();
        $needImage  = isset($f['need_image'])  ? (bool)$f['need_image']  : true;
        $needSpecs  = isset($f['need_specs'])  ? (bool)$f['need_specs']  : true;
        $activeOnly = isset($f['active_only']) ? (bool)$f['active_only'] : true;
        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadPath)) @mkdir($uploadPath, 0755, true);

        $whereActive = $activeOnly ? "is_active=1 AND is_deleted=0" : "is_deleted=0";

        // Fetch ALL products — no LIMIT, PHP decides what needs work
        $all = $this->db->fetchAll(
            "SELECT id, name, image, specs FROM products WHERE {$whereActive} ORDER BY id ASC"
        );

        $totalAll    = count($all);
        $totalActive = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND is_deleted=0")->fetchColumn();

        // PHP-side scan: file_exists for image, meaningful JSON for specs
        $needImageList = array(); $needSpecsList = array();
        foreach ($all as $p) {
            if ($needImage) {
                $imgVal = trim($p['image'] ?? '');
                if ($imgVal === '' || !file_exists($uploadPath . $imgVal)) {
                    $needImageList[] = $p;
                }
            }
            if ($needSpecs) {
                $s = trim($p['specs'] ?? '');
                $a = ($s !== '' && $s !== '{}' && $s !== '[]') ? json_decode($s, true) : null;
                if (!is_array($a) || count($a) < 2) {
                    $needSpecsList[] = $p;
                }
            }
        }

        $imgTotal   = count($needImageList);
        $specsTotal = count($needSpecsList);

        if (!$imgTotal && !$specsTotal) {
            $this->out($chatId,
                "Đã kiểm tra toàn bộ {$totalAll}/{$totalActive} SP:\n"
                . "  Tất cả ảnh tồn tại (file check) và specs có >=2 keys.\n"
                . "  Không cần xử lý thêm."
            );
            return;
        }

        $this->out($chatId,
            "Tìm thấy {$imgTotal} thiếu ảnh, {$specsTotal} thiếu specs trong tổng {$totalActive} SP.\n"
            . "Bắt đầu xử lý theo batch 10..."
        );

        $imgDone = 0; $rembgDone = 0; $specsDone = 0; $batchCount = 0;

        // ── Phase 1: find image → rembg (batch 10) ───────────────────
        $chunks = array_chunk($needImageList, 10);
        foreach ($chunks as $batch) {
            foreach ($batch as $p) {
                $fname = $this->downloadPixabayImage($p['name'], $p['id'], $uploadPath);
                if ($fname) {
                    $imgDone++;
                    $cleanFname = $this->tryRembg($fname, $p['id'], $uploadPath);
                    $saveFname  = $cleanFname ?: $fname;
                    $this->db->query("UPDATE products SET image=? WHERE id=?", array($saveFname, $p['id']));
                    if ($cleanFname) $rembgDone++;
                }
            }
            $batchCount += count($batch);
            $this->writeProgress($batchCount, $imgTotal, "Ảnh: {$batchCount}/{$imgTotal} — {$imgDone} lưu OK", 'full_auto');
            $this->out($chatId, "Ảnh: {$batchCount}/{$imgTotal} xử lý, {$imgDone} lưu thành công...");
        }

        // ── Phase 2: fill specs (batch 10) ───────────────────────────
        $batchCount = 0;
        $chunks = array_chunk($needSpecsList, 10);
        foreach ($chunks as $batch) {
            foreach ($batch as $p) {
                $ok = $this->fillSpecsForProduct($p['id'], $p['name']);
                if ($ok) $specsDone++;
            }
            $batchCount += count($batch);
            $this->writeProgress($batchCount, $specsTotal, "Specs: {$batchCount}/{$specsTotal} — {$specsDone} cập nhật OK", 'full_auto');
            $this->out($chatId, "Specs: {$batchCount}/{$specsTotal} xử lý, {$specsDone} cập nhật thành công...");
        }

        $this->out($chatId,
            "Hoàn thành Full Auto! Tổng {$totalActive} SP:\n"
            . "  Ảnh: {$imgDone}/{$imgTotal}" . ($rembgDone ? " ({$rembgDone} xóa nền)" : '') . "\n"
            . "  Thông số: {$specsDone}/{$specsTotal}"
        );
    }

    // ── Helper: download one image from Pixabay ───────────────────────
    private function downloadPixabayImage($productName, $productId, $uploadPath) {
        $debugLog = __DIR__ . '/../../storage/ai_debug.log';
        $logLine  = '[' . date('Y-m-d H:i:s') . "] imgSearch #{$productId} \"{$productName}\": ";

        // ── Pixabay ──────────────────────────────────────────────────
        $pxKey = (defined('PIXABAY_KEY') && PIXABAY_KEY !== '') ? PIXABAY_KEY : '';
        if ($pxKey) {
            $q      = urlencode($productName . ' computer hardware');
            $pxUrl  = "https://pixabay.com/api/?key={$pxKey}&q={$q}&image_type=photo&per_page=5&safesearch=true&lang=en";
            $pxRes  = @file_get_contents($pxUrl);
            $pxDat  = $pxRes ? json_decode($pxRes, true) : null;
            $pxHits = $pxDat['hits'] ?? array();
            @file_put_contents($debugLog, $logLine . 'Pixabay hits=' . count($pxHits)
                . ' raw=' . substr($pxRes ?: 'EMPTY', 0, 300) . "\n", FILE_APPEND | LOCK_EX);

            foreach ($pxHits as $hit) {
                $imgUrl  = $hit['largeImageURL'] ?? $hit['webformatURL'] ?? '';
                if (!$imgUrl) continue;
                $imgData = @file_get_contents($imgUrl);
                if (!$imgData || strlen($imgData) < 5000) continue;
                $fname = 'auto_' . $productId . '_' . uniqid() . '.jpg';
                file_put_contents($uploadPath . $fname, $imgData);
                @file_put_contents($debugLog, $logLine . "Pixabay OK fname={$fname} size=" . strlen($imgData) . "\n", FILE_APPEND | LOCK_EX);
                return $fname;
            }
            @file_put_contents($debugLog, $logLine . "Pixabay FAILED (no usable image)\n", FILE_APPEND | LOCK_EX);
        } else {
            @file_put_contents($debugLog, $logLine . "PIXABAY_KEY missing\n", FILE_APPEND | LOCK_EX);
        }

        // ── Google Custom Search fallback ─────────────────────────────
        $gKey = (defined('GOOGLE_SEARCH_KEY') && GOOGLE_SEARCH_KEY !== '') ? GOOGLE_SEARCH_KEY : '';
        $gCx  = (defined('GOOGLE_SEARCH_CX')  && GOOGLE_SEARCH_CX  !== '') ? GOOGLE_SEARCH_CX  : '';
        if ($gKey && $gCx) {
            $gQ    = urlencode($productName . ' computer hardware');
            $gUrl  = "https://www.googleapis.com/customsearch/v1?key={$gKey}&cx={$gCx}&q={$gQ}&searchType=image&num=5";
            $gRes  = @file_get_contents($gUrl);
            $gDat  = $gRes ? json_decode($gRes, true) : null;
            $items = $gDat['items'] ?? array();
            @file_put_contents($debugLog, $logLine . 'Google hits=' . count($items)
                . ' raw=' . substr($gRes ?: 'EMPTY', 0, 300) . "\n", FILE_APPEND | LOCK_EX);

            foreach ($items as $item) {
                $imgUrl  = $item['link'] ?? '';
                if (!$imgUrl) continue;
                $imgData = @file_get_contents($imgUrl);
                if (!$imgData || strlen($imgData) < 5000) continue;
                $fname = 'auto_g_' . $productId . '_' . uniqid() . '.jpg';
                file_put_contents($uploadPath . $fname, $imgData);
                @file_put_contents($debugLog, $logLine . "Google OK fname={$fname} size=" . strlen($imgData) . "\n", FILE_APPEND | LOCK_EX);
                return $fname;
            }
            @file_put_contents($debugLog, $logLine . "Google FAILED (no usable image)\n", FILE_APPEND | LOCK_EX);
        } else {
            @file_put_contents($debugLog, $logLine . "GOOGLE_SEARCH_KEY/CX missing — no fallback\n", FILE_APPEND | LOCK_EX);
        }

        return null;
    }

    // ── Helper: remove background (Python rembg → remove.bg fallback) ─
    private function tryRembg($srcFname, $productId, $uploadPath) {
        $srcPath  = $uploadPath . $srcFname;
        $outFname = 'nobg_' . $productId . '_' . uniqid() . '.png';
        $outPath  = $uploadPath . $outFname;

        // Try Python rembg CLI
        if (function_exists('shell_exec')) {
            $cmd = 'python -m rembg i ' . escapeshellarg($srcPath) . ' ' . escapeshellarg($outPath) . ' 2>&1';
            @shell_exec($cmd);
            if (file_exists($outPath) && filesize($outPath) > 1000) return $outFname;
            @unlink($outPath);
        }

        // Fallback: remove.bg API
        $key = defined('REMOVEBG_KEY') ? REMOVEBG_KEY : '';
        if (!$key) return null;
        $ch = curl_init('https://api.remove.bg/v1.0/removebg');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => array('image_file' => new CURLFile($srcPath), 'size' => 'auto'),
            CURLOPT_HTTPHEADER     => array('X-Api-Key: ' . $key),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $result = curl_exec($ch);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 && strlen($result) > 1000) {
            file_put_contents($outPath, $result);
            return $outFname;
        }
        return null;
    }

    // ── Helper: AI-generate specs/desc for one product ────────────────
    private function fillSpecsForProduct($id, $name) {
        $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
        if (!$apiKey) return false;

        $prompt = "Sản phẩm máy tính: \"{$name}\"\n"
                . "Viết bằng tiếng Việt:\n"
                . "1) description: mô tả ~100 từ\n"
                . "2) short_desc: tóm tắt ~25 từ\n"
                . "3) specs: JSON thông số kỹ thuật {\"CPU\":\"...\",\"RAM\":\"...\",\"Storage\":\"...\",\"GPU\":\"...\",\"Display\":\"...\"}\n"
                . "Trả về JSON: {\"description\":\"...\",\"short_desc\":\"...\",\"specs\":{}}";

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array(
                'model'           => 'llama-3.3-70b-versatile',
                'messages'        => array(
                    array('role' => 'system', 'content' => 'Chuyên gia phần cứng máy tính. Trả về JSON chính xác, không markdown.'),
                    array('role' => 'user',   'content' => $prompt),
                ),
                'max_tokens'      => 700,
                'temperature'     => 0.2,
                'response_format' => array('type' => 'json_object'),
            )),
            CURLOPT_HTTPHEADER     => array('Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res    = curl_exec($ch);
        curl_close($ch);
        $data   = json_decode($res, true);
        $parsed = json_decode($data['choices'][0]['message']['content'] ?? '{}', true);
        if (!$parsed) return false;

        $sets = array(); $vals = array();
        if (!empty($parsed['description'])) { $sets[] = 'description=?'; $vals[] = $parsed['description']; }
        if (!empty($parsed['short_desc']))  { $sets[] = 'short_desc=?';  $vals[] = $parsed['short_desc']; }
        if (!empty($parsed['specs'])) {
            $specs = is_array($parsed['specs']) ? json_encode($parsed['specs'], JSON_UNESCAPED_UNICODE) : $parsed['specs'];
            $sets[] = 'specs=?'; $vals[] = $specs;
        }
        if (!$sets) return false;
        $vals[] = $id;
        $this->db->query("UPDATE products SET " . implode(',', $sets) . " WHERE id=?", $vals);
        return true;
    }

    // ── Pre-classifier: bypass AI for unambiguous commands ────────────
    private function preClassify($text) {
        $t = trim(mb_strtolower($text, 'UTF-8'));

        // full_auto patterns: "full auto", "full_auto", "tự động hóa", "tu dong hoa", "auto all"
        if (preg_match('/full[\s_-]?auto|t[uư]\s*d[oô]ng\s*h[oó]a|auto\s+all|chay\s+tu\s+dong/ui', $t)) {
            preg_match('/(\d+)/', $t, $m);
            $limit = isset($m[1]) ? (int)$m[1] : 10;
            return array(
                'action'          => 'full_auto',
                'params'          => array('limit' => $limit),
                'reply_vn'        => 'Bắt đầu Full Auto...',
                'steps'           => array('Tìm sản phẩm thiếu ảnh', 'Tải ảnh Pixabay', 'Xóa nền rembg', 'Fill specs AI', 'Tổng kết'),
                'requires_confirm'=> false,
            );
        }

        // verify_images patterns
        if (preg_match('/ki[eể]m\s*tra\s*h[iì]nh|qu[eé]t\s*[aả]nh|verify\s*image|check\s*image|r[aà]o\s*[aả]nh/ui', $t)) {
            return array('action' => 'verify_images', 'params' => array(), 'reply_vn' => 'Bắt đầu kiểm tra ảnh bằng AI...', 'requires_confirm' => false);
        }

        // ai_insight patterns — phải kiểm tra TRƯỚC "báo cáo" thông thường
        if (preg_match('/ph[aâ]n\s*t[íi]ch\s*ai|ai\s*insight|nh[aậ]t\s*k[yý]\s*ai|b[aá]o\s*c[aá]o\s*ai|insight/ui', $t)) {
            preg_match('/(\d+)\s*(?:gi[oờ]|h\b)/ui', $t, $m);
            $hours = isset($m[1]) ? (int)$m[1] : 24;
            if ($hours < 1)   $hours = 24;
            if ($hours > 168) $hours = 168;
            return array(
                'action'          => 'ai_insight',
                'params'          => array('hours' => $hours),
                'reply_vn'        => "⏳ Đang tạo báo cáo AI {$hours}h qua...",
                'requires_confirm'=> false,
            );
        }

        // report patterns — có dấu và không dấu
        if (preg_match('/b[aá]o\s*c[aá]o|bao\s+cao|doanh\s*thu|revenue|report|th[oô]ng\s*k[eê]|thong\s*ke|stats/ui', $t)) {
            $period = 'today';
            if (preg_match('/tu[aầ]n|tuan|week|7\s*ng[aà]y|7\s*ngay/ui', $t))   $period = 'week';
            if (preg_match('/th[aá]ng|thang|month|30\s*ng[aà]y|30\s*ngay/ui', $t)) $period = 'month';
            $this->debugLog("preClassify: report period={$period}");
            return array('action' => 'report', 'params' => array('period' => $period), 'reply_vn' => '', 'requires_confirm' => false);
        }

        return null;
    }

    // ── EXECUTOR: bulk ────────────────────────────────────────────────
    private function execBulk($params, $chatId, $aiReply) {
        $filter     = $params['filter'] ?? 'no_image';
        $action     = $params['action'] ?? 'find_images';
        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';

        if ($filter === 'low_stock') {
            // low_stock: no PHP re-filter needed, DB condition is definitive
            $candidates = $this->db->fetchAll(
                "SELECT p.id, p.name, p.image, p.specs FROM products p
                 LEFT JOIN inventory i ON p.id=i.product_id
                 WHERE p.is_active=1 AND p.is_deleted=0 AND COALESCE(i.stock_quantity, p.stock)<=5
                 ORDER BY p.id ASC"
            );
            $products = $candidates;
        } else {
            // Fetch ALL active; PHP file/specs check decides what needs work
            $candidates = $this->db->fetchAll(
                "SELECT id, name, image, specs FROM products WHERE is_active=1 AND is_deleted=0 ORDER BY id ASC"
            );
            $products = array();
            foreach ($candidates as $p) {
                if ($filter === 'no_image' || $action === 'find_images') {
                    $imgVal = trim($p['image'] ?? '');
                    if ($imgVal === '' || !file_exists($uploadPath . $imgVal)) $products[] = $p;
                } elseif ($filter === 'no_specs' || $action === 'fill_specs') {
                    $s = trim($p['specs'] ?? '');
                    $a = ($s !== '' && $s !== '{}' && $s !== '[]') ? json_decode($s, true) : null;
                    if (!is_array($a) || count($a) < 2) $products[] = $p;
                } else {
                    $products[] = $p;
                }
            }
        }

        $totalActive = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND is_deleted=0")->fetchColumn();
        $total = count($products);
        if (!$total) {
            $this->out($chatId, "Không tìm thấy SP cần bulk [{$filter}] trong tổng {$totalActive} SP active.");
            return;
        }

        $this->out($chatId, "Tìm thấy {$total} SP cần [{$filter}→{$action}] trong tổng {$totalActive} SP. Bắt đầu...");

        $done = 0;
        foreach (array_chunk($products, 10) as $batch) {
            foreach ($batch as $p) {
                if ($action === 'find_images') {
                    $fname = $this->downloadPixabayImage($p['name'], $p['id'], $uploadPath);
                    if ($fname) { $this->db->query("UPDATE products SET image=? WHERE id=?", array($fname, $p['id'])); $done++; }
                } elseif ($action === 'fill_specs') {
                    if ($this->fillSpecsForProduct($p['id'], $p['name'])) $done++;
                } elseif ($action === 'remove_bg') {
                    $imgVal = trim($p['image'] ?? '');
                    if ($imgVal && file_exists($uploadPath . $imgVal)) {
                        $fname = $this->tryRembg($imgVal, $p['id'], $uploadPath);
                        if ($fname) { $this->db->query("UPDATE products SET image=? WHERE id=?", array($fname, $p['id'])); $done++; }
                    }
                }
            }
            $this->writeProgress($done, $total, "Batch {$done}/{$total} SP", 'bulk');
            $this->out($chatId, "Batch xong: {$done}/{$total} SP hoàn thành...");
        }
        $this->clearProgress();
        $this->out($chatId, "Bulk hoàn thành! {$done}/{$total} SP trong tổng {$totalActive} SP active.");
    }

    // ── AI vision: confirm image matches product ───────────────────────
    private function validateImageWithAI($imageData, $productName) {
        $apiKey = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
        if (!$apiKey || !$imageData) return true; // no key → skip validation, accept image

        $mime = (substr($imageData, 1, 3) === 'PNG') ? 'image/png' : 'image/jpeg';
        $b64  = base64_encode($imageData);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => array('Authorization: Bearer '.$apiKey, 'Content-Type: application/json'),
            CURLOPT_POSTFIELDS     => json_encode(array(
                'model'      => 'meta-llama/llama-4-scout-17b-16e-instruct',
                'max_tokens' => 5,
                'messages'   => array(array(
                    'role'    => 'user',
                    'content' => array(
                        array('type' => 'image_url', 'image_url' => array('url' => "data:{$mime};base64,{$b64}")),
                        array('type' => 'text',      'text'      => "Does this image show the product \"{$productName}\"? Reply YES or NO only."),
                    ),
                )),
            ), JSON_UNESCAPED_UNICODE),
        ));
        $res = curl_exec($ch);
        curl_close($ch);
        $answer = strtoupper(trim(json_decode($res, true)['choices'][0]['message']['content'] ?? 'YES'));
        return strpos($answer, 'NO') === false;
    }

    // ── EXECUTOR: verify_images ────────────────────────────────────────
    private function execVerifyImages($params, $chatId, $aiReply) {
        set_time_limit(300);
        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/../../uploads/products/';
        $products   = $this->db->fetchAll(
            "SELECT id, name, image FROM products WHERE is_active=1 AND is_deleted=0 AND image!='' ORDER BY id ASC"
        );
        $total = count($products);
        $this->out($chatId, "Kiểm tra ảnh {$total} SP bằng AI vision...");

        $correct = 0; $wrong = 0; $replaced = 0;
        foreach ($products as $p) {
            $imgPath = $uploadPath . trim($p['image']);
            if (!file_exists($imgPath)) { $wrong++; continue; }
            $imgData = @file_get_contents($imgPath);
            if (!$imgData) { $wrong++; continue; }
            if ($this->validateImageWithAI($imgData, $p['name'])) {
                $correct++;
            } else {
                $wrong++;
                $oldImg = $p['image'];
                $this->execFindImages(array('id' => $p['id']), $chatId, '');
                $updated = $this->db->fetch("SELECT image FROM products WHERE id=?", array($p['id']));
                if ($updated && $updated['image'] !== $oldImg) $replaced++;
            }
        }
        $this->out($chatId, "Kết quả: {$correct} ảnh đúng, {$wrong} ảnh sai → đã đổi {$replaced} ảnh.");
    }

    // ── Progress file writer ───────────────────────────────────────────
    private function writeProgress($done, $total, $step, $action = '') {
        $pct = $total > 0 ? min(99, (int)round($done / $total * 100)) : 0;
        @file_put_contents(
            __DIR__ . '/../../storage/ai_progress.json',
            json_encode(array(
                'active'  => true,
                'action'  => $action,
                'step'    => $step,
                'done'    => $done,
                'total'   => $total,
                'percent' => $pct,
                'ts'      => time(),
            ), JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function clearProgress() {
        @file_put_contents(__DIR__ . '/../../storage/ai_progress.json',
            '{"active":false,"percent":100}', LOCK_EX);
    }

    // ── Internal API caller (reuses existing ApiController endpoints) ──
    private function callApi($path, $body = array()) {
        $base   = rtrim(defined('APP_URL') ? APP_URL : 'http://localhost/tuanhuy_computer', '/');
        $cookie = session_name() . '=' . session_id();
        $ch     = curl_init($base . $path);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIE         => $cookie,
        ));
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err || !$res) return null;
        return json_decode($res, true);
    }

    // ── Output router: Telegram or web accumulator ────────────────────
    private function out($chatId, $text) {
        if (!$text) return;
        if ($this->webMode) {
            $this->webOutput[] = $text;
        } elseif ($chatId) {
            $ok = TelegramNotifier::send($chatId, $text);
            $this->debugLog("out chatId={$chatId} ok=" . ($ok ? '1' : '0') . " len=" . mb_strlen($text, 'UTF-8') . " preview=" . mb_substr(strip_tags($text), 0, 60, 'UTF-8'));
        }
    }

    private function sendMsg($chatId, $text) {
        if ($chatId && $text) {
            $ok = TelegramNotifier::send($chatId, $text);
            $this->debugLog("sendMsg chatId={$chatId} ok=" . ($ok ? '1' : '0') . " preview=" . mb_substr(strip_tags($text), 0, 60, 'UTF-8'));
        }
    }

    // ── Debug logger ──────────────────────────────────────────────────
    private function debugLog($msg) {
        if (!$this->tgDebugLog) return;
        @file_put_contents(
            $this->tgDebugLog,
            '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    // ── Pending confirmations ─────────────────────────────────────────
    private function storePending($action) {
        file_put_contents($this->pendingFile, json_encode($action, JSON_UNESCAPED_UNICODE));
    }

    private function clearPending() {
        if (file_exists($this->pendingFile)) @unlink($this->pendingFile);
    }

    private function executePendingTelegram($chatId) {
        if (!file_exists($this->pendingFile)) {
            $this->sendMsg($chatId, 'Khong co thao tac nao dang cho xac nhan.');
            return;
        }
        $action = json_decode(file_get_contents($this->pendingFile), true);
        $this->clearPending();
        if (!$action) { $this->sendMsg($chatId, 'Du lieu thao tac bi loi.'); return; }
        $action['requires_confirm'] = false;
        $this->executeAction($action, $chatId);
    }

    // ── Daemon mode: long-polling loop (call from bot_daemon.php) ────
    public function startDaemon() {
        set_time_limit(0);
        $site = defined('APP_NAME') ? APP_NAME : 'Bot';

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo " {$site} — AI Telegram Bot Daemon\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo " Token : ..." . substr($this->token, -8) . "\n";
        echo " ChatID: " . ($this->adminChatId ?: '(chưa set)') . "\n";
        echo " Mode  : long-polling 25s (phản hồi tức thì)\n";
        echo " Log   : storage/telegram_debug.log\n";
        echo " Dừng  : Ctrl+C\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        if (!$this->token) {
            echo "[ERROR] TELEGRAM_BOT_TOKEN chưa cấu hình.\n";
            exit(1);
        }

        TelegramNotifier::sendToAdmin("🟢 <b>{$site} AI Bot</b> daemon đã khởi động\n🕐 " . date('H:i:s d/m/Y'));
        $this->debugLog("DAEMON START pid=" . getmypid());

        $ticks = 0;
        while (true) {
            try {
                $offset  = $this->getOffset();
                // Long-poll timeout=25: blocks up to 25s, returns instantly when message arrives
                $updates = $this->fetchUpdates($offset, 25);

                foreach ($updates as $up) {
                    $text = mb_substr($up['message']['text'] ?? '(non-text)', 0, 60, 'UTF-8');
                    echo "[" . date('H:i:s') . "] IN: {$text}\n";
                    $this->processUpdate($up);
                    $this->saveOffset((int)$up['update_id'] + 1);
                    $ticks++;
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();
                echo "[" . date('H:i:s') . "] EXCEPTION: {$msg}\n";
                $this->debugLog("DAEMON EXCEPTION: {$msg}");
                sleep(5);
            }
        }
    }

    // ── Polling helpers ───────────────────────────────────────────────
    private function fetchUpdates($offset, $timeout = 0) {
        $curlTimeout = $timeout > 0 ? $timeout + 5 : 10; // cURL timeout > Telegram timeout
        $url = 'https://api.telegram.org/bot' . $this->token
             . '/getUpdates?offset=' . $offset . '&limit=20&timeout=' . $timeout;
        $ch  = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $curlTimeout,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $res  = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err) {
            $this->debugLog("fetchUpdates cURL error: {$err}");
            return array();
        }
        $data = json_decode($res, true);
        return ($data && !empty($data['ok']) && !empty($data['result'])) ? $data['result'] : array();
    }

    private function getOffset() {
        $f = __DIR__ . '/../../storage/telegram_offset.txt';
        if (file_exists($f)) { $v = (int)file_get_contents($f); if ($v > 0) return $v; }
        try {
            $row = $this->db->fetch("SELECT new_data FROM action_logs WHERE action='tg_offset' ORDER BY id DESC LIMIT 1");
            return $row ? (int)$row['new_data'] : 0;
        } catch (Exception $e) { return 0; }
    }

    private function saveOffset($offset) {
        $f = __DIR__ . '/../../storage/telegram_offset.txt';
        @file_put_contents($f, (string)$offset);
    }
}
