<?php include __DIR__ . '/layout_top.php'; ?>
<style>
:root{--ai-bg:#0a0a0a;--ai-card:#111;--ai-border:#1c1c1c;--ai-red:#e30000;--ai-green:#00e57a;--ai-dim:#3a3a3a}
.ai-wrap{display:grid;grid-template-columns:1fr 320px;gap:1rem;height:calc(100vh - 110px);min-height:600px}
.ai-main{display:flex;flex-direction:column;gap:.75rem;min-width:0}
/* Chat */
.chat-box{flex:1;background:var(--ai-bg);border:1px solid var(--ai-border);border-radius:12px;overflow-y:auto;padding:1.1rem;display:flex;flex-direction:column;gap:.85rem;font-family:'Courier New',monospace;font-size:.8rem}
.msg{max-width:78%;padding:.7rem 1rem;border-radius:10px;line-height:1.55;word-break:break-word;animation:fadeIn .25s ease}
.msg.user{align-self:flex-end;background:var(--ai-red);color:#fff;border-bottom-right-radius:3px;position:relative;padding-left:2rem}
.msg.user .copy-btn{left:.35rem;right:auto;color:rgba(255,255,255,.35)}.msg.user .copy-btn:hover{color:#fff}
.msg.ai{align-self:flex-start;background:#161616;border:1px solid var(--ai-border);color:#ccc;border-bottom-left-radius:3px;position:relative;padding-right:2rem}
.msg.ai .msg-label{font-size:.62rem;color:#555;margin-bottom:.3rem;letter-spacing:.5px;text-transform:uppercase}
.copy-btn{position:absolute;top:.35rem;right:.35rem;background:none;border:none;color:#444;cursor:pointer;padding:.15rem .3rem;border-radius:4px;font-size:.72rem;line-height:1;transition:color .2s}.copy-btn:hover{color:#aaa}
/* Typing dots */
.typing-dots span{display:inline-block;width:6px;height:6px;background:#555;border-radius:50%;margin:0 2px;animation:dot-bounce .9s infinite}
.typing-dots span:nth-child(2){animation-delay:.15s}
.typing-dots span:nth-child(3){animation-delay:.3s}
@keyframes dot-bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}
/* Input */
.chat-input-row{display:flex;gap:.5rem}
#ai-input{flex:1;background:#111;border:1.5px solid var(--ai-border);border-radius:8px;padding:.65rem .95rem;color:#ddd;font-family:'Courier New',monospace;font-size:.82rem;outline:none;resize:none;line-height:1.5;max-height:120px;transition:border-color .2s}
#ai-input:focus{border-color:var(--ai-red)}
.send-btn{background:var(--ai-red);border:none;border-radius:8px;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;transition:.15s;flex-shrink:0}
.send-btn:hover{background:#b00}
.send-btn:disabled{background:#2a1010;cursor:not-allowed}
/* Progress timeline */
.timeline{display:flex;flex-direction:column;gap:.5rem}
.tl-item{display:flex;align-items:center;gap:.7rem;padding:.5rem .75rem;border-radius:8px;background:#111;border:1px solid var(--ai-border);font-size:.76rem;color:#888;animation:fadeIn .3s}
.tl-item.done{border-color:#00e57a22;color:#ccc}
.tl-item.run{border-color:#e3000033;color:#e87}
.tl-item.err{border-color:#ef444433;color:#f87171}
.tl-icon{width:20px;text-align:center;flex-shrink:0}
/* SVG ring */
.ring-wrap{position:relative;width:52px;height:52px;flex-shrink:0}
.ring-bg{fill:none;stroke:#1c1c1c;stroke-width:4}
.ring-arc{fill:none;stroke:var(--ai-red);stroke-width:4;stroke-linecap:round;transform-origin:50% 50%;transform:rotate(-90deg);transition:stroke-dashoffset .5s ease}
.ring-pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#ccc;font-family:'Courier New',monospace}
/* Progress bar */
.prog-bar-wrap{height:3px;background:#1c1c1c;border-radius:99px;overflow:hidden;margin-top:.3rem}
.prog-bar{height:100%;background:var(--ai-red);border-radius:99px;transition:width .6s ease;width:0}
/* Sidebar */
.ai-side{display:flex;flex-direction:column;gap:.75rem}
.side-card{background:#111;border:1px solid var(--ai-border);border-radius:12px;padding:.9rem}
.side-card h4{font-size:.65rem;color:#444;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:.7rem}
.stat-row{display:flex;align-items:center;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid #1a1a1a}
.stat-row:last-child{border-bottom:none}
.stat-lbl{font-size:.72rem;color:#666}
.stat-val{font-size:.8rem;font-weight:700;color:#ddd;font-family:'Courier New',monospace}
.status-dot{width:8px;height:8px;border-radius:50%;background:var(--ai-green);box-shadow:0 0 6px var(--ai-green);animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.4}}
/* Terminal log */
.term{background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:.7rem;height:200px;overflow-y:auto;font-family:'Courier New',monospace;font-size:.68rem;line-height:1.6}
.term-line{white-space:pre-wrap;word-break:break-all}
.term-line.info{color:#60a5fa}
.term-line.ok{color:var(--ai-green)}
.term-line.err{color:#f87171}
.term-line.warn{color:#fbbf24}
.term-cursor{display:inline-block;width:6px;height:.8em;background:#555;vertical-align:middle;animation:blink .7s step-end infinite;margin-left:2px}
@keyframes blink{50%{opacity:0}}
/* Confirm modal */
#confirm-modal{position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:9998;display:none;align-items:center;justify-content:center}
#confirm-modal.open{display:flex}
.confirm-box{background:#111;border:1px solid #2a2a2a;border-radius:14px;padding:2rem;max-width:480px;width:92%;text-align:center;animation:fadeIn .25s}
.confirm-countdown{font-size:2rem;font-weight:900;color:var(--ai-red);font-family:'Courier New',monospace;margin:.75rem 0}
.confirm-btns{display:flex;gap:.75rem;justify-content:center;margin-top:1.25rem}
/* Quick cmds */
.qcmd{display:inline-block;background:#181818;border:1px solid var(--ai-border);border-radius:6px;padding:2px 9px;font-size:.68rem;color:#888;cursor:pointer;font-family:'Courier New',monospace;transition:.15s}
.qcmd:hover{border-color:var(--ai-red);color:#e87}
/* Animations */
@keyframes fadeIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
@keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
/* Loading card */
#loading-card{background:#0f0f0f;border:1px solid var(--ai-border);border-radius:12px;padding:1rem;animation:slideUp .25s ease}
.lc-header{display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem}
.lc-timer{font-family:'Courier New',monospace;font-size:.82rem;color:#555;margin-left:auto;letter-spacing:.5px;font-weight:700}
.lc-steps{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.5rem}
.lc-step{display:flex;align-items:center;gap:.6rem;padding:.38rem .65rem;border-radius:7px;background:#0d0d0d;border:1px solid #181818;font-size:.73rem;color:#555;transition:all .3s ease}
.lc-step.active{border-color:#e3000040;color:#e87;background:#150b0b}
.lc-step.done{border-color:#00e57a20;color:#6a6a6a;background:#0b130e}
.step-dot{width:7px;height:7px;border-radius:50%;background:#252525;flex-shrink:0;transition:background .3s,box-shadow .3s}
.lc-step.active .step-dot{background:var(--ai-red);box-shadow:0 0 7px var(--ai-red)}
.lc-step.done .step-dot{background:var(--ai-green)}
.lc-product{font-size:.67rem;color:#3a3a3a;font-family:'Courier New',monospace;padding:.2rem .65rem;min-height:1rem}
/* Message enhancements */
.msg{animation:fadeIn .22s ease}
.msg-ts{font-size:.57rem;color:#333;margin-top:.35rem;text-align:right;line-height:1}
.msg.user .msg-ts{color:rgba(255,255,255,.22)}
.action-badge{display:inline-block;font-size:.54rem;font-weight:800;letter-spacing:.7px;text-transform:uppercase;background:#1c1c1c;border:1px solid #262626;border-radius:3px;padding:1px 5px;color:#4a4a4a;margin-right:.35rem;vertical-align:middle;font-family:'Courier New',monospace}
/* Session separator */
.session-sep{font-size:.6rem;color:#2a2a2a;padding:.6rem 0;display:flex;align-items:center;gap:.5rem;font-family:'Courier New',monospace}
.session-sep::before,.session-sep::after{content:'';flex:1;height:1px;background:#181818}
/* History indicator */
.hist-indicator{font-size:.6rem;color:#252525;padding:.2rem .5rem;display:flex;align-items:center;gap:.35rem;font-family:'Courier New',monospace}
</style>

<div class="ai-wrap">
  <!-- LEFT: chat + progress -->
  <div class="ai-main">

    <!-- Chat window -->
    <div class="chat-box" id="chat-box">
      <div class="msg ai">
        <div class="msg-label"><i class="fa-solid fa-robot" style="margin-right:.3rem"></i>AI Assistant</div>
        Xin chào Admin! Tôi là AI quản trị Tuấn Huy Computer. Bạn có thể hỏi bất kỳ điều gì hoặc ra lệnh bằng tiếng Việt.<br><br>
        Ví dụ: <code>báo cáo hôm nay</code> · <code>tìm ảnh cho SP #12</code> · <code>fill specs SP #5</code> · <code>full auto 10 sản phẩm</code>
      </div>
    </div>

    <!-- Loading card (hidden until AI working) -->
    <div id="loading-card" style="display:none">
      <div class="lc-header">
        <div class="ring-wrap">
          <svg viewBox="0 0 52 52" width="52" height="52">
            <circle class="ring-bg" cx="26" cy="26" r="22"/>
            <circle class="ring-arc" id="ring-arc" cx="26" cy="26" r="22" stroke-dasharray="138" stroke-dashoffset="138"/>
          </svg>
          <div class="ring-pct" id="ring-pct">0%</div>
        </div>
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem">
            <i class="fa-solid fa-robot fa-spin" style="color:var(--ai-red);font-size:.85rem"></i>
            <span style="font-size:.77rem;color:#ccc;font-weight:600">AI đang xử lý</span>
          </div>
          <div class="prog-bar-wrap"><div class="prog-bar" id="prog-bar"></div></div>
        </div>
        <div class="lc-timer" id="lc-timer">00:00</div>
      </div>
      <div class="lc-steps" id="lc-steps"></div>
      <div class="lc-product" id="lc-product"></div>
    </div>

    <!-- Quick commands -->
    <div style="display:flex;flex-wrap:wrap;gap:.4rem;padding:.2rem 0">
      <?php foreach(array('báo cáo hôm nay','báo cáo tuần','tồn kho thấp','tìm ảnh SP #','fill specs SP #','full auto 5','bulk no_image find_images','search sản phẩm') as $cmd): ?>
      <span class="qcmd" onclick="insertCmd(this)"><?= htmlspecialchars($cmd) ?></span>
      <?php endforeach; ?>
      <span class="qcmd" style="color:#f87171;border-color:#2a1010;margin-left:auto" onclick="clearHistory()"><i class="fa-solid fa-trash-can" style="margin-right:.3rem"></i>Xóa lịch sử</span>
    </div>

    <!-- Input -->
    <div class="chat-input-row">
      <textarea id="ai-input" rows="1" placeholder="Nhập lệnh bằng tiếng Việt... (Enter gửi, Shift+Enter xuống dòng)"></textarea>
      <button class="send-btn" id="send-btn" onclick="sendMsg()"><i class="fa-solid fa-paper-plane" style="font-size:.85rem"></i></button>
    </div>
  </div>

  <!-- RIGHT: sidebar -->
  <div class="ai-side">

    <!-- Server status -->
    <div class="side-card">
      <h4>Trạng thái hệ thống</h4>
      <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem">
        <div class="status-dot"></div>
        <span style="font-size:.75rem;color:#60a5fa">AI Online · Groq API</span>
      </div>
      <div class="stat-row"><span class="stat-lbl">Sản phẩm</span><span class="stat-val" id="s-products"><?= number_format($totalProducts) ?></span></div>
      <div class="stat-row"><span class="stat-lbl">Tổng đơn</span><span class="stat-val" id="s-orders"><?= number_format($totalOrders) ?></span></div>
      <div class="stat-row"><span class="stat-lbl">Chờ duyệt</span><span class="stat-val" id="s-pending" style="<?= $pendingOrders>0?'color:#fbbf24':'' ?>"><?= $pendingOrders ?></span></div>
      <div class="stat-row"><span class="stat-lbl">Khách hàng</span><span class="stat-val" id="s-customers"><?= number_format($totalCustomers) ?></span></div>
      <div class="stat-row"><span class="stat-lbl">DT hôm nay</span><span class="stat-val" style="color:#4ade80"><?= number_format($todayRevenue,0,',','.') ?>đ</span></div>
    </div>

    <!-- Terminal log -->
    <div class="side-card" style="flex:1;display:flex;flex-direction:column">
      <h4><i class="fa-solid fa-terminal" style="margin-right:.4rem;color:var(--ai-red)"></i>Terminal Log</h4>
      <div class="term" id="terminal">
        <div class="term-line info">[SYSTEM] AI Admin Console v1.0</div>
        <div class="term-line info">[SYSTEM] Groq llama-3.3-70b-versatile ready</div>
        <div class="term-line ok">[OK] Connected to database</div>
        <div class="term-line" style="color:#555">_<span class="term-cursor"></span></div>
      </div>
    </div>

    <!-- Pending action -->
    <div class="side-card" id="pending-card" style="display:none">
      <h4><i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;margin-right:.4rem"></i>Chờ xác nhận</h4>
      <p id="pending-desc" style="font-size:.72rem;color:#aaa;margin-bottom:.75rem;line-height:1.5"></p>
      <div style="display:flex;gap:.5rem">
        <button class="btn-r" style="flex:1;font-size:.75rem" onclick="sendConfirm()"><i class="fa-solid fa-check"></i> Xác nhận</button>
        <button class="btn-g" style="flex:1;font-size:.75rem" onclick="sendCancel()"><i class="fa-solid fa-xmark"></i> Hủy</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirm modal -->
<div id="confirm-modal">
  <div class="confirm-box">
    <div style="font-size:.72rem;color:#555;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem">Xác nhận thao tác</div>
    <div id="modal-text" style="font-size:.88rem;color:#ccc;line-height:1.6"></div>
    <div class="confirm-countdown" id="modal-countdown">30</div>
    <div style="font-size:.7rem;color:#444;margin-bottom:.5rem">Tự động hủy sau <span id="modal-secs">30</span>s</div>
    <div class="confirm-btns">
      <button class="btn-r" style="padding:.55rem 1.5rem" onclick="confirmAction()"><i class="fa-solid fa-check"></i> Xác nhận</button>
      <button class="btn-g" style="padding:.55rem 1.5rem" onclick="cancelModal()"><i class="fa-solid fa-xmark"></i> Hủy</button>
    </div>
  </div>
</div>

<script>
const APIURL = '<?= APP_URL ?>/admin/ai-assistant';
let isLoading = false;
let modalTimer = null;
let modalSecs  = 30;
let pendingAction = null;

// ── Chat history ──────────────────────────────────────────────────
const HISTORY_KEY = 'ai_chat_history';
const HISTORY_MAX = 50;
let chatMsgs = [];

function saveHistory() {
  localStorage.setItem(HISTORY_KEY, JSON.stringify(chatMsgs.slice(-HISTORY_MAX)));
}

function loadHistory() {
  try {
    const raw = localStorage.getItem(HISTORY_KEY);
    if (!raw) return;
    const msgs = JSON.parse(raw);
    if (!Array.isArray(msgs) || !msgs.length) return;
    const box = document.getElementById('chat-box');
    const sep = document.createElement('div');
    sep.className = 'session-sep';
    sep.innerHTML = '<i class="fa-solid fa-clock-rotate-left"></i>Phiên trước · ' + msgs.length + ' tin nhắn';
    box.appendChild(sep);
    msgs.forEach(function(m) {
      if (m.role === 'user') {
        const el = document.createElement('div');
        el.className = 'msg user';
        el.dataset.ts = m.ts || 0;
        el.innerHTML = copyBtn() + escHtml(m.text) + '<div class="msg-ts">' + relativeTime(m.ts) + '</div>';
        box.appendChild(el);
      } else {
        const el = document.createElement('div');
        el.className = 'msg ai';
        el.dataset.ts = m.ts || 0;
        const icon = actionToIcon(m.action);
        const badge = m.action && m.action !== 'chat' ? '<span class="action-badge">' + escHtml(m.action) + '</span>' : '';
        el.innerHTML = '<div class="msg-label"><i class="' + icon + '" style="margin-right:.3rem"></i>' + badge + 'AI · ' + escHtml(m.action||'chat') + '</div>'
                     + '<span style="white-space:pre-wrap">' + escHtml(m.text) + '</span>'
                     + '<div class="msg-ts">' + relativeTime(m.ts) + '</div>'
                     + copyBtn();
        box.appendChild(el);
      }
      chatMsgs.push(m);
    });
    const ind = document.createElement('div');
    ind.className = 'hist-indicator';
    ind.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#1e3a1e"></i>Lịch sử đã lưu · ' + msgs.length + '/' + HISTORY_MAX + ' tin';
    box.appendChild(ind);
    // Restore saved scroll position or jump to bottom instantly (no animation on load)
    const saved = sessionStorage.getItem('ai_chat_scroll');
    if (saved !== null) {
      box.scrollTop = parseInt(saved, 10);
    } else {
      scrollChat(true);
    }
  } catch(e) {}
}

function clearHistory() {
  localStorage.removeItem(HISTORY_KEY);
  chatMsgs = [];
  const box = document.getElementById('chat-box');
  while (box.children.length > 1) box.removeChild(box.lastChild);
  termLog('warn', '[HISTORY] Đã xóa lịch sử chat');
}

// ── Input handling ────────────────────────────────────────────────
const inp = document.getElementById('ai-input');
inp.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); return; }
  setTimeout(function() {
    inp.style.height = 'auto';
    inp.style.height = Math.min(inp.scrollHeight, 120) + 'px';
  }, 0);
});
inp.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

function insertCmd(el) {
  inp.value = el.textContent;
  inp.focus();
  inp.setSelectionRange(inp.value.length, inp.value.length);
}

// ── Loading card ──────────────────────────────────────────────────
const ACTION_STEPS = {
  find_images: [
    {icon:'fa-solid fa-magnifying-glass', label:'Tìm kiếm hình ảnh'},
    {icon:'fa-solid fa-download',         label:'Tải ảnh về'},
    {icon:'fa-solid fa-eye',              label:'Xác nhận AI'},
    {icon:'fa-solid fa-floppy-disk',      label:'Lưu kết quả'},
  ],
  verify: [
    {icon:'fa-solid fa-eye',               label:'Quét ảnh sản phẩm'},
    {icon:'fa-solid fa-brain',             label:'Phân tích AI'},
    {icon:'fa-solid fa-arrow-rotate-right',label:'Đổi ảnh sai'},
  ],
  report: [
    {icon:'fa-solid fa-database',  label:'Truy vấn dữ liệu'},
    {icon:'fa-solid fa-chart-bar', label:'Tổng hợp báo cáo'},
  ],
  fill_specs: [
    {icon:'fa-solid fa-brain', label:'Phân tích sản phẩm'},
    {icon:'fa-solid fa-pen',   label:'Cập nhật thông số'},
  ],
  full_auto: [
    {icon:'fa-solid fa-magnifying-glass', label:'Tìm kiếm'},
    {icon:'fa-solid fa-download',         label:'Tải ảnh'},
    {icon:'fa-solid fa-brain',            label:'Phân tích AI'},
    {icon:'fa-solid fa-floppy-disk',      label:'Lưu sản phẩm'},
  ],
  default: [
    {icon:'fa-solid fa-brain', label:'Đang suy nghĩ...'},
  ],
};
let lcTimer = null, lcStepTimer = null, lcStartTs = 0, lcStepIdx = 0, lcStepDefs = [];

function detectAction(text) {
  if (/tìm ảnh|find.?image/i.test(text)) return 'find_images';
  if (/verify|kiểm tra ảnh/i.test(text))  return 'verify';
  if (/báo cáo|report/i.test(text))        return 'report';
  if (/fill.?spec|thông số/i.test(text))   return 'fill_specs';
  if (/full.?auto/i.test(text))            return 'full_auto';
  return 'default';
}

function startLoadingCard(text) {
  lcStepDefs = ACTION_STEPS[detectAction(text)] || ACTION_STEPS.default;
  lcStepIdx = 0; lcStartTs = Date.now();
  const stepsEl = document.getElementById('lc-steps');
  stepsEl.innerHTML = '';
  lcStepDefs.forEach(function(s) {
    const row = document.createElement('div');
    row.className = 'lc-step';
    row.innerHTML = '<div class="step-dot"></div>'
      + '<i class="' + s.icon + '" style="width:14px;text-align:center;color:#333;font-size:.75rem;flex-shrink:0"></i>'
      + '<span>' + escHtml(s.label) + '</span>';
    stepsEl.appendChild(row);
  });
  document.getElementById('lc-product').textContent = '';
  document.getElementById('lc-timer').textContent = '00:00';
  setRing(0);
  document.getElementById('loading-card').style.display = 'block';
  clearInterval(lcTimer);
  lcTimer = setInterval(function() {
    const s = Math.floor((Date.now() - lcStartTs) / 1000);
    document.getElementById('lc-timer').textContent = String(Math.floor(s/60)).padStart(2,'0') + ':' + String(s%60).padStart(2,'0');
  }, 500);
  lcStepTick();
}

function lcStepTick() {
  const steps = document.getElementById('lc-steps').children;
  if (!steps.length) return;
  if (lcStepIdx > 0 && steps[lcStepIdx-1]) {
    const prev = steps[lcStepIdx-1];
    prev.className = 'lc-step done';
    const ic = prev.querySelectorAll('i')[0];
    if (ic) ic.style.color = '#4ade80';
  }
  if (lcStepIdx >= steps.length) { setRing(92); return; }
  const cur = steps[lcStepIdx];
  cur.className = 'lc-step active';
  const ic = cur.querySelectorAll('i')[0];
  if (ic) ic.style.color = 'var(--ai-red)';
  setRing(Math.round((lcStepIdx / steps.length) * 85));
  lcStepIdx++;
  lcStepTimer = setTimeout(lcStepTick, Math.max(900, Math.floor(4800 / lcStepDefs.length)));
}

function stopLoadingCard() {
  clearInterval(lcTimer); clearTimeout(lcStepTimer);
  setRing(100);
  Array.from(document.getElementById('lc-steps').children).forEach(function(s) {
    s.className = 'lc-step done';
    const ic = s.querySelectorAll('i')[0];
    if (ic) ic.style.color = '#4ade80';
  });
  setTimeout(function() { document.getElementById('loading-card').style.display = 'none'; }, 1500);
}

function setLcProduct(text) {
  const el = document.getElementById('lc-product');
  if (el) el.textContent = text ? ('→ ' + text) : '';
}

// ── Send message ──────────────────────────────────────────────────
async function sendMsg() {
  const text = inp.value.trim();
  if (!text || isLoading) return;

  appendUserMsg(text);
  inp.value = '';
  inp.style.height = 'auto';
  isLoading = true;
  document.getElementById('send-btn').disabled = true;
  startLoadingCard(text);

  let pollTimer = setInterval(async function() {
    try {
      const pr = await fetch(APIURL + '?progress=1');
      const pd = await pr.json();
      if (pd && pd.active) {
        if (pd.percent) setRing(pd.percent);
        if (pd.product) setLcProduct(pd.product);
        if (pd.step) termLog('info', '[PROG] ' + pd.step);
      }
    } catch(e) {}
  }, 2000);

  termLog('info', '[USER] ' + text.substring(0, 80));

  try {
    const res = await fetch(APIURL, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: text})
    });
    const data = await res.json();
    clearInterval(pollTimer);
    stopLoadingCard();

    if (data.thinking) termLog('info', '[THINK] ' + data.thinking);
    if (data.plan)     termLog('info', '[PLAN]  ' + data.plan);

    if (!data.ok && data.reply === undefined) {
      appendAIMsg('Lỗi kết nối. Thử lại sau.', '');
      termLog('err', '[ERR] Connection failed');
    } else if (data.requires_confirm) {
      showPendingCard(data.reply, data);
      showConfirmModal(data.reply);
      termLog('warn', '[CONFIRM] ' + (data.action||'action') + ' requires confirmation');
    } else {
      typeMsg(data.reply || 'Hoàn thành.', data.action);
      termLog('ok', '[AI] ' + (data.action||'chat') + ' — done');
    }
  } catch(e) {
    clearInterval(pollTimer);
    stopLoadingCard();
    appendAIMsg('Lỗi: ' + e.message, '');
    termLog('err', '[ERR] ' + e.message);
  }

  isLoading = false;
  document.getElementById('send-btn').disabled = false;
}

// ── Chat rendering ────────────────────────────────────────────────
function appendUserMsg(text) {
  const ts = Date.now();
  const el = document.createElement('div');
  el.className = 'msg user';
  el.dataset.ts = ts;
  el.innerHTML = copyBtn() + escHtml(text) + '<div class="msg-ts">' + relativeTime(ts) + '</div>';
  document.getElementById('chat-box').appendChild(el);
  scrollChat();
  chatMsgs.push({role:'user', text:text, ts:ts});
  saveHistory();
}

function copyBtn() {
  return '<button class="copy-btn" onclick="copyMsg(this)" title="Sao chép"><i class="fa-regular fa-copy"></i></button>';
}
function copyMsg(btn) {
  const msg = btn.closest('.msg');
  const span = msg.querySelector('span');
  const text = span ? span.innerText : msg.innerText.trim();
  navigator.clipboard.writeText(text).then(function() {
    const ic = btn.querySelector('i');
    ic.className = 'fa-solid fa-check';
    setTimeout(function(){ ic.className = 'fa-regular fa-copy'; }, 1500);
  });
}

function appendAIMsg(text, action) {
  const ts = Date.now();
  const el = document.createElement('div');
  el.className = 'msg ai';
  el.dataset.ts = ts;
  const icon = actionToIcon(action || '');
  const badge = action && action !== 'chat' ? '<span class="action-badge">' + escHtml(action) + '</span>' : '';
  el.innerHTML = '<div class="msg-label"><i class="' + icon + '" style="margin-right:.3rem"></i>' + badge + 'AI</div>'
               + '<span style="white-space:pre-wrap">' + escHtml(text) + '</span>'
               + '<div class="msg-ts">' + relativeTime(ts) + '</div>'
               + copyBtn();
  document.getElementById('chat-box').appendChild(el);
  scrollChat();
  chatMsgs.push({role:'ai', text:text, action:action||'', ts:ts});
  saveHistory();
  return el;
}

function typeMsg(text, action) {
  const ts = Date.now();
  const el = document.createElement('div');
  el.className = 'msg ai';
  el.dataset.ts = ts;
  const actionIcon = actionToIcon(action);
  const badge = action && action !== 'chat' ? '<span class="action-badge">' + escHtml(action) + '</span>' : '';
  el.innerHTML = '<div class="msg-label"><i class="' + actionIcon + '" style="margin-right:.3rem"></i>' + badge + 'AI · ' + escHtml(action||'chat') + '</div>'
               + '<span id="type-target" style="white-space:pre-wrap"></span>'
               + '<div class="msg-ts">' + relativeTime(ts) + '</div>'
               + copyBtn();
  document.getElementById('chat-box').appendChild(el);
  scrollChat();
  const target = el.querySelector('#type-target');
  let i = 0, accumulated = '';
  const chars = text.split('');
  const interval = setInterval(function() {
    if (i >= chars.length) {
      clearInterval(interval);
      chatMsgs.push({role:'ai', text:text, action:action||'', ts:ts});
      saveHistory();
      return;
    }
    accumulated += chars[i++];
    target.innerHTML = escHtml(accumulated).replace(/\n/g, '<br>');
    scrollChat();
  }, 10);
}

function scrollChat(instant) {
  const box = document.getElementById('chat-box');
  box.scrollTo({top: box.scrollHeight, behavior: instant ? 'instant' : 'smooth'});
}

// ── Ring ──────────────────────────────────────────────────────────
function setRing(pct) {
  const off = 138 - (pct / 100 * 138);
  const arc = document.getElementById('ring-arc');
  if (arc) arc.style.strokeDashoffset = off;
  const pctEl = document.getElementById('ring-pct');
  if (pctEl) pctEl.textContent = pct + '%';
  const bar = document.getElementById('prog-bar');
  if (bar) bar.style.width = pct + '%';
}

// ── Confirmation ──────────────────────────────────────────────────
function showPendingCard(desc, data) {
  pendingAction = data;
  document.getElementById('pending-desc').textContent = desc;
  document.getElementById('pending-card').style.display = 'block';
}

function showConfirmModal(text) {
  document.getElementById('modal-text').textContent = text;
  document.getElementById('confirm-modal').classList.add('open');
  startModalTimer();
}

function startModalTimer() {
  modalSecs = 30;
  clearInterval(modalTimer);
  modalTimer = setInterval(function() {
    modalSecs--;
    document.getElementById('modal-countdown').textContent = modalSecs;
    document.getElementById('modal-secs').textContent = modalSecs;
    if (modalSecs <= 0) { clearInterval(modalTimer); cancelModal(); }
  }, 1000);
}

function confirmAction() {
  clearInterval(modalTimer);
  document.getElementById('confirm-modal').classList.remove('open');
  document.getElementById('pending-card').style.display = 'none';
  inp.value = 'xác nhận';
  sendMsg();
}

function cancelModal() {
  clearInterval(modalTimer);
  document.getElementById('confirm-modal').classList.remove('open');
  document.getElementById('pending-card').style.display = 'none';
  inp.value = 'hủy';
  sendMsg();
}

function sendConfirm() { confirmAction(); }
function sendCancel()  { cancelModal(); }

// ── Terminal ──────────────────────────────────────────────────────
function termLog(type, text) {
  const term = document.getElementById('terminal');
  const line = document.createElement('div');
  line.className = 'term-line ' + type;
  line.textContent = '[' + new Date().toLocaleTimeString('vi-VN') + '] ' + text;
  const cursor = term.querySelector('.term-cursor')?.parentElement;
  if (cursor) term.insertBefore(line, cursor);
  else term.appendChild(line);
  term.scrollTop = term.scrollHeight;
}

// ── Helpers ───────────────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function relativeTime(ts) {
  if (!ts) return '';
  const diff = Math.floor((Date.now() - ts) / 1000);
  if (diff < 10)  return 'vừa xong';
  if (diff < 60)  return diff + 's trước';
  const m = Math.floor(diff / 60);
  if (m < 60)     return m + ' phút trước';
  const h = Math.floor(m / 60);
  if (h < 24)     return h + ' giờ trước';
  return Math.floor(h / 24) + ' ngày trước';
}

function actionToIcon(action) {
  const map = {
    report:         'fa-solid fa-chart-line',
    find_images:    'fa-solid fa-images',
    remove_bg:      'fa-solid fa-eraser',
    fill_specs:     'fa-solid fa-microchip',
    create_product: 'fa-solid fa-plus-circle',
    update_product: 'fa-solid fa-pen',
    delete_product: 'fa-solid fa-trash',
    search:         'fa-solid fa-magnifying-glass',
    full_auto:      'fa-solid fa-wand-magic-sparkles',
    bulk:           'fa-solid fa-layer-group',
    chat:           'fa-solid fa-robot',
  };
  return map[action] || 'fa-solid fa-robot';
}

loadHistory();

// ── Preserve chat scroll position across navigation ───────────────
(function() {
  const box = document.getElementById('chat-box');
  // Save scroll on every scroll event (debounced)
  let _st;
  box.addEventListener('scroll', function() {
    clearTimeout(_st);
    _st = setTimeout(function() {
      const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
      if (atBottom) {
        sessionStorage.removeItem('ai_chat_scroll');
      } else {
        sessionStorage.setItem('ai_chat_scroll', box.scrollTop);
      }
    }, 150);
  });
  // Save before navigating away
  window.addEventListener('pagehide', function() {
    const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
    if (atBottom) sessionStorage.removeItem('ai_chat_scroll');
    else sessionStorage.setItem('ai_chat_scroll', box.scrollTop);
  });
})();

// ── Live stat refresh ─────────────────────────────────────────────
setInterval(async function() {
  try {
    const res = await fetch('<?= APP_URL ?>/admin/api/new-orders-count?since=' + Math.floor(Date.now()/1000 - 300));
    const d   = await res.json();
    if (d && d.success !== undefined) {
      document.getElementById('s-pending').textContent = d.count || '<?= $pendingOrders ?>';
    }
  } catch(e) {}
}, 30000);
</script>

<?php include __DIR__ . '/layout_bottom.php'; ?>
