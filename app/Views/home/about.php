<?php include __DIR__.'/../layouts/header.php'; ?>
<style>
/* ── ABOUT PAGE ── */

/* HERO */
.ab-hero{position:relative;min-height:92vh;background:#080808;display:flex;align-items:center;justify-content:center;overflow:hidden}
.ab-hero-bg{position:absolute;inset:0;background:
  radial-gradient(ellipse 70% 60% at 60% 40%,rgba(227,0,0,.18) 0%,transparent 55%),
  radial-gradient(ellipse 40% 50% at 10% 80%,rgba(227,0,0,.08) 0%,transparent 50%);
}
.ab-grid{position:absolute;inset:0;background-image:
  linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
  linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
  background-size:60px 60px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,#000 40%,transparent 100%)}
.ab-particles{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.ab-p{position:absolute;width:2px;height:2px;background:var(--red);border-radius:50%;opacity:0;animation:pFloat var(--dur,6s) var(--del,0s) ease-in-out infinite}
@keyframes pFloat{0%,100%{opacity:0;transform:translateY(0) scale(1)}20%,80%{opacity:.6}50%{opacity:1;transform:translateY(-120px) scale(1.5)}}

.ab-hero-inner{position:relative;z-index:2;text-align:center;padding:2rem 1rem;max-width:900px}
.ab-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(227,0,0,.12);border:1px solid rgba(227,0,0,.3);border-radius:99px;padding:.35rem 1rem;font-size:.7rem;color:var(--red);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1.75rem;animation:fadeUp .6s ease both}
.ab-hero-title{font-size:clamp(2.4rem,6vw,4.5rem);font-weight:900;color:#fff;font-family:var(--font-head);letter-spacing:-1px;line-height:1.05;margin-bottom:1.25rem;animation:fadeUp .7s .1s ease both}
.ab-hero-title .red{color:var(--red);text-shadow:0 0 40px rgba(227,0,0,.5)}
.ab-hero-title .line{display:block}
.ab-hero-sub{font-size:1rem;color:#777;max-width:600px;margin:0 auto 2.25rem;line-height:1.8;animation:fadeUp .7s .2s ease both}
.ab-hero-sub strong{color:#bbb}
.ab-hero-btns{display:flex;gap:.85rem;justify-content:center;flex-wrap:wrap;animation:fadeUp .7s .3s ease both}

.ab-scroll-hint{position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:.35rem;color:#333;font-size:.68rem;letter-spacing:1px;animation:bounce 2s infinite}
.ab-scroll-hint i{font-size:.9rem;color:#444}
@keyframes bounce{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(6px)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

/* STATS BAND */
.ab-stats{background:#fff;border-bottom:1px solid var(--border)}
.ab-stats-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr)}
.ab-stat{padding:2.5rem 1.5rem;text-align:center;position:relative;transition:background var(--t)}
.ab-stat::after{content:'';position:absolute;right:0;top:20%;bottom:20%;width:1px;background:var(--border)}
.ab-stat:last-child::after{display:none}
.ab-stat:hover{background:var(--gray-50)}
.ab-stat-num{font-size:2.8rem;font-weight:900;color:var(--red);font-family:var(--font-head);line-height:1;display:block}
.ab-stat-unit{font-size:1.4rem;color:var(--red)}
.ab-stat-lbl{font-size:.8rem;color:var(--gray-600);margin-top:.4rem;font-weight:500}
.ab-stat-icon{font-size:1.5rem;color:var(--gray-200);margin-bottom:.5rem;display:block}

/* SECTION GENERIC */
.ab-sec{padding:5rem 1rem}
.ab-sec-inner{max-width:1200px;margin:0 auto}
.ab-sec-tag{display:inline-flex;align-items:center;gap:.45rem;background:rgba(227,0,0,.08);border:1px solid rgba(227,0,0,.18);border-radius:99px;padding:.28rem .85rem;font-size:.68rem;color:var(--red);font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:.9rem}
.ab-sec-title{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;color:var(--black);font-family:var(--font-head);letter-spacing:-.5px;line-height:1.15;margin-bottom:.75rem}
.ab-sec-title em{color:var(--red);font-style:normal}
.ab-sec-desc{font-size:.92rem;color:var(--gray-600);line-height:1.8;max-width:520px}

/* STORY SECTION */
.ab-story{background:var(--gray-50)}
.ab-story-grid{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center}
.ab-story-visual{position:relative}
.ab-story-card{background:#0d0d0d;border-radius:20px;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;border:1px solid #1e1e1e}
.ab-story-card::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 80% at 50% 50%,rgba(227,0,0,.2) 0%,transparent 70%)}
.ab-story-icon{font-size:6rem;color:rgba(227,0,0,.2);position:relative;z-index:1}
.ab-story-float{position:absolute;background:#fff;border-radius:14px;padding:.75rem 1.1rem;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid var(--border);display:flex;align-items:center;gap:.65rem;font-size:.78rem;font-weight:700;color:var(--black)}
.ab-story-float i{color:var(--red);font-size:.9rem}
.ab-story-float-1{bottom:-1rem;left:-1.5rem}
.ab-story-float-2{top:-1rem;right:-1.5rem}
.ab-story-float-2 i{color:#22c55e}
.ab-story-text p{color:var(--gray-600);font-size:.9rem;line-height:1.85;margin-bottom:1rem}
.ab-story-text p:last-child{margin-bottom:0}
.ab-year-tag{display:inline-flex;align-items:center;gap:.4rem;background:#111;color:#fff;padding:.3rem .8rem;border-radius:6px;font-size:.72rem;font-weight:700;margin-bottom:1.25rem}
.ab-year-tag i{color:var(--red)}

/* TIMELINE */
.ab-timeline-sec{background:#080808;overflow:hidden;position:relative}
.ab-timeline-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 50% 60% at 50% 50%,rgba(227,0,0,.06) 0%,transparent 60%)}
.ab-tl-head{text-align:center;margin-bottom:3.5rem}
.ab-tl-head .ab-sec-title{color:#fff}
.ab-tl-head .ab-sec-desc{margin:0 auto;color:#666}
.ab-timeline{position:relative;max-width:900px;margin:0 auto}
.ab-timeline::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:1px;background:linear-gradient(to bottom,transparent,rgba(227,0,0,.4) 10%,rgba(227,0,0,.4) 90%,transparent);transform:translateX(-50%)}
.ab-tl-item{display:grid;grid-template-columns:1fr 60px 1fr;gap:0;margin-bottom:3rem;align-items:start}
.ab-tl-item:last-child{margin-bottom:0}
.ab-tl-content{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:1.4rem 1.6rem;transition:all .3s}
.ab-tl-content:hover{background:rgba(227,0,0,.05);border-color:rgba(227,0,0,.2);transform:translateY(-3px)}
.ab-tl-left{text-align:right}
.ab-tl-right{text-align:left}
.ab-tl-empty{/* placeholder */}
.ab-tl-dot{display:flex;align-items:flex-start;justify-content:center;padding-top:.9rem}
.ab-tl-dot-inner{width:14px;height:14px;border-radius:50%;background:var(--red);box-shadow:0 0 0 4px rgba(227,0,0,.2),0 0 20px rgba(227,0,0,.4);flex-shrink:0}
.ab-tl-year{font-size:.7rem;font-weight:800;color:var(--red);letter-spacing:1px;margin-bottom:.4rem;text-transform:uppercase}
.ab-tl-title{font-size:.95rem;font-weight:700;color:#fff;margin-bottom:.4rem}
.ab-tl-desc{font-size:.8rem;color:#666;line-height:1.65}

/* WHY US */
.ab-why-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-top:3rem}
.ab-why-card{background:#fff;border-radius:16px;padding:2rem 1.75rem;border:1px solid var(--border);position:relative;overflow:hidden;transition:all .3s;cursor:default}
.ab-why-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(227,0,0,.04) 0%,transparent 60%);opacity:0;transition:opacity .3s}
.ab-why-card:hover{box-shadow:0 16px 48px rgba(0,0,0,.1);transform:translateY(-5px);border-color:#e0e0e0}
.ab-why-card:hover::before{opacity:1}
.ab-why-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(to right,var(--red),transparent);transform:scaleX(0);transform-origin:left;transition:transform .3s}
.ab-why-card:hover::after{transform:scaleX(1)}
.ab-why-num{position:absolute;top:1.5rem;right:1.5rem;font-size:3.5rem;font-weight:900;color:var(--border);font-family:var(--font-head);line-height:1;transition:color .3s}
.ab-why-card:hover .ab-why-num{color:#f0e0e0}
.ab-why-icon{width:52px;height:52px;background:rgba(227,0,0,.08);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:var(--red);margin-bottom:1.1rem;transition:all .3s}
.ab-why-card:hover .ab-why-icon{background:var(--red);color:#fff;box-shadow:0 8px 24px rgba(227,0,0,.35)}
.ab-why-card h3{font-size:.95rem;font-weight:800;color:var(--black);margin-bottom:.55rem}
.ab-why-card p{font-size:.8rem;color:var(--gray-600);line-height:1.7}

/* TEAM */
.ab-team-sec{background:var(--gray-50)}
.ab-team-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-top:3rem}
.ab-team-card{background:#fff;border-radius:18px;overflow:hidden;border:1px solid var(--border);transition:all .3s;text-align:center}
.ab-team-card:hover{box-shadow:0 20px 60px rgba(0,0,0,.1);transform:translateY(-6px)}
.ab-team-top{padding:2rem 1rem 1.25rem;position:relative}
.ab-team-top::before{content:'';position:absolute;inset:0;height:80px;background:linear-gradient(135deg,#0d0d0d,#1a0000);border-radius:18px 18px 0 0}
.ab-team-av{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;font-weight:900;margin:0 auto;position:relative;z-index:1;border:3px solid rgba(255,255,255,.15);font-family:var(--font-head)}
.ab-team-body{padding:1.25rem 1rem 1.5rem}
.ab-team-name{font-size:.92rem;font-weight:800;color:var(--black);margin-bottom:.2rem}
.ab-team-role{font-size:.72rem;color:var(--red);font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:.75rem}
.ab-team-desc{font-size:.75rem;color:var(--gray-600);line-height:1.6;margin-bottom:.9rem}
.ab-team-tag{display:inline-block;background:rgba(227,0,0,.07);color:var(--red);border-radius:99px;padding:.18rem .65rem;font-size:.65rem;font-weight:700;margin:.15rem}

/* PARTNERS MARQUEE */
.ab-partners-sec{background:#080808;padding:4rem 0;overflow:hidden;position:relative}
.ab-partners-sec::before{content:'';position:absolute;left:0;top:0;bottom:0;width:120px;background:linear-gradient(to right,#080808,transparent);z-index:2;pointer-events:none}
.ab-partners-sec::after{content:'';position:absolute;right:0;top:0;bottom:0;width:120px;background:linear-gradient(to left,#080808,transparent);z-index:2;pointer-events:none}
.ab-partners-head{text-align:center;margin-bottom:2.5rem;padding:0 1rem}
.ab-partners-head .ab-sec-title{color:#fff;font-size:1.5rem}
.ab-marquee-wrap{display:flex;overflow:hidden;gap:2rem}
.ab-marquee{display:flex;gap:1.5rem;animation:marquee 25s linear infinite;flex-shrink:0}
.ab-marquee-2{animation-delay:-12.5s}
@keyframes marquee{from{transform:translateX(0)}to{transform:translateX(calc(-100% - 1.5rem))}}
.ab-brand{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:.85rem 1.75rem;font-size:.85rem;font-weight:800;color:#888;letter-spacing:1px;white-space:nowrap;transition:all .3s;flex-shrink:0}
.ab-brand:hover{background:rgba(227,0,0,.1);border-color:rgba(227,0,0,.3);color:#fff}

/* CTA */
.ab-cta{position:relative;overflow:hidden;background:#080808;padding:6rem 1rem}
.ab-cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 70% at 50% 50%,rgba(227,0,0,.15) 0%,transparent 65%)}
.ab-cta-inner{position:relative;z-index:1;max-width:700px;margin:0 auto;text-align:center}
.ab-cta-inner h2{font-size:clamp(1.8rem,4vw,3rem);font-weight:900;color:#fff;font-family:var(--font-head);letter-spacing:-.5px;margin-bottom:.8rem;line-height:1.15}
.ab-cta-inner h2 em{color:var(--red);font-style:normal}
.ab-cta-inner p{color:#666;font-size:.92rem;line-height:1.7;margin-bottom:2rem}
.ab-cta-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-glow{background:var(--red);color:#fff;display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.6rem;border-radius:10px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;font-family:var(--font);transition:all .3s;box-shadow:0 0 0 0 rgba(227,0,0,.4)}
.btn-glow:hover{background:var(--red-dk);box-shadow:0 0 30px rgba(227,0,0,.5);transform:translateY(-2px)}
.btn-ghost-dark{display:inline-flex;align-items:center;gap:.45rem;padding:.68rem 1.6rem;border-radius:10px;font-weight:700;font-size:.88rem;border:1.5px solid rgba(255,255,255,.12);color:#888;background:transparent;transition:all .3s}
.btn-ghost-dark:hover{border-color:rgba(255,255,255,.35);color:#fff;background:rgba(255,255,255,.05)}

/* RESPONSIVES */
@media(max-width:900px){
  .ab-story-grid{grid-template-columns:1fr;gap:2.5rem}
  .ab-story-float{display:none}
  .ab-why-grid{grid-template-columns:1fr 1fr}
  .ab-team-grid{grid-template-columns:1fr 1fr}
  .ab-timeline::before{left:20px}
  .ab-tl-item{grid-template-columns:40px 1fr;grid-template-areas:'dot content'}
  .ab-tl-left,.ab-tl-empty{display:none}
  .ab-tl-right{grid-area:content;text-align:left}
  .ab-tl-dot{grid-area:dot;padding-top:.9rem}
  .ab-stats-inner{grid-template-columns:repeat(2,1fr)}
  .ab-stat::after{display:none}
  .ab-stat:nth-child(1),.ab-stat:nth-child(2){border-bottom:1px solid var(--border)}
}
@media(max-width:600px){
  .ab-why-grid{grid-template-columns:1fr}
  .ab-team-grid{grid-template-columns:1fr 1fr}
  .ab-hero{min-height:80vh}
}
</style>

<!-- ── HERO ── -->
<section class="ab-hero">
  <div class="ab-hero-bg"></div>
  <div class="ab-grid"></div>
  <div class="ab-particles" id="ab-particles"></div>

  <div class="ab-hero-inner">
    <div class="ab-badge"><i class="fa-solid fa-microchip"></i> SINCE 2015</div>
    <h1 class="ab-hero-title">
      <span class="line">CHÚNG TÔI LÀ</span>
      <span class="line red">TUẤN HUY COMPUTER</span>
    </h1>
    <p class="ab-hero-sub">
      Không chỉ là một cửa hàng công nghệ —<br>
      chúng tôi là <strong>người đồng hành tin cậy</strong> cho mọi game thủ<br>và người dùng công nghệ Việt Nam suốt hơn một thập kỷ.
    </p>
    <div class="ab-hero-btns">
      <a href="#ab-story" class="btn-glow"><i class="fa-solid fa-play"></i>Khám phá ngay</a>
      <a href="<?= APP_URL ?>/contact" class="btn-ghost-dark"><i class="fa-solid fa-phone"></i>Liên hệ tư vấn</a>
    </div>
  </div>

  <div class="ab-scroll-hint">
    <span>CUỘN XUỐNG</span>
    <i class="fa-solid fa-chevron-down"></i>
  </div>
</section>

<!-- ── STATS BAND ── -->
<div class="ab-stats">
  <div class="ab-stats-inner">
    <div class="ab-stat reveal">
      <i class="ab-stat-icon fa-solid fa-calendar-check"></i>
      <span class="ab-stat-num" data-target="10" data-suffix="+">0</span>
      <div class="ab-stat-lbl">Năm hoạt động</div>
    </div>
    <div class="ab-stat reveal">
      <i class="ab-stat-icon fa-solid fa-users"></i>
      <span class="ab-stat-num" data-target="50" data-suffix="K+">0</span>
      <div class="ab-stat-lbl">Khách hàng tin dùng</div>
    </div>
    <div class="ab-stat reveal">
      <i class="ab-stat-icon fa-solid fa-box-open"></i>
      <span class="ab-stat-num" data-target="5" data-suffix="K+">0</span>
      <div class="ab-stat-lbl">Sản phẩm chính hãng</div>
    </div>
    <div class="ab-stat reveal">
      <i class="ab-stat-icon fa-solid fa-star"></i>
      <span class="ab-stat-num" data-target="4.9" data-suffix="★" data-decimal="1">0</span>
      <div class="ab-stat-lbl">Đánh giá trung bình</div>
    </div>
  </div>
</div>

<!-- ── STORY ── -->
<section class="ab-sec ab-story" id="ab-story">
  <div class="ab-sec-inner">
    <div class="ab-story-grid">
      <div class="reveal">
        <div class="ab-sec-tag"><i class="fa-solid fa-landmark"></i> Câu chuyện</div>
        <h2 class="ab-sec-title">Từ đam mê<br>đến <em>thương hiệu</em></h2>
        <div class="ab-year-tag"><i class="fa-solid fa-flag"></i> Thành lập năm 2015</div>
        <div class="ab-story-text">
          <p>Tuấn Huy Computer ra đời từ niềm đam mê cháy bỏng của anh Nguyễn Tuấn Huy — khi đó chỉ là một thanh niên trẻ với ước mơ mang những chiếc máy tính tốt nhất đến tay người dùng Việt với giá cả minh bạch, dịch vụ tận tâm.</p>
          <p>Từ một cửa hàng nhỏ tại Quận 7, TP.HCM, chúng tôi đã vươn lên trở thành một trong những nhà phân phối linh kiện và PC Gaming uy tín hàng đầu miền Nam — với hơn 50.000 khách hàng trung thành trên toàn quốc.</p>
          <p>Sứ mệnh của chúng tôi không thay đổi: <strong>sản phẩm chính hãng, giá tốt nhất, dịch vụ hoàn hảo nhất.</strong></p>
        </div>
      </div>

      <div class="ab-story-visual reveal">
        <div class="ab-story-card">
          <i class="ab-story-icon fa-solid fa-microchip"></i>
        </div>
        <div class="ab-story-float ab-story-float-1">
          <i class="fa-solid fa-shield-halved"></i>
          <div>
            <div style="font-size:.65rem;color:#888;font-weight:600">BẢO HÀNH</div>
            <div>Chính hãng 36 tháng</div>
          </div>
        </div>
        <div class="ab-story-float ab-story-float-2">
          <i class="fa-solid fa-circle-check"></i>
          <div>
            <div style="font-size:.65rem;color:#888;font-weight:600">ĐÁP GIÁ</div>
            <div style="color:#22c55e">Cam kết giá tốt nhất</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── TIMELINE ── -->
<section class="ab-sec ab-timeline-sec">
  <div class="ab-sec-inner">
    <div class="ab-tl-head reveal">
      <div class="ab-sec-tag" style="border-color:rgba(227,0,0,.3)"><i class="fa-solid fa-timeline"></i> Hành trình</div>
      <h2 class="ab-sec-title">10 năm <em>phát triển</em></h2>
      <p class="ab-sec-desc" style="color:#666">Từng mốc thời gian là một bước tiến, một câu chuyện về niềm tin và nỗ lực không ngừng.</p>
    </div>
    <div class="ab-timeline">
      <div class="ab-tl-item">
        <div class="ab-tl-content ab-tl-left reveal">
          <div class="ab-tl-year">2015</div>
          <div class="ab-tl-title">Khởi đầu</div>
          <div class="ab-tl-desc">Mở cửa hàng đầu tiên tại Quận 7, TP.HCM. Chỉ 2 nhân viên, tập trung vào linh kiện PC và tư vấn build máy.</div>
        </div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-empty"></div>
      </div>
      <div class="ab-tl-item">
        <div class="ab-tl-empty"></div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-content ab-tl-right reveal">
          <div class="ab-tl-year">2017</div>
          <div class="ab-tl-title">Mở rộng danh mục</div>
          <div class="ab-tl-desc">Trở thành đối tác phân phối chính thức của ASUS, MSI, Gigabyte. Bắt đầu nhập khẩu trực tiếp, giảm chi phí cho khách hàng.</div>
        </div>
      </div>
      <div class="ab-tl-item">
        <div class="ab-tl-content ab-tl-left reveal">
          <div class="ab-tl-year">2019</div>
          <div class="ab-tl-title">Ra mắt website bán hàng</div>
          <div class="ab-tl-desc">Lên trực tuyến, phủ sóng toàn quốc. Hệ thống giao hàng toàn quốc, thanh toán đa kênh. Khách hàng tăng 300% sau 6 tháng.</div>
        </div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-empty"></div>
      </div>
      <div class="ab-tl-item">
        <div class="ab-tl-empty"></div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-content ab-tl-right reveal">
          <div class="ab-tl-year">2021</div>
          <div class="ab-tl-title">Vượt 20.000 khách hàng</div>
          <div class="ab-tl-desc">Mốc 20.000 đơn hàng thành công. Mở thêm showroom trưng bày và trung tâm bảo hành, sửa chữa chuyên nghiệp.</div>
        </div>
      </div>
      <div class="ab-tl-item">
        <div class="ab-tl-content ab-tl-left reveal">
          <div class="ab-tl-year">2023</div>
          <div class="ab-tl-title">Công cụ Build PC AI</div>
          <div class="ab-tl-desc">Ra mắt công cụ Build PC thông minh — tư vấn cấu hình tự động theo ngân sách, nhu cầu và đảm bảo tương thích linh kiện.</div>
        </div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-empty"></div>
      </div>
      <div class="ab-tl-item">
        <div class="ab-tl-empty"></div>
        <div class="ab-tl-dot"><div class="ab-tl-dot-inner"></div></div>
        <div class="ab-tl-content ab-tl-right reveal" style="border-color:rgba(227,0,0,.3);background:rgba(227,0,0,.05)">
          <div class="ab-tl-year">2025 — Hiện tại</div>
          <div class="ab-tl-title" style="color:#fff">50.000+ khách hàng</div>
          <div class="ab-tl-desc" style="color:#888">Hơn 50.000 khách hàng tin dùng. Tiếp tục mở rộng, nâng cấp trải nghiệm — và đặt ra mục tiêu 100.000 khách hàng vào 2027.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── WHY US ── -->
<section class="ab-sec">
  <div class="ab-sec-inner">
    <div style="text-align:center;margin-bottom:.5rem" class="reveal">
      <div class="ab-sec-tag" style="margin:0 auto .9rem"><i class="fa-solid fa-star"></i> Lý do chọn chúng tôi</div>
      <h2 class="ab-sec-title">Tại sao hơn <em>50.000 khách hàng</em><br>tin tưởng chúng tôi?</h2>
    </div>
    <div class="ab-why-grid">
      <div class="ab-why-card reveal">
        <div class="ab-why-num">01</div>
        <div class="ab-why-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h3>Bảo hành chính hãng</h3>
        <p>100% sản phẩm có hóa đơn VAT, bảo hành từ 12–36 tháng. Đổi mới trong 7 ngày nếu lỗi nhà sản xuất — không hỏi thêm.</p>
      </div>
      <div class="ab-why-card reveal">
        <div class="ab-why-num">02</div>
        <div class="ab-why-icon"><i class="fa-solid fa-tags"></i></div>
        <h3>Cam kết giá tốt nhất</h3>
        <p>Tìm được nơi rẻ hơn cùng sản phẩm chính hãng? Chúng tôi hoàn lại phần chênh lệch — ngay lập tức, không điều kiện.</p>
      </div>
      <div class="ab-why-card reveal">
        <div class="ab-why-num">03</div>
        <div class="ab-why-icon"><i class="fa-solid fa-truck-fast"></i></div>
        <h3>Giao hàng toàn quốc</h3>
        <p>Đóng gói chống sốc 3 lớp, giao hàng 2–5 ngày toàn quốc. Miễn phí vận chuyển cho đơn từ 500.000đ.</p>
      </div>
      <div class="ab-why-card reveal">
        <div class="ab-why-num">04</div>
        <div class="ab-why-icon"><i class="fa-solid fa-headset"></i></div>
        <h3>Hỗ trợ 7 ngày/tuần</h3>
        <p>Kỹ thuật viên dày dặn kinh nghiệm trực tuyến 8:00–21:00 kể cả cuối tuần. Gọi một cuộc — giải quyết ngay.</p>
      </div>
      <div class="ab-why-card reveal">
        <div class="ab-why-num">05</div>
        <div class="ab-why-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <h3>Tư vấn Build PC thông minh</h3>
        <p>Công cụ Build PC tích hợp AI giúp chọn linh kiện phù hợp ngân sách, đảm bảo tương thích 100% và hiệu năng tối ưu.</p>
      </div>
      <div class="ab-why-card reveal">
        <div class="ab-why-num">06</div>
        <div class="ab-why-icon"><i class="fa-solid fa-medal"></i></div>
        <h3>Uy tín được kiểm chứng</h3>
        <p>4.9★ trên Google, Facebook và các sàn TMĐT. 10 năm không một khiếu nại gian lận — uy tín là tất cả với chúng tôi.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── TEAM ── -->
<section class="ab-sec ab-team-sec">
  <div class="ab-sec-inner">
    <div style="text-align:center" class="reveal">
      <div class="ab-sec-tag" style="margin:0 auto .9rem"><i class="fa-solid fa-users"></i> Đội ngũ</div>
      <h2 class="ab-sec-title">Những người <em>đứng sau</em> Tuấn Huy</h2>
      <p class="ab-sec-desc" style="margin:0 auto">Chúng tôi là những người yêu công nghệ, đam mê gaming và luôn đặt khách hàng lên hàng đầu.</p>
    </div>
    <div class="ab-team-grid">
      <div class="ab-team-card reveal">
        <div class="ab-team-top">
          <div class="ab-team-av" style="background:linear-gradient(135deg,#e30000,#7b0000)">TH</div>
        </div>
        <div class="ab-team-body">
          <div class="ab-team-name">Nguyễn Tuấn Huy</div>
          <div class="ab-team-role">Giám đốc & Sáng lập</div>
          <p class="ab-team-desc">Người đặt nền móng cho Tuấn Huy Computer, với hơn 15 năm kinh nghiệm trong ngành công nghệ.</p>
          <span class="ab-team-tag">Gaming</span>
          <span class="ab-team-tag">Business</span>
        </div>
      </div>
      <div class="ab-team-card reveal">
        <div class="ab-team-top">
          <div class="ab-team-av" style="background:linear-gradient(135deg,#1d4ed8,#1e3a8a)">AN</div>
        </div>
        <div class="ab-team-body">
          <div class="ab-team-name">Trần Anh Nguyên</div>
          <div class="ab-team-role">Kỹ thuật trưởng</div>
          <p class="ab-team-desc">10 năm kinh nghiệm lắp ráp và tối ưu hệ thống PC. Chuyên gia về overclock và cooling.</p>
          <span class="ab-team-tag">Hardware</span>
          <span class="ab-team-tag">Overclocking</span>
        </div>
      </div>
      <div class="ab-team-card reveal">
        <div class="ab-team-top">
          <div class="ab-team-av" style="background:linear-gradient(135deg,#059669,#065f46)">ML</div>
        </div>
        <div class="ab-team-body">
          <div class="ab-team-name">Lê Minh Linh</div>
          <div class="ab-team-role">Tư vấn bán hàng</div>
          <p class="ab-team-desc">Luôn tìm ra giải pháp phù hợp nhất với ngân sách của từng khách hàng. Tỷ lệ hài lòng 98%.</p>
          <span class="ab-team-tag">Sales</span>
          <span class="ab-team-tag">Build PC</span>
        </div>
      </div>
      <div class="ab-team-card reveal">
        <div class="ab-team-top">
          <div class="ab-team-av" style="background:linear-gradient(135deg,#7c3aed,#4c1d95)">PH</div>
        </div>
        <div class="ab-team-body">
          <div class="ab-team-name">Phạm Thanh Hà</div>
          <div class="ab-team-role">Chăm sóc khách hàng</div>
          <p class="ab-team-desc">Đảm bảo mọi khách hàng đều nhận được sự phục vụ tận tình và giải quyết vấn đề nhanh chóng.</p>
          <span class="ab-team-tag">Support</span>
          <span class="ab-team-tag">Warranty</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── PARTNERS MARQUEE ── -->
<section class="ab-partners-sec">
  <div class="ab-partners-head reveal">
    <div class="ab-sec-tag" style="margin:0 auto .9rem;border-color:rgba(227,0,0,.3)"><i class="fa-solid fa-handshake"></i> Đối tác</div>
    <h2 class="ab-sec-title" style="color:#fff;text-align:center">Đối tác <em>chính hãng</em> toàn cầu</h2>
  </div>
  <div class="ab-marquee-wrap">
    <div class="ab-marquee">
      <?php foreach(['ASUS','MSI','GIGABYTE','INTEL','AMD','SAMSUNG','CORSAIR','WESTERN DIGITAL','NVIDIA','LOGITECH','KINGSTON','SEAGATE','THERMALTAKE','NOCTUA','ZOTAC'] as $b): ?>
      <div class="ab-brand"><?= $b ?></div>
      <?php endforeach; ?>
    </div>
    <div class="ab-marquee ab-marquee-2" aria-hidden="true">
      <?php foreach(['ASUS','MSI','GIGABYTE','INTEL','AMD','SAMSUNG','CORSAIR','WESTERN DIGITAL','NVIDIA','LOGITECH','KINGSTON','SEAGATE','THERMALTAKE','NOCTUA','ZOTAC'] as $b): ?>
      <div class="ab-brand"><?= $b ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="ab-cta">
  <div class="ab-cta-inner reveal">
    <div class="ab-sec-tag" style="margin:0 auto 1.25rem;border-color:rgba(227,0,0,.3)"><i class="fa-solid fa-rocket"></i> Bắt đầu ngay</div>
    <h2>Sẵn sàng<br><em>nâng cấp hệ thống</em> của bạn?</h2>
    <p>Khám phá hàng nghìn sản phẩm chính hãng, dùng công cụ Build PC thông minh, hoặc gọi ngay để được tư vấn miễn phí bởi đội ngũ chuyên gia.</p>
    <div class="ab-cta-btns">
      <a href="<?= APP_URL ?>/products" class="btn-glow"><i class="fa-solid fa-layer-group"></i>Xem sản phẩm</a>
      <a href="<?= APP_URL ?>/products/pc-builder" class="btn-glow" style="background:#fff;color:#111;box-shadow:none" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#fff'"><i class="fa-solid fa-screwdriver-wrench" style="color:var(--red)"></i>Build PC ngay</a>
      <a href="<?= APP_URL ?>/contact" class="btn-ghost-dark"><i class="fa-solid fa-phone"></i>Liên hệ tư vấn</a>
    </div>
  </div>
</section>

<script>
// ── Floating particles
(function(){
  var w=document.getElementById('ab-particles');if(!w)return;
  for(var i=0;i<18;i++){
    var p=document.createElement('div');p.className='ab-p';
    p.style.cssText='left:'+Math.random()*100+'%;top:'+(40+Math.random()*50)+'%;--dur:'+(4+Math.random()*5)+'s;--del:'+(-Math.random()*6)+'s';
    w.appendChild(p);
  }
})();

// ── Animated counters
(function(){
  var els=document.querySelectorAll('[data-target]');
  var done=false;
  function run(){
    if(done)return;
    els.forEach(function(el){
      var r=el.getBoundingClientRect();
      if(r.top<window.innerHeight-50){
        done=true;
        els.forEach(function(e){
          var target=parseFloat(e.getAttribute('data-target'));
          var suffix=e.getAttribute('data-suffix')||'';
          var dec=parseInt(e.getAttribute('data-decimal')||'0');
          var start=0,dur=1400,startTime=null;
          function step(ts){
            if(!startTime)startTime=ts;
            var prog=Math.min((ts-startTime)/dur,1);
            var ease=1-Math.pow(1-prog,3);
            var cur=start+(target-start)*ease;
            e.textContent=(dec?cur.toFixed(dec):Math.round(cur))+suffix;
            if(prog<1)requestAnimationFrame(step);
          }
          requestAnimationFrame(step);
        });
      }
    });
  }
  window.addEventListener('scroll',run,{passive:true});
  run();
})();

// ── Smooth scroll for hero button
document.querySelector('[href="#ab-story"]').addEventListener('click',function(e){
  e.preventDefault();
  document.getElementById('ab-story').scrollIntoView({behavior:'smooth'});
});
</script>

<?php include __DIR__.'/../layouts/footer.php'; ?>
