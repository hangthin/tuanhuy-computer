<footer>
  <div style="height:3px;background:var(--red,#E30000)"></div>

  <div style="background:#111;padding:3rem 1rem 2rem">
    <div class="ft-grid" style="max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1.5fr 1fr 1fr 1.2fr;gap:2.5rem">

      <!-- Col 1: Brand -->
      <div>
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:1.1rem">
          <div style="width:38px;height:38px;background:var(--red,#E30000);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fa-solid fa-microchip" style="color:#fff;font-size:.85rem"></i>
          </div>
          <div>
            <div style="color:#fff;font-weight:900;font-size:.88rem;letter-spacing:-.2px">TUẤN HUY COMPUTER</div>
            <div style="color:#444;font-size:.58rem;letter-spacing:2px;margin-top:1px">CÔNG NGHỆ ĐỈNH CAO</div>
          </div>
        </div>
        <p style="color:#777;font-size:.8rem;line-height:1.75;margin-bottom:1.2rem">Chuyên PC, Laptop, linh kiện máy tính chính hãng. Cam kết giá tốt nhất TP.HCM. Bảo hành tận nơi, giao hàng toàn quốc.</p>
        <div style="display:flex;gap:.4rem">
          <?php foreach(array(
            array('fa-facebook-f', '#1877f2'),
            array('fa-youtube',    '#ff0000'),
            array('fa-tiktok',     '#ffffff'),
            array('fa-telegram',   '#0088cc')
          ) as $s): ?>
          <a href="#" style="width:32px;height:32px;border:1px solid #222;border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $s[1] ?>;font-size:.78rem;transition:background .18s,border-color .18s" onmouseover="this.style.background='<?= $s[1] ?>33';this.style.borderColor='<?= $s[1] ?>66'" onmouseout="this.style.background='transparent';this.style.borderColor='#222'">
            <i class="fa-brands <?= $s[0] ?>"></i>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Col 2: Danh mục -->
      <div>
        <div style="color:#fff;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.55rem">Danh mục</div>
        <div style="width:20px;height:3px;background:var(--red,#E30000);border-radius:2px;margin-bottom:.9rem"></div>
        <?php if(!isset($allCategories)) $allCategories=(new CategoryModel())->getAll(); ?>
        <?php foreach($allCategories as $cat): ?>
        <a href="<?= APP_URL ?>/products/<?= $cat['slug'] ?>" style="display:flex;align-items:center;gap:.35rem;color:#666;font-size:.79rem;padding:.22rem 0;transition:color .18s,transform .18s" onmouseover="this.style.color='#fff';this.style.transform='translateX(3px)'" onmouseout="this.style.color='#666';this.style.transform='translateX(0)'">
          <i class="fa-solid fa-angle-right" style="color:var(--red,#E30000);font-size:.6rem;flex-shrink:0"></i>
          <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Col 3: Hỗ trợ -->
      <div>
        <div style="color:#fff;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.55rem">Hỗ trợ</div>
        <div style="width:20px;height:3px;background:var(--red,#E30000);border-radius:2px;margin-bottom:.9rem"></div>
        <?php foreach(array(
          'Chính sách bảo hành',
          'Chính sách đổi trả',
          'Hướng dẫn mua hàng',
          'Hướng dẫn thanh toán',
          'Chính sách vận chuyển',
          'Câu hỏi thường gặp'
        ) as $s): ?>
        <a href="#" style="display:flex;align-items:center;gap:.35rem;color:#666;font-size:.79rem;padding:.22rem 0;transition:color .18s,transform .18s" onmouseover="this.style.color='#fff';this.style.transform='translateX(3px)'" onmouseout="this.style.color='#666';this.style.transform='translateX(0)'">
          <i class="fa-solid fa-angle-right" style="color:var(--red,#E30000);font-size:.6rem;flex-shrink:0"></i>
          <?= $s ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Col 4: Liên hệ -->
      <div>
        <div style="color:#fff;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.55rem">Liên hệ</div>
        <div style="width:20px;height:3px;background:var(--red,#E30000);border-radius:2px;margin-bottom:.9rem"></div>
        <?php foreach(array(
          array('fa-location-dot', '123 Nguyễn Văn Cừ, Q.5, TP.HCM'),
          array('fa-phone',        '0909 999 888'),
          array('fa-envelope',     'info@tuanhuycmp.vn'),
          array('fa-clock',        'T2–CN: 8:00 – 21:00')
        ) as $c): ?>
        <div style="display:flex;gap:.55rem;margin-bottom:.55rem;font-size:.79rem;color:#666;align-items:flex-start">
          <i class="fa-solid <?= $c[0] ?>" style="color:var(--red,#E30000);flex-shrink:0;margin-top:2px;font-size:.75rem;width:14px;text-align:center"></i>
          <span><?= $c[1] ?></span>
        </div>
        <?php endforeach; ?>
        <div style="height:1px;background:#222;margin:1rem 0 .8rem"></div>
        <div style="color:#555;font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;margin-bottom:.5rem">Thanh toán</div>
        <div style="display:flex;gap:.3rem;flex-wrap:wrap">
          <?php foreach(array(
            array('fa-money-bill-wave', 'COD'),
            array('fa-building-columns','Bank'),
            array('fa-mobile-screen',   'MoMo'),
            array('fa-credit-card',     'VNPay')
          ) as $pm): ?>
          <span style="background:#fff;border:1px solid #ddd;color:#555;padding:3px 8px;border-radius:5px;font-size:.67rem;display:inline-flex;align-items:center;gap:.28rem">
            <i class="fa-solid <?= $pm[0] ?>" style="font-size:.6rem;color:#888"></i><?= $pm[1] ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>

  <div style="height:1px;background:#222"></div>

  <!-- Bottom bar -->
  <div style="background:#0d0d0d;padding:.8rem 1rem;text-align:center">
    <div style="color:#444;font-size:.7rem">
      &copy; <?= date('Y') ?> <span style="color:var(--red,#E30000);font-weight:700">Tuấn Huy Computer</span>. All rights reserved.
    </div>
    <div style="color:#333;font-size:.65rem;margin-top:.2rem">Thiết kế bởi Tuấn Huy Team</div>
  </div>
