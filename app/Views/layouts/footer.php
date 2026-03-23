<footer style="background:#0d0d0d;margin-top:0">
  <div style="background:#0a0a0a;border-top:1px solid #181818;padding:2.5rem 1rem 2rem">
    <div style="max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1.4fr 1fr 1fr 1.2fr;gap:2rem">
      <div>
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:1rem">
          <div style="width:38px;height:38px;background:var(--red,#E30000);border-radius:8px;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-microchip" style="color:#fff;font-size:.85rem"></i></div>
          <div><div style="color:#fff;font-weight:900;font-size:.88rem">TUẤN HUY COMPUTER</div><div style="color:#333;font-size:.58rem;letter-spacing:2px">CÔNG NGHỆ ĐỈNH CAO</div></div>
        </div>
        <p style="color:#444;font-size:.79rem;line-height:1.7;margin-bottom:1rem">Chuyên PC, Laptop, linh kiện máy tính chính hãng. Cam kết giá tốt nhất TP.HCM.</p>
        <div style="display:flex;gap:.4rem">
          <?php foreach(array(array('fa-facebook-f','#1877f2'),array('fa-youtube','#ff0000'),array('fa-tiktok','#fff'),array('fa-telegram','#0088cc')) as $s): ?>
          <a href="#" style="width:32px;height:32px;background:#161616;border:1px solid #1e1e1e;border-radius:7px;display:flex;align-items:center;justify-content:center;color:<?= $s[1] ?>;font-size:.78rem;transition:.18s" onmouseover="this.style.background='<?= $s[1] ?>20'" onmouseout="this.style.background='#161616'">
            <i class="fa-brands <?= $s[0] ?>"></i>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <div style="color:#fff;font-weight:700;font-size:.79rem;margin-bottom:.8rem;text-transform:uppercase;letter-spacing:.5px">Danh mục</div>
        <?php if(!isset($allCategories)) $allCategories=(new CategoryModel())->getAll(); ?>
        <?php foreach($allCategories as $cat): ?>
        <a href="<?= APP_URL ?>/products/<?= $cat['slug'] ?>" style="display:flex;align-items:center;gap:.4rem;color:#444;font-size:.78rem;padding:.18rem 0;transition:color .18s" onmouseover="this.style.color='var(--red,#E30000)'" onmouseout="this.style.color='#444'">
          <i class="fa-solid fa-angle-right" style="font-size:.58rem;opacity:.5"></i><?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <div>
        <div style="color:#fff;font-weight:700;font-size:.79rem;margin-bottom:.8rem;text-transform:uppercase;letter-spacing:.5px">Hỗ trợ</div>
        <?php foreach(array('Chính sách bảo hành','Chính sách đổi trả','Hướng dẫn mua hàng','Hướng dẫn thanh toán','Chính sách vận chuyển','Câu hỏi thường gặp') as $s): ?>
        <a href="#" style="display:flex;align-items:center;gap:.4rem;color:#444;font-size:.78rem;padding:.18rem 0;transition:color .18s" onmouseover="this.style.color='var(--red,#E30000)'" onmouseout="this.style.color='#444'">
          <i class="fa-solid fa-angle-right" style="font-size:.58rem;opacity:.5"></i><?= $s ?>
        </a>
        <?php endforeach; ?>
      </div>
      <div>
        <div style="color:#fff;font-weight:700;font-size:.79rem;margin-bottom:.8rem;text-transform:uppercase;letter-spacing:.5px">Liên hệ</div>
        <?php foreach(array(array('fa-location-dot','123 Nguyễn Văn Cừ, Q.5, TP.HCM'),array('fa-phone','0909 999 888'),array('fa-envelope','info@tuanhuycmp.vn'),array('fa-clock','T2–CN: 8:00 – 21:00')) as $c): ?>
        <div style="display:flex;gap:.55rem;margin-bottom:.5rem;font-size:.79rem;color:#444">
          <i class="fa-solid <?= $c[0] ?>" style="color:var(--red,#E30000);flex-shrink:0;margin-top:2px;font-size:.75rem;width:14px;text-align:center"></i>
          <span><?= $c[1] ?></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:.8rem">
          <div style="color:#333;font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.45rem">Thanh toán</div>
          <div style="display:flex;gap:.3rem;flex-wrap:wrap">
            <?php foreach(array(array('fa-money-bill-wave','COD'),array('fa-building-columns','Bank'),array('fa-mobile-screen','MoMo'),array('fa-credit-card','VNPay')) as $pm): ?>
            <span style="background:#161616;border:1px solid #1e1e1e;color:#444;padding:3px 7px;border-radius:5px;font-size:.67rem;display:flex;align-items:center;gap:.28rem">
              <i class="fa-solid <?= $pm[0] ?>" style="color:#333;font-size:.6rem"></i><?= $pm[1] ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div style="background:#080808;border-top:1px solid #141414;padding:.7rem 1rem;text-align:center">
    <span style="color:#2a2a2a;font-size:.7rem">© <?= date('Y') ?> <span style="color:var(--red,#E30000)">Tuấn Huy Computer</span>. All rights reserved.</span>
  </div>
</footer>
<script>
window.addEventListener('load',function(){var l=document.getElementById('pg-ld');if(l){l.classList.add('out');setTimeout(function(){l.remove()},500);}});
(function(){var els=document.querySelectorAll('.reveal');if(!els.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)x.target.classList.add('visible');});},{threshold:.08});els.forEach(function(el){o.observe(el);});})();
</script>
</body>
</html>
