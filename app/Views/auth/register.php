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
      <h2 style="font-size:1.15rem;font-weight:800;color:#111;margin-bottom:1.25rem;text-align:center">Tạo tài khoản mới</h2>
      <?php if(!empty($error)): ?>
      <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;color:#991b1b"><?= htmlspecialchars($error) ?></div>
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
      <?php if(GOOGLE_CLIENT_ID): ?>
      <div style="display:flex;align-items:center;gap:.6rem;margin:.9rem 0 .75rem">
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
        <span style="font-size:.75rem;color:#aaa;white-space:nowrap">hoặc đăng ký nhanh với</span>
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
      </div>
      <a href="<?= APP_URL ?>/auth/google-login" style="display:flex;align-items:center;justify-content:center;gap:.6rem;width:100%;padding:.6rem;border:1.5px solid #dadce0;border-radius:8px;background:#fff;text-decoration:none;font-size:.88rem;font-weight:600;color:#3c4043;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.12)'" onmouseout="this.style.boxShadow='none'">
        <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.08 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.96 2.31-8.16 2.31-6.26 0-11.57-3.59-13.46-8.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
        Đăng ký bằng Google
      </a>
      <?php endif; ?>
      <div style="text-align:center;margin-top:.9rem;font-size:.82rem;color:#999">
        Đã có tài khoản? <a href="<?= APP_URL ?>/auth/login" style="color:var(--red);font-weight:600;text-decoration:none">Đăng nhập</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
