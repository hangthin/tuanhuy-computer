<?php
define('APP_DIR', dirname(__DIR__));
require_once APP_DIR . '/config/database.php';
require_once APP_DIR . '/config/app.php';

if (!isStaff()) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

require_once APP_DIR . '/app/Helpers/TelegramBot.php';
require_once APP_DIR . '/app/Helpers/AITools.php';

$SESSION_KEY = 'ai_chat_history';

// Xóa lịch sử
if (isset($_GET['clear'])) {
    $_SESSION[$SESSION_KEY] = array();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    if ($msg !== '') {
        $history   = isset($_SESSION[$SESSION_KEY]) ? $_SESSION[$SESSION_KEY] : array();
        $history[] = array('role' => 'user', 'text' => htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'), 'time' => date('H:i'));

        $reply     = TelegramBot::dispatch('web', $msg);
        $history[] = array('role' => 'bot', 'text' => $reply, 'time' => date('H:i'));

        $_SESSION[$SESSION_KEY] = array_slice($history, -60);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$history = isset($_SESSION[$SESSION_KEY]) ? $_SESSION[$SESSION_KEY] : array();
$appName = defined('APP_NAME') ? APP_NAME : 'Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>AI Chat — <?= htmlspecialchars($appName) ?></title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, -apple-system, sans-serif; background: #0f1117; color: #e2e8f0; height: 100vh; display: flex; flex-direction: column; }
  header { background: #1e2130; border-bottom: 1px solid #2d3148; padding: .75rem 1rem; display: flex; align-items: center; gap: .75rem; flex-shrink: 0; }
  header h1 { font-size: 1rem; font-weight: 600; }
  header span { font-size: .8rem; color: #64748b; }
  .clear-btn { margin-left: auto; background: transparent; border: 1px solid #374151; color: #9ca3af; padding: .3rem .7rem; border-radius: 6px; cursor: pointer; font-size: .8rem; text-decoration: none; }
  .clear-btn:hover { border-color: #ef4444; color: #ef4444; }
  .chat-box { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: .75rem; }
  .bubble { max-width: 80%; padding: .6rem .9rem; border-radius: 12px; line-height: 1.5; font-size: .9rem; word-break: break-word; }
  .bubble.user { background: #3b4fd8; align-self: flex-end; border-bottom-right-radius: 3px; }
  .bubble.bot  { background: #1e2130; border: 1px solid #2d3148; align-self: flex-start; border-bottom-left-radius: 3px; }
  .bubble .meta { font-size: .7rem; color: #64748b; margin-top: .3rem; }
  .bubble b    { color: #f1f5f9; }
  .bubble code { background: #0f1117; padding: .1rem .3rem; border-radius: 4px; font-size: .85em; }
  .bubble pre  { background: #0f1117; padding: .5rem; border-radius: 6px; overflow-x: auto; font-size: .82em; margin-top: .4rem; }
  .empty { text-align: center; color: #4b5563; margin: auto; }
  .empty p { font-size: .9rem; margin-top: .5rem; }
  form { background: #1e2130; border-top: 1px solid #2d3148; padding: .75rem 1rem; display: flex; gap: .5rem; flex-shrink: 0; }
  form input { flex: 1; background: #0f1117; border: 1px solid #374151; color: #e2e8f0; padding: .6rem .9rem; border-radius: 8px; font-size: .9rem; outline: none; }
  form input:focus { border-color: #3b4fd8; }
  form button { background: #3b4fd8; color: #fff; border: none; padding: .6rem 1.2rem; border-radius: 8px; cursor: pointer; font-size: .9rem; white-space: nowrap; }
  form button:hover { background: #4f63e8; }
  .suggestions { display: flex; flex-wrap: wrap; gap: .4rem; padding: .5rem 1rem; background: #1e2130; border-top: 1px solid #2d3148; }
  .suggestions button { background: #0f1117; border: 1px solid #2d3148; color: #94a3b8; padding: .3rem .7rem; border-radius: 20px; font-size: .78rem; cursor: pointer; }
  .suggestions button:hover { border-color: #3b4fd8; color: #e2e8f0; }
</style>
</head>
<body>
<header>
  <span>🤖</span>
  <h1>AI Chat</h1>
  <span><?= htmlspecialchars($appName) ?> · <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
  <?php if ($history): ?>
  <a href="?clear=1" class="clear-btn" onclick="return confirm('Xóa lịch sử chat?')">Xóa lịch sử</a>
  <?php endif; ?>
</header>

<div class="chat-box" id="chatBox">
<?php if (!$history): ?>
  <div class="empty">
    <div style="font-size:2.5rem">🤖</div>
    <p>Hỏi bất cứ điều gì về cửa hàng.<br>AI có thể đọc và ghi database.</p>
  </div>
<?php else: ?>
  <?php foreach ($history as $m): ?>
    <div class="bubble <?= $m['role'] ?>">
      <?php if ($m['role'] === 'user'): ?>
        <?= $m['text'] ?>
      <?php else: ?>
        <?= $m['text'] ?>
      <?php endif; ?>
      <div class="meta"><?= $m['time'] ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<div class="suggestions">
  <?php foreach (array('thống kê hôm nay','doanh thu tuần này','đơn hàng chờ xử lý','sản phẩm sắp hết kho','báo cáo AI') as $s): ?>
  <button type="button" onclick="setMsg(this.textContent)"><?= $s ?></button>
  <?php endforeach; ?>
</div>

<form method="POST" id="chatForm">
  <input type="text" name="message" id="msgInput" placeholder="Nhập lệnh hoặc câu hỏi..." autocomplete="off" autofocus>
  <button type="submit">Gửi</button>
</form>

<script>
  // Scroll to bottom
  var box = document.getElementById('chatBox');
  box.scrollTop = box.scrollHeight;

  function setMsg(text) {
    document.getElementById('msgInput').value = text;
    document.getElementById('msgInput').focus();
  }

  // Show loading on submit
  document.getElementById('chatForm').addEventListener('submit', function() {
    var btn = this.querySelector('button');
    btn.textContent = '⏳';
    btn.disabled = true;
  });
</script>
</body>
</html>
