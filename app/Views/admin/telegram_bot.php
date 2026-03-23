<?php require_once __DIR__.'/layout_top.php'; ?>

<style>
.log-entry{background:#161616;border:1px solid #1e1e1e;border-radius:9px;padding:.75rem .9rem;margin-bottom:.55rem;animation:fadeIn .3s}
.log-entry:hover{border-color:#2a2a2a}
.log-cmd{font-size:.68rem;color:#229ED9;font-weight:700;display:flex;align-items:center;gap:.35rem;margin-bottom:.3rem}
.log-action{font-size:.78rem;color:#fff;font-weight:700;margin-bottom:.35rem;display:flex;align-items:center;gap:.4rem}
.log-result{font-size:.75rem;color:#888;line-height:1.7;white-space:pre-wrap;word-break:break-word;max-height:200px;overflow-y:auto;padding-right:.25rem}
.log-result::-webkit-scrollbar{width:3px}.log-result::-webkit-scrollbar-thumb{background:#333}
.log-time{font-size:.62rem;color:#333;margin-top:.35rem;text-align:right}
#chat-log{max-height:500px;overflow-y:auto}
#chat-log::-webkit-scrollbar{width:3px}#chat-log::-webkit-scrollbar-thumb{background:#333}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.live-dot{width:7px;height:7px;background:#4ade80;border-radius:50%;display:inline-block;animation:blink 1.4s infinite;margin-right:.3rem}
</style>

<div style="max-width:860px">

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.65rem;margin-bottom:1.1rem">
    <div>
      <h2 style="color:#fff;font-size:1rem;font-weight:800;margin:0;display:flex;align-items:center;gap:.45rem">
        <i class="fa-brands fa-telegram" style="color:#229ED9"></i> Telegram Bot Console
      </h2>
      <p style="color:#444;font-size:.72rem;margin:.2rem 0 0">
        <span class="live-dot"></span>
        <span id="status-txt">Đang kết nối...</span>
        &nbsp;·&nbsp; Bot: <b style="color:#ccc">@TuanHuyComputerBot</b>
      </p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
      <button id="toggle-btn" class="btn-g" onclick="togglePoll()" style="font-size:.78rem;display:flex;align-items:center;gap:.35rem">
        <i class="fa-solid fa-pause"></i> Tạm dừng
      </button>
      <button class="btn-g" onclick="clearLog()" style="font-size:.78rem">
        <i class="fa-solid fa-trash"></i> Xóa log
      </button>
      <div style="background:rgba(34,158,217,.08);border:1px solid rgba(34,158,217,.2);border-radius:8px;padding:.38rem .75rem;font-size:.72rem;color:#38bdf8;display:flex;align-items:center;gap:.4rem">
        <i class="fa-solid fa-terminal"></i>
        Daemon: <code style="background:rgba(0,0,0,.3);padding:1px 6px;border-radius:4px;font-size:.7rem;color:#7dd3fc">php bot_daemon.php</code>
        <span style="color:#555">để bot hoạt động 24/7</span>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:.85rem">

    <!-- Chat log -->
    <div class="card" style="padding:1rem">
      <div style="font-size:.72rem;color:#444;margin-bottom:.65rem;display:flex;align-items:center;justify-content:space-between">
        <span>NHẬT KÝ TIN NHẮN</span>
        <span id="msg-count" style="color:#555">0 tin nhắn</span>
      </div>
      <div id="chat-log">
        <div id="empty-state" style="text-align:center;padding:3rem 1rem;color:#333">
          <i class="fa-brands fa-telegram" style="font-size:2rem;margin-bottom:.65rem;display:block;color:#1a1a1a"></i>
          <div style="font-size:.8rem">Đang chờ tin nhắn từ Telegram...</div>
          <div style="font-size:.72rem;margin-top:.35rem">Nhắn vào <b style="color:#555">@TuanHuyComputerBot</b></div>
        </div>
      </div>
    </div>

    <!-- Sidebar info -->
    <div style="display:flex;flex-direction:column;gap:.65rem">

      <!-- Send test message -->
      <div class="card" style="padding:.9rem">
        <div style="font-size:.72rem;color:#444;margin-bottom:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px">GỬI TIN NHẮN</div>
        <textarea id="test-msg" class="form-inp" rows="3" placeholder="Nhập lệnh hoặc câu hỏi..." style="resize:vertical;font-size:.8rem"></textarea>
        <button onclick="sendTestMsg()" class="btn-r" style="width:100%;margin-top:.5rem;font-size:.8rem">
          <i class="fa-solid fa-paper-plane"></i> Gửi cho bot
        </button>
      </div>

      <!-- Quick commands -->
      <div class="card" style="padding:.9rem">
        <div style="font-size:.72rem;color:#444;margin-bottom:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px">LỆNH NHANH</div>
        <div style="display:flex;flex-direction:column;gap:.35rem">
          <?php foreach(array(
            array('thống kê','📊'),
            array('doanh thu','💰'),
            array('đơn hàng','🛒'),
            array('tồn kho','📦'),
            array('báo cáo','🤖'),
            array('khách hàng','👤'),
          ) as $cmd): ?>
          <button onclick="quickCmd('<?= $cmd[0] ?>')" class="btn-g" style="text-align:left;font-size:.78rem;padding:.38rem .65rem">
            <?= $cmd[1] ?> <?= $cmd[0] ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Poll stats -->
      <div class="card" style="padding:.9rem">
        <div style="font-size:.72rem;color:#444;margin-bottom:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px">THỐNG KÊ POLL</div>
        <div style="font-size:.78rem;color:#666;line-height:2">
          Tổng xử lý: <span id="stat-total" style="color:#ddd">0</span><br>
          Poll thành công: <span id="stat-ok" style="color:#4ade80">0</span><br>
          Poll lỗi: <span id="stat-err" style="color:#f87171">0</span><br>
          Lần poll cuối: <span id="stat-last" style="color:#ddd">—</span>
        </div>
      </div>

    </div>
  </div>

  <!-- Command reference -->
  <div style="margin-top:.85rem;background:#111;border:1px solid #1e1e1e;border-radius:9px;padding:.8rem 1rem">
    <div style="font-size:.7rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem">CÁC LỆNH HỖ TRỢ</div>
    <div style="display:flex;flex-wrap:wrap;gap:.3rem">
      <?php foreach(array('thống kê','doanh thu','báo cáo','đơn hàng','đơn [số]','tồn kho','sản phẩm','khách hàng','khóa [tên]','mở khóa [tên]','xác nhận [số]','hủy đơn [số]','help') as $c): ?>
      <code style="background:#1a1a1a;padding:2px 8px;border-radius:5px;font-size:.7rem;color:#888"><?= $c ?></code>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<script>
var APP_URL  = '<?= APP_URL ?>';
var isPolling = true;
var pollTimer = null;
var stats     = { total: 0, ok: 0, err: 0 };
var msgCount  = 0;

function togglePoll() {
  isPolling = !isPolling;
  var btn = document.getElementById('toggle-btn');
  if (isPolling) {
    btn.innerHTML = '<i class="fa-solid fa-pause"></i> Tạm dừng';
    setStatus('Đang lắng nghe...', '#4ade80');
    doPoll();
  } else {
    clearTimeout(pollTimer);
    btn.innerHTML = '<i class="fa-solid fa-play"></i> Tiếp tục';
    setStatus('Đã tạm dừng', '#fbbf24');
  }
}

function setStatus(txt, color) {
  var el = document.getElementById('status-txt');
  if (el) { el.textContent = txt; el.style.color = color || '#555'; }
}

function clearLog() {
  document.getElementById('chat-log').innerHTML =
    '<div id="empty-state" style="text-align:center;padding:3rem 1rem;color:#333">'
    +'<i class="fa-brands fa-telegram" style="font-size:2rem;margin-bottom:.65rem;display:block;color:#1a1a1a"></i>'
    +'<div style="font-size:.8rem">Log đã được xóa.</div></div>';
  msgCount = 0;
  updateMsgCount();
}

function updateMsgCount() {
  var el = document.getElementById('msg-count');
  if (el) el.textContent = msgCount + ' tin nhắn';
}

function addMessage(inText, action, icon, outText, time) {
  var empty = document.getElementById('empty-state');
  if (empty) empty.remove();

  var log  = document.getElementById('chat-log');
  var wrap = document.createElement('div');
  wrap.className = 'log-entry';
  wrap.innerHTML =
    '<div class="log-cmd"><i class="fa-brands fa-telegram"></i> '+ escHtml(inText) +' <span style="color:#333;margin-left:auto">'+ time +'</span></div>'
    + '<div class="log-action">'+ escHtml(icon) +' '+ escHtml(action) +'</div>'
    + '<div class="log-result">'+ escHtml(outText) +'</div>';

  log.insertBefore(wrap, log.firstChild);
  msgCount++;
  updateMsgCount();
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

async function doPoll() {
  if (!isPolling) return;
  try {
    var r = await fetch(APP_URL + '/api/telegram/poll');
    var d = await r.json();

    stats.ok++;
    document.getElementById('stat-ok').textContent  = stats.ok;
    document.getElementById('stat-last').textContent = new Date().toLocaleTimeString('vi-VN');

    if (d.ok && d.log && d.log.length > 0) {
      d.log.forEach(function(m) {
        stats.total++;
        addMessage(m.in, m.action || '—', m.icon || '💬', m.out, m.time);
      });
      document.getElementById('stat-total').textContent = stats.total;
      setStatus('Đã xử lý ' + d.log.length + ' tin nhắn · ' + new Date().toLocaleTimeString('vi-VN'), '#4ade80');
      showToast('Bot: ' + d.log.length + ' tin nhắn mới', 'ok');
    } else {
      setStatus('Đang lắng nghe... · ' + new Date().toLocaleTimeString('vi-VN'), '#555');
    }
  } catch(e) {
    stats.err++;
    document.getElementById('stat-err').textContent = stats.err;
    setStatus('Lỗi kết nối', '#f87171');
  }
  if (isPolling) pollTimer = setTimeout(doPoll, 5000);
}

async function sendTestMsg() {
  var txt = document.getElementById('test-msg').value.trim();
  if (!txt) return;
  document.getElementById('test-msg').value = '';

  // Send via Telegram API directly from browser (needs token in JS — use backend instead)
  try {
    var r = await fetch('https://api.telegram.org/bot<?= TELEGRAM_BOT_TOKEN ?>/sendMessage', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ chat_id: '<?= TELEGRAM_ADMIN_CHAT ?>', text: txt })
    });
    var d = await r.json();
    if (d.ok) {
      showToast('Đã gửi — bot sẽ xử lý trong ~5 giây', 'ok');
      // Poll immediately
      clearTimeout(pollTimer);
      setTimeout(doPoll, 1200);
    } else {
      showToast('Lỗi gửi: ' + (d.description || ''), 'err');
    }
  } catch(e) {
    showToast('Lỗi: ' + e.message, 'err');
  }
}

function quickCmd(cmd) {
  document.getElementById('test-msg').value = cmd;
  sendTestMsg();
}

// Enter key on textarea
document.getElementById('test-msg').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendTestMsg(); }
});

// Start polling
setStatus('Đang lắng nghe...', '#4ade80');
doPoll();
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