</footer>

<!-- ── AI CHAT WIDGET ──────────────────────────────────────────────── -->
<style>
#thchat-btn{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9990;width:52px;height:52px;background:#E30000;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(227,0,0,.45);transition:transform .2s,box-shadow .2s}
#thchat-btn:hover{transform:scale(1.08);box-shadow:0 6px 24px rgba(227,0,0,.55)}
#thchat-btn i{color:#fff;font-size:1.15rem;transition:transform .25s}
#thchat-btn.open i.open-ico{display:none}
#thchat-btn.open i.close-ico{display:block}
#thchat-btn i.close-ico{display:none}
#thchat-panel{position:fixed;bottom:5.2rem;right:1.5rem;z-index:9989;width:340px;max-width:calc(100vw - 2rem);background:#111;border:1px solid #222;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.55);display:flex;flex-direction:column;overflow:hidden;transform:scale(.92) translateY(16px);opacity:0;pointer-events:none;transition:transform .22s ease,opacity .22s ease;transform-origin:bottom right}
#thchat-panel.show{transform:scale(1) translateY(0);opacity:1;pointer-events:auto}
.thc-hd{background:#1a0000;padding:.75rem 1rem;display:flex;align-items:center;gap:.55rem;border-bottom:1px solid #2a0000;flex-shrink:0}
.thc-av{width:32px;height:32px;background:#E30000;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.thc-hd-info{flex:1;min-width:0}
.thc-hd-name{font-weight:700;font-size:.82rem;color:#fff}
.thc-hd-status{font-size:.65rem;color:#22c55e;display:flex;align-items:center;gap:.3rem}
.thc-hd-status::before{content:'';width:6px;height:6px;background:#22c55e;border-radius:50%;display:inline-block}
.thc-msgs{flex:1;overflow-y:auto;padding:.75rem;display:flex;flex-direction:column;gap:.55rem;min-height:220px;max-height:340px;scroll-behavior:smooth}
.thc-msgs::-webkit-scrollbar{width:3px}.thc-msgs::-webkit-scrollbar-thumb{background:#2a2a2a;border-radius:99px}
.thc-bubble{max-width:88%;padding:.5rem .75rem;border-radius:12px;font-size:.8rem;line-height:1.6;word-break:break-word;white-space:pre-wrap}
.thc-bubble.bot{background:#1e1e1e;color:#ddd;border-bottom-left-radius:4px;align-self:flex-start}
.thc-bubble.user{background:#E30000;color:#fff;border-bottom-right-radius:4px;align-self:flex-end}
.thc-typing{display:flex;gap:4px;align-items:center;padding:.45rem .75rem;background:#1e1e1e;border-radius:12px;width:52px;border-bottom-left-radius:4px;align-self:flex-start}
.thc-typing span{width:6px;height:6px;background:#555;border-radius:50%;animation:thcDot 1.2s infinite}
.thc-typing span:nth-child(2){animation-delay:.2s}
.thc-typing span:nth-child(3){animation-delay:.4s}
@keyframes thcDot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-5px)}}
.thc-inp-row{padding:.6rem .75rem;border-top:1px solid #1e1e1e;display:flex;gap:.4rem;align-items:flex-end;flex-shrink:0;background:#111}
#thc-inp{flex:1;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:10px;padding:.42rem .65rem;color:#ddd;font-size:.8rem;font-family:inherit;outline:none;resize:none;max-height:90px;line-height:1.5;transition:border-color .15s}
#thc-inp:focus{border-color:rgba(227,0,0,.5)}
#thc-inp::placeholder{color:#444}
#thc-send{width:34px;height:34px;background:#E30000;border:none;border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:opacity .15s}
#thc-send:hover{opacity:.85}
#thc-send:disabled{opacity:.4;cursor:not-allowed}
.thc-cards{display:flex;flex-direction:column;gap:.35rem;margin-top:.35rem;align-self:flex-start;width:100%}
.thc-card{display:flex;align-items:center;gap:.55rem;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:10px;padding:.45rem .55rem;text-decoration:none;transition:border-color .15s,background .15s;cursor:pointer}
.thc-card:hover{border-color:rgba(227,0,0,.4);background:#1e0505}
.thc-card-img{width:42px;height:42px;border-radius:7px;background:#111;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.thc-card-img img{width:100%;height:100%;object-fit:contain;padding:3px}
.thc-card-info{flex:1;min-width:0}
.thc-card-name{font-size:.73rem;font-weight:600;color:#ddd;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3}
.thc-card-price{font-size:.72rem;color:#E30000;font-weight:700;margin-top:1px}
.thc-card-arr{color:#444;font-size:.7rem;flex-shrink:0}
.thc-quick{padding:.4rem .75rem 0;display:flex;gap:.3rem;flex-wrap:wrap}
.thc-q{background:#1a1a1a;border:1px solid #252525;color:#888;font-size:.68rem;padding:.22rem .5rem;border-radius:99px;cursor:pointer;transition:all .15s;white-space:nowrap;font-family:inherit}
.thc-q:hover{border-color:rgba(227,0,0,.4);color:#E30000}
@media(max-width:400px){#thchat-panel{width:calc(100vw - 2rem);right:1rem}#thchat-btn{right:1rem}}
</style>

<!-- Toggle button -->
<button id="thchat-btn" onclick="thcToggle()" title="Chat với AI tư vấn">
  <i class="fa-solid fa-comments open-ico"></i>
  <i class="fa-solid fa-xmark close-ico"></i>
</button>

<!-- Chat panel -->
<div id="thchat-panel">
  <!-- Header -->
  <div class="thc-hd">
    <div class="thc-av"><i class="fa-solid fa-robot" style="color:#fff;font-size:.8rem"></i></div>
    <div class="thc-hd-info">
      <div class="thc-hd-name">Trợ lý Tuấn Huy</div>
      <div class="thc-hd-status">Trực tuyến 24/7</div>
    </div>
    <button onclick="thcToggle()" style="background:none;border:none;color:#555;cursor:pointer;padding:0;line-height:1"><i class="fa-solid fa-minus"></i></button>
  </div>
  <!-- Messages -->
  <div class="thc-msgs" id="thc-msgs">
    <div class="thc-bubble bot">Xin chào! Tôi là trợ lý tư vấn của Tuấn Huy Computer. Bạn cần hỗ trợ gì về sản phẩm hôm nay?</div>
  </div>
  <!-- Quick prompts -->
  <div class="thc-quick" id="thc-quick">
    <button class="thc-q" onclick="thcQuick(this)">Laptop gaming tầm 20tr</button>
    <button class="thc-q" onclick="thcQuick(this)">PC gaming giá rẻ</button>
    <button class="thc-q" onclick="thcQuick(this)">Màn hình tốt nhất</button>
    <button class="thc-q" onclick="thcQuick(this)">Bàn phím cơ</button>
  </div>
  <!-- Input -->
  <div class="thc-inp-row">
    <textarea id="thc-inp" rows="1" placeholder="Nhập câu hỏi..." onkeydown="thcKey(event)" oninput="thcResize(this)"></textarea>
    <button id="thc-send" onclick="thcSend()" title="Gửi"><i class="fa-solid fa-paper-plane" style="color:#fff;font-size:.78rem"></i></button>
  </div>
</div>

<script>
(function(){
  var BASE    = '<?= APP_URL ?>';
  var history = [];
  var busy    = false;
  var opened  = false;

  window.thcToggle = function() {
    opened = !opened;
    document.getElementById('thchat-panel').classList.toggle('show', opened);
    document.getElementById('thchat-btn').classList.toggle('open', opened);
    if (opened) setTimeout(function(){ document.getElementById('thc-inp').focus(); }, 250);
  };

  window.thcKey = function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); thcSend(); }
  };

  window.thcResize = function(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 90) + 'px';
  };

  window.thcQuick = function(btn) {
    document.getElementById('thc-inp').value = btn.textContent;
    document.getElementById('thc-quick').style.display = 'none';
    thcSend();
  };

  window.thcSend = function() {
    if (busy) return;
    var inp = document.getElementById('thc-inp');
    var msg = inp.value.trim();
    if (!msg) return;

    inp.value = ''; inp.style.height = 'auto';
    document.getElementById('thc-quick').style.display = 'none';
    thcAddBubble('user', msg);
    thcTyping(true);
    busy = true;
    document.getElementById('thc-send').disabled = true;

    fetch(BASE + '/api/chat', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: msg, history: history})
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
      thcTyping(false);
      busy = false;
      document.getElementById('thc-send').disabled = false;
      var reply = d.reply || 'Xin lỗi, có lỗi xảy ra.';
      thcAddBubble('bot', reply, d.products || []);
      history.push({role:'user', content: msg});
      history.push({role:'assistant', content: reply});
      if (history.length > 12) history = history.slice(-12);
    })
    .catch(function() {
      thcTyping(false);
      busy = false;
      document.getElementById('thc-send').disabled = false;
      thcAddBubble('bot', 'Kết nối thất bại. Vui lòng thử lại hoặc gọi 0909 999 888.');
    });
  };

  function thcAddBubble(role, text, products) {
    var msgs = document.getElementById('thc-msgs');
    var div  = document.createElement('div');
    div.className = 'thc-bubble ' + role;
    div.textContent = text;
    msgs.appendChild(div);

    // Render product cards nếu có
    if (role === 'bot' && products && products.length) {
      var cards = document.createElement('div');
      cards.className = 'thc-cards';
      products.forEach(function(p) {
        var a = document.createElement('a');
        a.className = 'thc-card';
        a.href = p.url;
        a.target = '_blank';
        var imgHtml = p.image
          ? '<img src="' + p.image + '" alt="" onerror="this.style.display=\'none\'">'
          : '<i class="fa-solid fa-box" style="color:#444;font-size:.9rem"></i>';
        var priceStr = new Intl.NumberFormat('vi-VN').format(p.price) + 'đ';
        a.innerHTML =
          '<div class="thc-card-img">' + imgHtml + '</div>' +
          '<div class="thc-card-info">' +
            '<div class="thc-card-name">' + p.name + '</div>' +
            '<div class="thc-card-price">' + priceStr + '</div>' +
          '</div>' +
          '<i class="fa-solid fa-arrow-right thc-card-arr"></i>';
        cards.appendChild(a);
      });
      msgs.appendChild(cards);
    }

    msgs.scrollTop = msgs.scrollHeight;
  }

  function thcTyping(show) {
    var msgs = document.getElementById('thc-msgs');
    var el   = document.getElementById('thc-typing-indicator');
    if (show) {
      if (el) return;
      var t = document.createElement('div');
      t.className = 'thc-typing'; t.id = 'thc-typing-indicator';
      t.innerHTML = '<span></span><span></span><span></span>';
      msgs.appendChild(t);
      msgs.scrollTop = msgs.scrollHeight;
    } else {
      if (el) el.remove();
    }
  }
})();
</script>

<style>
@media(max-width:768px){
  .ft-grid{grid-template-columns:1fr 1fr!important}
}
@media(max-width:480px){
  .ft-grid{grid-template-columns:1fr!important}
}
</style>

<script>
window.addEventListener('load',function(){var l=document.getElementById('pg-ld');if(l){l.classList.add('out');setTimeout(function(){l.remove()},500);}});
(function(){var els=document.querySelectorAll('.reveal');if(!els.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)x.target.classList.add('visible');});},{threshold:.08});els.forEach(function(el){o.observe(el);});})();
</script>
</body>
</html>
