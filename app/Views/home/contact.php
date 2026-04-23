<?php include __DIR__.'/../layouts/header.php'; ?>

<style>
.contact-hero{background:linear-gradient(135deg,#0d0d0d 0%,#1a0000 60%,#0d0d0d 100%);padding:3.5rem 1rem;text-align:center;position:relative;overflow:hidden}
.contact-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 50% 50%,rgba(227,0,0,.12) 0%,transparent 70%);pointer-events:none}
.contact-hero h1{font-size:clamp(1.8rem,4vw,2.6rem);font-weight:900;color:#fff;font-family:var(--font-head)}
.contact-hero h1 em{color:var(--red);font-style:normal}
.contact-hero p{color:#888;font-size:.9rem;max-width:500px;margin:.65rem auto 0}

.contact-wrap{max-width:1100px;margin:0 auto;padding:3rem 1rem}

.contact-grid{display:grid;grid-template-columns:1fr 1.4fr;gap:2rem;align-items:start;margin-bottom:2.5rem}

.info-cards{display:flex;flex-direction:column;gap:1rem}
.info-card{background:#fff;border-radius:var(--r-lg);padding:1.2rem 1.4rem;border:1px solid var(--border);display:flex;align-items:flex-start;gap:1rem;transition:all var(--t)}
.info-card:hover{box-shadow:var(--shadow-md);transform:translateX(3px)}
.info-icon{width:42px;height:42px;border-radius:10px;background:rgba(227,0,0,.08);display:flex;align-items:center;justify-content:center;font-size:1.05rem;color:var(--red);flex-shrink:0}
.info-lbl{font-size:.7rem;font-weight:700;color:var(--gray-400);letter-spacing:.5px;text-transform:uppercase;margin-bottom:.2rem}
.info-val{font-size:.88rem;font-weight:600;color:var(--black);line-height:1.5}
.info-sub{font-size:.75rem;color:var(--gray-600);margin-top:.15rem}

.form-card{background:#fff;border-radius:var(--r-lg);padding:2rem;border:1px solid var(--border);box-shadow:var(--shadow)}
.form-card h2{font-size:1.1rem;font-weight:800;color:var(--black);margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem}
.form-group{margin-bottom:.85rem}
.form-label{display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.3rem}
.form-select{width:100%;padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:var(--r);outline:none;font-family:var(--font);font-size:.875rem;background:#fff;color:var(--text);transition:border-color var(--t);appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%23888' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .6rem center;background-size:12px}
.form-select:focus{border-color:var(--red)}
.form-textarea{width:100%;padding:.6rem .85rem;border:1.5px solid var(--border);border-radius:var(--r);outline:none;font-family:var(--font);font-size:.875rem;color:var(--text);resize:vertical;min-height:110px;transition:border-color var(--t)}
.form-textarea:focus{border-color:var(--red)}

#contact-success{display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:var(--r);padding:1rem 1.2rem;color:#166534;font-size:.85rem;margin-bottom:1rem;align-items:center;gap:.6rem}

.map-card{background:#fff;border-radius:var(--r-lg);overflow:hidden;border:1px solid var(--border);margin-bottom:2.5rem}
.map-card iframe{width:100%;height:380px;display:block;border:none;filter:grayscale(20%)}
.map-fallback{height:300px;background:linear-gradient(135deg,#f8f8f8,#f0f0f0);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;color:var(--gray-400)}
.map-fallback i{font-size:2.5rem;color:var(--red);opacity:.4}
.map-addr{padding:1.2rem 1.4rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:.75rem;font-size:.84rem;color:var(--gray-600)}

.social-row{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2.5rem}
.social-btn{display:flex;align-items:center;gap:.55rem;padding:.55rem 1.1rem;border-radius:var(--r);font-size:.82rem;font-weight:600;transition:all var(--t);border:1px solid var(--border);color:var(--gray-600);background:#fff}
.social-btn:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.social-fb:hover{border-color:#1877f2;color:#1877f2;background:#f0f4ff}
.social-zalo:hover{border-color:#0068ff;color:#0068ff;background:#f0f6ff}
.social-yt:hover{border-color:#ff0000;color:#ff0000;background:#fff5f5}
.social-tiktok:hover{border-color:#111;color:#111;background:#f9f9f9}

@media(max-width:768px){
  .contact-grid{grid-template-columns:1fr}
  .form-row{grid-template-columns:1fr}
}
</style>

<!-- HERO -->
<div class="contact-hero">
  <div style="position:relative;z-index:1">
    <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(227,0,0,.12);border:1px solid rgba(227,0,0,.25);border-radius:99px;padding:.3rem .9rem;font-size:.72rem;color:var(--red);font-weight:700;margin-bottom:1rem;letter-spacing:.5px">
      <i class="fa-solid fa-phone"></i> LIÊN HỆ
    </div>
    <h1>LIÊN HỆ <em>VỚI CHÚNG TÔI</em></h1>
    <p>Đội ngũ tư vấn nhiệt tình sẵn sàng hỗ trợ bạn 7 ngày trong tuần, từ 8:00 đến 21:00.</p>
  </div>
</div>

<div class="contact-wrap">

  <div class="contact-grid">
    <!-- Thông tin liên hệ -->
    <div class="info-cards reveal">
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
        <div>
          <div class="info-lbl">Hotline</div>
          <div class="info-val"><a href="tel:0909999888" style="color:var(--black)">0909 999 888</a></div>
          <div class="info-sub">Miễn phí · 8:00 – 21:00 hằng ngày</div>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
        <div>
          <div class="info-lbl">Email</div>
          <div class="info-val"><a href="mailto:info@tuanhuycmp.vn" style="color:var(--black)">info@tuanhuycmp.vn</a></div>
          <div class="info-sub">Phản hồi trong vòng 2–4 giờ làm việc</div>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
        <div>
          <div class="info-lbl">Địa chỉ</div>
          <div class="info-val">47-49 Xô Viết Nghệ Tĩnh</div>
          <div class="info-sub">Phường Phú Lợi, Cần Thơ</div>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
        <div>
          <div class="info-lbl">Giờ làm việc</div>
          <div class="info-val">Thứ 2 – Chủ nhật</div>
          <div class="info-sub">8:00 – 21:00 · Kể cả ngày lễ</div>
        </div>
      </div>
      <div class="info-card">
        <div class="info-icon"><i class="fa-brands fa-facebook"></i></div>
        <div>
          <div class="info-lbl">Facebook</div>
          <div class="info-val">Tuấn Huy Computer</div>
          <div class="info-sub">Nhắn tin để được tư vấn nhanh nhất</div>
        </div>
      </div>
    </div>

    <!-- Form liên hệ -->
    <div class="form-card reveal">
      <h2><i class="fa-solid fa-paper-plane" style="color:var(--red)"></i>Gửi tin nhắn cho chúng tôi</h2>

      <div id="contact-success">
        <i class="fa-solid fa-circle-check" style="font-size:1.1rem"></i>
        <span>Tin nhắn đã gửi thành công! Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.</span>
      </div>

      <div class="form-row">
        <div>
          <label class="form-label">Họ và tên *</label>
          <input type="text" id="ct-name" class="form-input" placeholder="Nguyễn Văn A">
        </div>
        <div>
          <label class="form-label">Số điện thoại *</label>
          <input type="tel" id="ct-phone" class="form-input" placeholder="0909 xxx xxx">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" id="ct-email" class="form-input" placeholder="your@email.com">
      </div>
      <div class="form-group">
        <label class="form-label">Chủ đề</label>
        <select id="ct-topic" class="form-select">
          <option value="">-- Chọn chủ đề --</option>
          <option value="tuvan">Tư vấn sản phẩm / Build PC</option>
          <option value="donhang">Hỏi về đơn hàng</option>
          <option value="baohanh">Bảo hành / Sửa chữa</option>
          <option value="khieuhai">Khiếu nại / Phản hồi</option>
          <option value="khac">Khác</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Nội dung *</label>
        <textarea id="ct-msg" class="form-textarea" placeholder="Mô tả yêu cầu của bạn..."></textarea>
      </div>
      <button onclick="submitContact()" id="ct-btn" class="btn-red" style="width:100%;justify-content:center;padding:.65rem">
        <i class="fa-solid fa-paper-plane"></i>Gửi tin nhắn
      </button>
    </div>
  </div>

  <!-- BẢN ĐỒ -->
  <div class="map-card reveal">
    <div class="map-fallback">
      <i class="fa-solid fa-map-location-dot"></i>
      <div style="text-align:center">
        <div style="font-weight:700;color:var(--gray-600);margin-bottom:.3rem">47-49 Xô Viết Nghệ Tĩnh, Phường Phú Lợi, Cần Thơ</div>
        <a href="https://maps.google.com/?q=47+Xo+Viet+Nghe+Tinh+Phu+Loi+Can+Tho" target="_blank" class="btn-outline" style="font-size:.78rem;padding:.38rem .9rem">
          <i class="fa-solid fa-map-pin"></i>Mở Google Maps
        </a>
      </div>
    </div>
    <div class="map-addr">
      <i class="fa-solid fa-location-dot" style="color:var(--red)"></i>
      <span><strong>Tuấn Huy Computer</strong> · 47-49 Xô Viết Nghệ Tĩnh, Phường Phú Lợi, Cần Thơ</span>
    </div>
  </div>

  <!-- MẠNG XÃ HỘI -->
  <div class="reveal">
    <div style="font-size:.75rem;font-weight:700;color:var(--gray-400);letter-spacing:.5px;text-transform:uppercase;margin-bottom:.85rem">Kết nối với chúng tôi</div>
    <div class="social-row">
      <a href="#" class="social-btn social-fb"><i class="fa-brands fa-facebook" style="color:#1877f2"></i>Facebook</a>
      <a href="#" class="social-btn social-zalo"><i class="fa-solid fa-comment-dots" style="color:#0068ff"></i>Zalo</a>
      <a href="#" class="social-btn social-yt"><i class="fa-brands fa-youtube" style="color:#ff0000"></i>YouTube</a>
      <a href="#" class="social-btn social-tiktok"><i class="fa-brands fa-tiktok"></i>TikTok</a>
    </div>
  </div>

</div>

<script>
function submitContact() {
  var name  = document.getElementById('ct-name').value.trim();
  var phone = document.getElementById('ct-phone').value.trim();
  var msg   = document.getElementById('ct-msg').value.trim();
  if (!name || !phone || !msg) {
    showToast('Vui lòng điền đầy đủ họ tên, số điện thoại và nội dung.', 'error');
    return;
  }
  var btn = document.getElementById('ct-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
  // Simulate send (có thể kết nối API thực tế sau)
  setTimeout(function() {
    btn.style.display = 'none';
    var ok = document.getElementById('contact-success');
    ok.style.display = 'flex';
    // Clear form
    ['ct-name','ct-phone','ct-email','ct-msg'].forEach(function(id) {
      document.getElementById(id).value = '';
    });
    document.getElementById('ct-topic').value = '';
  }, 900);
}
</script>

<?php include __DIR__.'/../layouts/footer.php'; ?>
