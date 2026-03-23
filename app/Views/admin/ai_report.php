<?php require_once __DIR__.'/layout_top.php'; ?>

<style>
.report-body{white-space:pre-wrap;line-height:1.85;font-size:.875rem;color:#ccc;font-family:'Be Vietnam Pro',sans-serif}
.report-body strong,.report-body b{color:#fff}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.gen-pulse{animation:pulse 1.5s infinite}
</style>

<div style="max-width:820px">

  <!-- Header row -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.65rem;margin-bottom:1.1rem">
    <div>
      <h2 style="color:#fff;font-size:1rem;font-weight:800;margin:0;display:flex;align-items:center;gap:.45rem">
        <i class="fa-solid fa-brain" style="color:var(--red)"></i> Báo cáo AI Admin
      </h2>
      <p style="color:#444;font-size:.72rem;margin:.2rem 0 0">Phân tích nhật ký hoạt động bằng Groq AI · Chỉ Admin</p>
    </div>

    <!-- Generate form -->
    <form method="POST" id="gen-form" style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
      <select name="hours" class="form-inp" style="font-size:.78rem;padding:.3rem .6rem;width:auto">
        <option value="6">6 giờ qua</option>
        <option value="24" selected>24 giờ qua</option>
        <option value="48">48 giờ qua</option>
        <option value="72">3 ngày qua</option>
        <option value="168">7 ngày qua</option>
      </select>
      <?php if($tgReady): ?>
      <label style="display:flex;align-items:center;gap:.3rem;font-size:.75rem;color:#777;cursor:pointer">
        <input type="checkbox" name="send_telegram" value="1" style="accent-color:#229ED9"> <i class="fa-brands fa-telegram" style="color:#229ED9"></i> Telegram
      </label>
      <?php endif; ?>
      <?php if($zaloReady): ?>
      <label style="display:flex;align-items:center;gap:.3rem;font-size:.75rem;color:#777;cursor:pointer">
        <input type="checkbox" name="send_zalo" value="1" style="accent-color:var(--red)"> Gửi Zalo
      </label>
      <?php endif; ?>
      <button type="submit" class="btn-r" id="gen-btn" style="display:flex;align-items:center;gap:.35rem">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Tạo báo cáo
      </button>
    </form>
  </div>

  <!-- Notification not configured notice -->
  <?php if(!$tgReady && !$zaloReady): ?>
  <div style="background:rgba(34,158,217,.07);border:1px solid rgba(34,158,217,.2);border-radius:9px;padding:.6rem .9rem;font-size:.75rem;color:#38bdf8;display:flex;align-items:center;gap:.5rem;margin-bottom:.85rem">
    <i class="fa-brands fa-telegram"></i>
    Telegram chưa cấu hình. Thêm <code style="background:#111;padding:1px 5px;border-radius:3px">TELEGRAM_BOT_TOKEN</code>
    và <code style="background:#111;padding:1px 5px;border-radius:3px">TELEGRAM_ADMIN_CHAT</code> vào <code style="background:#111;padding:1px 5px;border-radius:3px">config/app.php</code> để bật gửi thông báo.
  </div>
  <?php endif; ?>

  <!-- Report card -->
  <div class="card" style="padding:1.25rem">

    <?php if($cached): ?>

    <!-- Report meta -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
      <div style="display:flex;align-items:center;gap:.55rem">
        <div style="width:36px;height:36px;background:rgba(227,0,0,.12);border-radius:8px;display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-robot" style="color:var(--red);font-size:.9rem"></i>
        </div>
        <div>
          <div style="font-size:.82rem;font-weight:700;color:#fff">Phân tích Groq AI</div>
          <div style="font-size:.68rem;color:#555">
            Tạo lúc <?= date('H:i d/m/Y', strtotime($cached['generated_at'])) ?>
            · <?= $cached['hours_back'] ?> giờ dữ liệu
            <?php
            $age = time() - strtotime($cached['generated_at']);
            if($age < 3600) echo ' · <span style="color:#4ade80">Mới nhất</span>';
            elseif($age < 21600) echo ' · <span style="color:#fbbf24">'.round($age/3600,1).' giờ trước</span>';
            else echo ' · <span style="color:#f87171">Đã cũ — nên tạo lại</span>';
            ?>
          </div>
        </div>
      </div>
      <?php if($tgReady): ?>
      <form method="POST" style="margin:0">
        <input type="hidden" name="hours" value="<?= (int)$cached['hours_back'] ?>">
        <input type="hidden" name="send_telegram" value="1">
        <button type="submit" class="btn-g" style="font-size:.75rem;padding:.3rem .75rem;border-color:rgba(34,158,217,.35);color:#38bdf8;display:flex;align-items:center;gap:.3rem">
          <i class="fa-brands fa-telegram"></i> Gửi Telegram
        </button>
      </form>
      <?php endif; ?>
      <?php if($zaloReady): ?>
      <form method="POST" style="margin:0">
        <input type="hidden" name="hours" value="<?= (int)$cached['hours_back'] ?>">
        <input type="hidden" name="send_zalo" value="1">
        <button type="submit" class="btn-g" style="font-size:.75rem;padding:.3rem .75rem;border-color:rgba(34,197,94,.35);color:#4ade80;display:flex;align-items:center;gap:.3rem">
          <i class="fas fa-paper-plane"></i> Gửi Zalo
        </button>
      </form>
      <?php endif; ?>
    </div>

    <div style="border-top:1px solid #1e1e1e;padding-top:1rem">
      <div class="report-body"><?= nl2br(htmlspecialchars($cached['text'])) ?></div>
    </div>

    <?php else: ?>

    <!-- Empty state -->
    <div style="text-align:center;padding:3rem 1rem">
      <div style="width:72px;height:72px;background:rgba(227,0,0,.08);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
        <i class="fa-solid fa-brain" style="font-size:2rem;color:rgba(227,0,0,.4)"></i>
      </div>
      <div style="font-weight:700;font-size:.95rem;color:#fff;margin-bottom:.4rem">Chưa có báo cáo</div>
      <div style="color:#555;font-size:.82rem;margin-bottom:1.5rem">Nhấn <strong style="color:#ddd">Tạo báo cáo</strong> để AI phân tích nhật ký hoạt động</div>
      <button onclick="document.getElementById('gen-btn').click()" class="btn-r" style="display:inline-flex;align-items:center;gap:.4rem">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Tạo ngay
      </button>
    </div>

    <?php endif; ?>

  </div>

  <!-- How it works -->
  <div style="margin-top:.85rem;display:grid;grid-template-columns:repeat(3,1fr);gap:.55rem">
    <?php foreach(array(
      array('fa-database','Dữ liệu','Đọc action_logs 24h gần nhất: ai thay đổi gì, lỗi nào, đăng nhập từ đâu'),
      array('fa-robot','AI phân tích','Groq AI (llama-3.3-70b) xử lý và viết báo cáo tiếng Việt'),
      array('fa-bell','Cảnh báo','Tự khóa tài khoản Staff nếu truy cập trái phép > 3 lần/30 phút'),
    ) as $h): ?>
    <div style="background:#111;border:1px solid #1e1e1e;border-radius:9px;padding:.8rem">
      <i class="fa-solid <?= $h[0] ?>" style="color:var(--red);margin-bottom:.45rem;display:block"></i>
      <div style="font-size:.78rem;font-weight:700;color:#ddd;margin-bottom:.2rem"><?= $h[1] ?></div>
      <div style="font-size:.7rem;color:#555;line-height:1.5"><?= $h[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
// "Tạo báo cáo" loading state
document.getElementById('gen-form').addEventListener('submit', function(){
  var btn = document.getElementById('gen-btn');
  btn.innerHTML = '<i class="fa-solid fa-spinner gen-pulse"></i> Đang tạo...';
  btn.disabled = true;
});

// Tất cả form có nút gửi Telegram / Zalo
document.querySelectorAll('form').forEach(function(form){
  form.addEventListener('submit', function(){
    var tgInput = form.querySelector('input[name="send_telegram"]');
    var zlInput = form.querySelector('input[name="send_zalo"]');
    if(tgInput && tgInput.value === '1'){
      var btn = form.querySelector('button[type="submit"]');
      if(btn){ btn.innerHTML = '<i class="fa-solid fa-spinner gen-pulse"></i> Đang gửi...'; btn.disabled = true; }
    }
    if(zlInput && zlInput.value === '1'){
      var btn = form.querySelector('button[type="submit"]');
      if(btn){ btn.innerHTML = '<i class="fa-solid fa-spinner gen-pulse"></i> Đang gửi...'; btn.disabled = true; }
    }
  });
});
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
