<?php $pageTitle='Tài khoản'; require_once __DIR__.'/../layouts/header.php'; ?>
<?php /* Flash already consumed and rendered by header.php */ ?>

<style>
.acc-wrap{max-width:960px;margin:1.5rem auto;padding:0 1rem;display:grid;grid-template-columns:220px 1fr;gap:1.25rem;align-items:start}
.acc-side{background:#fff;border-radius:14px;overflow:hidden;box-shadow:var(--shadow);position:sticky;top:80px}
.acc-side-top{background:linear-gradient(135deg,var(--red),#b00000);padding:1.5rem 1rem 1.25rem;text-align:center}
.acc-avatar{width:64px;height:64px;background:rgba(255,255,255,.22);border:3px solid rgba(255,255,255,.35);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.5rem;margin:0 auto .65rem}
.acc-side-name{color:#fff;font-weight:800;font-size:.92rem;margin-bottom:.15rem}
.acc-side-email{color:rgba(255,255,255,.7);font-size:.7rem;word-break:break-all}
.acc-nav{padding:.5rem 0}
.acc-nav a{display:flex;align-items:center;gap:.65rem;padding:.6rem 1.1rem;color:#555;font-size:.83rem;transition:all var(--t);border-left:3px solid transparent}
.acc-nav a:hover{color:var(--text);background:#f9f9f9;border-left-color:#ddd}
.acc-nav a.active{color:var(--red);background:rgba(227,0,0,.05);border-left-color:var(--red);font-weight:600}
.acc-nav a i{width:16px;text-align:center;font-size:.82rem;opacity:.7}
.acc-nav a.active i{opacity:1}
.acc-nav .nav-sep{height:1px;background:#f0f0f0;margin:.35rem .8rem}
.acc-card{background:#fff;border-radius:14px;padding:1.5rem;box-shadow:var(--shadow)}
.acc-card-title{font-weight:800;font-size:1rem;color:var(--text);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem}
.acc-card-title i{color:var(--red);font-size:.9rem}
.field-group{display:grid;grid-template-columns:1fr 1fr;gap:.75rem .9rem;margin-bottom:.75rem}
.field-group.full{grid-template-columns:1fr}
.field-wrap label{display:block;font-size:.74rem;font-weight:600;color:#666;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.3px}
.field-wrap input,.field-wrap select{width:100%;padding:.52rem .8rem;border:1.5px solid var(--border);border-radius:9px;font-family:var(--font);font-size:.875rem;color:var(--text);outline:none;transition:border-color var(--t),box-shadow var(--t)}
.field-wrap input:focus,.field-wrap select:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,0,0,.08)}
.field-wrap input[readonly]{background:#f9f9f9;color:#999;cursor:not-allowed}
.info-row{display:flex;align-items:center;gap:.5rem;padding:.55rem 0;border-bottom:1px solid #f5f5f5;font-size:.84rem}
.info-row:last-child{border-bottom:none}
.info-row i{width:20px;text-align:center;color:var(--red);font-size:.82rem;flex-shrink:0}
.info-row .lbl{color:#999;font-size:.75rem;min-width:90px}
.info-row .val{color:var(--text);font-weight:500}
.stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1.25rem}
.stat-box{background:linear-gradient(135deg,#f8f9fa,#fff);border:1px solid #eee;border-radius:10px;padding:.9rem;text-align:center}
.stat-box .num{font-size:1.4rem;font-weight:900;color:var(--red);line-height:1}
.stat-box .lbl{font-size:.68rem;color:#999;margin-top:.25rem;text-transform:uppercase;letter-spacing:.4px}
.save-btn{background:var(--red);color:#fff;border:none;padding:.6rem 1.6rem;border-radius:9px;font-weight:700;font-size:.88rem;cursor:pointer;font-family:var(--font);transition:all var(--t);display:inline-flex;align-items:center;gap:.4rem}
.save-btn:hover{background:var(--red-dk);transform:translateY(-1px);box-shadow:0 4px 14px rgba(227,0,0,.3)}
.flash-acc{display:flex;align-items:center;gap:.55rem;padding:.65rem .9rem;border-radius:9px;font-size:.84rem;margin-bottom:1rem}
.flash-acc.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.flash-acc.error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
@media(max-width:680px){
  .acc-wrap{grid-template-columns:1fr}
  .acc-side{position:static}
  .field-group{grid-template-columns:1fr}
  .stat-grid{grid-template-columns:1fr 1fr}
}
</style>

<div class="acc-wrap">
  <!-- Sidebar -->
  <aside class="acc-side">
    <div class="acc-side-top">
      <div class="acc-avatar"><?= strtoupper(mb_substr($user['fullname']??'U',0,1,'UTF-8')) ?></div>
      <div class="acc-side-name"><?= htmlspecialchars($user['fullname']??'') ?></div>
      <div class="acc-side-email"><?= htmlspecialchars($user['email']??'') ?></div>
    </div>
    <nav class="acc-nav">
      <a href="<?= APP_URL ?>/account" class="active"><i class="fa-solid fa-user"></i>Thông tin cá nhân</a>
      <a href="<?= APP_URL ?>/account/orders"><i class="fa-solid fa-box-open"></i>Đơn hàng của tôi</a>
      <div class="nav-sep"></div>
      <a href="<?= APP_URL ?>/auth/logout" style="color:#ef4444" onclick="return confirm('Đăng xuất?')"><i class="fa-solid fa-arrow-right-from-bracket"></i>Đăng xuất</a>
    </nav>
  </aside>

  <!-- Main -->
  <div style="display:flex;flex-direction:column;gap:1rem">

    <!-- Thống kê nhanh -->
    <?php
    $db = Database::getInstance();
    $oStats = $db->fetch("SELECT COUNT(*) as total, COALESCE(SUM(total),0) as spent FROM orders WHERE user_id=? AND status!='cancelled'", array($_SESSION['user_id']));
    $oDeliv = $db->fetch("SELECT COUNT(*) as cnt FROM orders WHERE user_id=? AND status='delivered'", array($_SESSION['user_id']));
    ?>
    <div class="acc-card" style="padding:1.1rem 1.5rem">
      <div class="stat-grid">
        <div class="stat-box">
          <div class="num"><?= (int)($oStats['total']??0) ?></div>
          <div class="lbl">Tổng đơn</div>
        </div>
        <div class="stat-box">
          <div class="num"><?= (int)($oDeliv['cnt']??0) ?></div>
          <div class="lbl">Đã giao</div>
        </div>
        <div class="stat-box">
          <div class="num" style="font-size:1rem"><?= $oStats['spent']>0 ? number_format($oStats['spent']/1000000,1).'M' : '0' ?></div>
          <div class="lbl">Đã chi (đ)</div>
        </div>
      </div>

      <!-- Thông tin tài khoản -->
      <div style="display:flex;flex-direction:column">
        <div class="info-row"><i class="fa-solid fa-calendar-plus"></i><span class="lbl">Tham gia</span><span class="val"><?= date('d/m/Y', strtotime($user['created_at']??'now')) ?></span></div>
        <?php if(!empty($user['last_login'])): ?>
        <div class="info-row"><i class="fa-solid fa-clock"></i><span class="lbl">Đăng nhập</span><span class="val"><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></span></div>
        <?php endif; ?>
        <div class="info-row"><i class="fa-solid fa-shield-halved"></i><span class="lbl">Loại TK</span>
          <span class="val"><?= ($user['role']??0)==1 ? '<span style="background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:5px;font-size:.72rem;font-weight:700">Admin</span>' : '<span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:5px;font-size:.72rem;font-weight:700">Khách hàng</span>' ?></span>
        </div>
      </div>
    </div>

    <!-- Form chỉnh sửa -->
    <div class="acc-card">
      <div class="acc-card-title"><i class="fa-solid fa-pen-to-square"></i>Chỉnh sửa thông tin</div>
      <form method="POST" action="<?= APP_URL ?>/account/update">
        <div class="field-group">
          <div class="field-wrap">
            <label>Họ và tên *</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']??'') ?>" required placeholder="Nguyễn Văn A">
          </div>
          <div class="field-wrap">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']??'') ?>" readonly>
          </div>
        </div>
        <div class="field-group">
          <div class="field-wrap">
            <label>Số điện thoại</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="0909 xxx xxx">
          </div>
          <div class="field-wrap">
            <label>Tỉnh / Thành phố</label>
            <input type="text" name="city" value="<?= htmlspecialchars($user['city']??'') ?>" placeholder="TP. Hồ Chí Minh">
          </div>
        </div>
        <div class="field-group">
          <div class="field-wrap">
            <label>Quận / Huyện</label>
            <input type="text" name="district" value="<?= htmlspecialchars($user['district']??'') ?>" placeholder="Quận 5">
          </div>
          <div class="field-wrap">
            <label>Địa chỉ</label>
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']??'') ?>" placeholder="123 Đường ABC">
          </div>
        </div>
        <div style="margin-top:.5rem">
          <button type="submit" class="save-btn"><i class="fa-solid fa-floppy-disk"></i>Lưu thay đổi</button>
        </div>
      </form>
    </div>

    <!-- Đổi mật khẩu -->
    <div class="acc-card">
      <div class="acc-card-title"><i class="fa-solid fa-lock"></i>Đổi mật khẩu</div>
      <form method="POST" action="<?= APP_URL ?>/account/change-password" onsubmit="return accValidatePw(this)">
        <div class="field-group">
          <div class="field-wrap">
            <label>Mật khẩu hiện tại</label>
            <input type="password" name="current_password" placeholder="••••••••" autocomplete="current-password">
          </div>
          <div class="field-wrap" style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
            <div>
              <label>Mật khẩu mới</label>
              <input type="password" name="new_password" id="acc-pw-new" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
            </div>
            <div>
              <label>Nhập lại mật khẩu mới</label>
              <input type="password" name="confirm_password" id="acc-pw-cf" placeholder="Nhập lại" autocomplete="new-password">
            </div>
          </div>
        </div>
        <div id="acc-pw-err" style="display:none;color:#ef4444;font-size:.78rem;margin-bottom:.5rem"></div>
        <button type="submit" class="save-btn" style="background:#1d4ed8"><i class="fa-solid fa-key"></i>Đổi mật khẩu</button>
      </form>
    </div>

  </div>
</div>

<script>
function accValidatePw(f){
  var n=document.getElementById('acc-pw-new').value;
  var c=document.getElementById('acc-pw-cf').value;
  var err=document.getElementById('acc-pw-err');
  if(n.length>0&&n.length<6){err.textContent='Mật khẩu mới ít nhất 6 ký tự';err.style.display='block';return false;}
  if(n!==c){err.textContent='Mật khẩu nhập lại không khớp';err.style.display='block';return false;}
  err.style.display='none';return true;
}
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
