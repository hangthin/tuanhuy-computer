<?php $pageTitle='Đăng nhập'; require_once __DIR__.'/../layouts/header.php'; ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:#f5f5f5">
  <div style="width:100%;max-width:420px">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="display:inline-flex;align-items:center;gap:.65rem">
        <div style="width:46px;height:46px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.2rem">TH</div>
        <div><div style="font-weight:800;font-size:1rem;color:#111">TUẤN HUY COMPUTER</div><div style="font-size:.62rem;color:var(--red);letter-spacing:2px;font-weight:700">ĐĂNG NHẬP</div></div>
      </div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.08);animation:fadeIn .4s">
      <h2 style="font-size:1.15rem;font-weight:800;color:#111;margin-bottom:1.25rem;text-align:center">Chào mừng trở lại! 👋</h2>
      <?php if($error): ?>
      <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;color:#991b1b">❌ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div style="margin-bottom:.9rem">
          <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars(isset($_POST['email'])?$_POST['email']:'') ?>" required placeholder="your@email.com" class="form-input">
        </div>
        <div style="margin-bottom:1.1rem;position:relative">
          <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Mật khẩu</label>
          <input type="password" id="pw" name="password" required placeholder="••••••••" class="form-input" style="padding-right:3rem">
          <button type="button" onclick="var i=document.getElementById('pw');i.type=i.type==='password'?'text':'password'" style="position:absolute;right:.75rem;bottom:.6rem;background:none;border:none;cursor:pointer;color:#888"><i class="fa-solid fa-eye"></i></button>
        </div>
        <button type="submit" class="btn-red" style="width:100%;padding:.65rem;font-size:.95rem">Đăng nhập</button>
      </form>
      <div style="text-align:center;margin-top:.9rem;font-size:.82rem;color:#999">
        Chưa có tài khoản? <a href="<?= APP_URL ?>/auth/register" style="color:var(--red);font-weight:600;text-decoration:none">Đăng ký ngay</a>
      </div>
      <div style="background:#f8f8f8;border-radius:7px;padding:.65rem;margin-top:.9rem;font-size:.75rem;color:#666;text-align:center;border:1px dashed #ddd">
        <strong>Demo Admin:</strong> admin@tuanhuycmp.vn / Admin@123<br>
        <strong>Khách hàng:</strong> an@gmail.com / 123456
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
