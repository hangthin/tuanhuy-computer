<?php $pageTitle='Đăng ký tài khoản'; require_once __DIR__.'/../layouts/header.php'; ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:#f5f5f5">
  <div style="width:100%;max-width:460px">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="display:inline-flex;align-items:center;gap:.65rem">
        <div style="width:46px;height:46px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.2rem">TH</div>
        <div><div style="font-weight:800;font-size:1rem;color:#111">TUẤN HUY COMPUTER</div><div style="font-size:.62rem;color:var(--red);letter-spacing:2px;font-weight:700">TẠO TÀI KHOẢN</div></div>
      </div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.08);animation:fadeIn .4s">
      <h2 style="font-size:1.15rem;font-weight:800;color:#111;margin-bottom:1.25rem;text-align:center">Tạo tài khoản mới 🚀</h2>
      <?php if(!empty($error)): ?>
      <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;color:#991b1b">❌ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:.75rem">
          <div>
            <label style="display:block;font-weight:600;font-size:.8rem;color:#333;margin-bottom:.25rem">Họ và tên *</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars(isset($old['fullname'])?$old['fullname']:'') ?>" required class="form-input" style="font-size:.82rem">
          </div>
          <div>
            <label style="display:block;font-weight:600;font-size:.8rem;color:#333;margin-bottom:.25rem">Số điện thoại</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars(isset($old['phone'])?$old['phone']:'') ?>" class="form-input" style="font-size:.82rem">
          </div>
        </div>
        <div style="margin-bottom:.75rem">
          <label style="display:block;font-weight:600;font-size:.8rem;color:#333;margin-bottom:.25rem">Email *</label>
          <input type="email" name="email" value="<?= htmlspecialchars(isset($old['email'])?$old['email']:'') ?>" required class="form-input">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:1.1rem">
          <div>
            <label style="display:block;font-weight:600;font-size:.8rem;color:#333;margin-bottom:.25rem">Mật khẩu *</label>
            <input type="password" name="password" required placeholder="Tối thiểu 6 ký tự" class="form-input" style="font-size:.82rem">
          </div>
          <div>
            <label style="display:block;font-weight:600;font-size:.8rem;color:#333;margin-bottom:.25rem">Xác nhận *</label>
            <input type="password" name="confirm_password" required class="form-input" style="font-size:.82rem">
          </div>
        </div>
        <div style="display:flex;align-items:flex-start;gap:.45rem;margin-bottom:1.1rem;font-size:.8rem;color:#555">
          <input type="checkbox" required style="margin-top:2px;accent-color:var(--red)">
          <span>Tôi đồng ý với <a href="#" style="color:var(--red)">Điều khoản</a> và <a href="#" style="color:var(--red)">Chính sách bảo mật</a></span>
        </div>
        <button type="submit" class="btn-red" style="width:100%;padding:.65rem;font-size:.95rem">Tạo tài khoản</button>
      </form>
      <div style="text-align:center;margin-top:.9rem;font-size:.82rem;color:#999">
        Đã có tài khoản? <a href="<?= APP_URL ?>/auth/login" style="color:var(--red);font-weight:600;text-decoration:none">Đăng nhập</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
