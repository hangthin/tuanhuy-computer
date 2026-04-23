<?php $pageTitle='Đăng nhập'; require_once __DIR__.'/../layouts/header.php'; ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:#f5f5f5">
  <div style="width:100%;max-width:420px">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="display:inline-flex;align-items:center;gap:.65rem">
        <div style="width:46px;height:46px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.2rem">TH</div>
        <div><div style="font-weight:800;font-size:1rem;color:#111">TUẤN HUY COMPUTER</div><div style="font-size:.62rem;color:var(--red);letter-spacing:2px;font-weight:700">ĐĂNG NHẬP</div></div>
      </div>
    </div>
    <!-- Cat Avatar -->
    <style>
    #catWrap{display:flex;flex-direction:column;align-items:center;margin-bottom:.6rem;user-select:none}
    #catSvg{overflow:visible;filter:drop-shadow(0 6px 16px rgba(255,140,60,.25))}
    #catPawL,#catPawR{transition:transform .35s cubic-bezier(.34,1.56,.64,1)}
    #catEyes{transition:opacity .2s}
    #catEyesClosed{transition:opacity .2s}
    @keyframes catBob{0%,100%{transform:translateY(0) rotate(0deg)}40%{transform:translateY(-5px) rotate(-2deg)}60%{transform:translateY(-4px) rotate(2deg)}}
    @keyframes catShake{0%,100%{transform:rotate(0)}25%{transform:rotate(-5deg)}75%{transform:rotate(5deg)}}
    @keyframes catWag{0%,100%{transform:rotate(0deg)}50%{transform:rotate(18deg)}}
    #catSvg.state-watch{animation:catBob 2.2s ease-in-out infinite}
    #catSvg.state-cover{animation:catShake 1.6s ease-in-out infinite}
    #catTail{transform-box:fill-box;transform-origin:15% 85%;animation:catWag 1.6s ease-in-out infinite}
    </style>
    <div id="catWrap">
      <svg id="catSvg" viewBox="0 0 120 115" width="115" height="115">
        <defs>
          <radialGradient id="gHead" cx="45%" cy="38%" r="55%">
            <stop offset="0%" stop-color="#FFB87A"/>
            <stop offset="100%" stop-color="#FF8C3A"/>
          </radialGradient>
          <radialGradient id="gFace" cx="50%" cy="40%" r="55%">
            <stop offset="0%" stop-color="#FFD9B0"/>
            <stop offset="100%" stop-color="#FFC080"/>
          </radialGradient>
        </defs>

        <!-- Tail (bottom-right, curves up then hooks) -->
        <g id="catTail">
          <path d="M82 104 Q105 95 110 75 Q114 58 100 56" stroke="#FF8C3A" stroke-width="8" fill="none" stroke-linecap="round"/>
          <path d="M82 104 Q105 95 110 75 Q114 58 100 56" stroke="#FFB87A" stroke-width="3" fill="none" stroke-linecap="round" opacity=".5"/>
          <circle cx="100" cy="56" r="7" fill="#FF9A45"/>
          <circle cx="100" cy="56" r="4" fill="#FFB87A"/>
        </g>

        <!-- Body (tiny peek) -->
        <ellipse cx="60" cy="103" rx="26" ry="14" fill="#FF9A45"/>
        <ellipse cx="60" cy="103" rx="16" ry="10" fill="#FFD9B0"/>

        <!-- Head -->
        <circle cx="60" cy="62" r="42" fill="url(#gHead)"/>

        <!-- Ear left outer -->
        <polygon points="20,50 22,10 50,38" fill="#FF8C3A"/>
        <!-- Ear left inner (pink) -->
        <polygon points="26,46 28,20 46,38" fill="#FFB0C8"/>

        <!-- Ear right outer -->
        <polygon points="100,50 98,10 70,38" fill="#FF8C3A"/>
        <!-- Ear right inner -->
        <polygon points="94,46 92,20 74,38" fill="#FFB0C8"/>

        <!-- Face lighter center -->
        <ellipse cx="60" cy="68" rx="27" ry="22" fill="url(#gFace)"/>

        <!-- Forehead stripe marks (tabby) -->
        <path d="M52 28 Q60 24 68 28" stroke="#E07030" stroke-width="2.2" fill="none" stroke-linecap="round" opacity=".5"/>
        <path d="M50 34 Q60 30 70 34" stroke="#E07030" stroke-width="1.8" fill="none" stroke-linecap="round" opacity=".4"/>

        <!-- === EYES OPEN === -->
        <g id="catEyes">
          <!-- Left eye white -->
          <ellipse cx="42" cy="60" rx="11" ry="11" fill="white"/>
          <!-- Left iris (teal-green) -->
          <circle cx="42" cy="60" r="8" fill="#3DAA77"/>
          <!-- Left pupil slit -->
          <ellipse cx="42" cy="60" rx="3" ry="7.5" fill="#1a1a1a"/>
          <!-- Left highlights -->
          <circle cx="39" cy="56" r="2.5" fill="white" opacity=".9"/>
          <circle cx="44" cy="62" r="1.2" fill="white" opacity=".5"/>

          <!-- Right eye white -->
          <ellipse cx="78" cy="60" rx="11" ry="11" fill="white"/>
          <!-- Right iris -->
          <circle cx="78" cy="60" r="8" fill="#3DAA77"/>
          <!-- Right pupil slit -->
          <ellipse cx="78" cy="60" rx="3" ry="7.5" fill="#1a1a1a"/>
          <!-- Right highlights -->
          <circle cx="75" cy="56" r="2.5" fill="white" opacity=".9"/>
          <circle cx="80" cy="62" r="1.2" fill="white" opacity=".5"/>
        </g>

        <!-- === EYES CLOSED (squeeze shut, happy) === -->
        <g id="catEyesClosed" style="opacity:0;pointer-events:none">
          <path d="M31 60 Q42 51 53 60" stroke="#3d2015" stroke-width="3" fill="none" stroke-linecap="round"/>
          <path d="M67 60 Q78 51 89 60" stroke="#3d2015" stroke-width="3" fill="none" stroke-linecap="round"/>
          <!-- Eyelash lines -->
          <line x1="35" y1="58" x2="33" y2="53" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="42" y1="55" x2="42" y2="50" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="49" y1="58" x2="51" y2="53" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="71" y1="58" x2="69" y2="53" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="78" y1="55" x2="78" y2="50" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="85" y1="58" x2="87" y2="53" stroke="#3d2015" stroke-width="1.5" stroke-linecap="round"/>
        </g>

        <!-- Nose (heart-ish pink triangle) -->
        <path d="M60 73 L56.5 77.5 Q60 80 63.5 77.5 Z" fill="#FF8FAD"/>

        <!-- Mouth ω -->
        <path d="M52 81 Q56 87 60 83 Q64 87 68 81" stroke="#C0654A" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

        <!-- Whiskers left -->
        <line x1="8"  y1="70" x2="48" y2="74" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>
        <line x1="6"  y1="77" x2="48" y2="77" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>
        <line x1="10" y1="84" x2="48" y2="80" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>

        <!-- Whiskers right -->
        <line x1="112" y1="70" x2="72" y2="74" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>
        <line x1="114" y1="77" x2="72" y2="77" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>
        <line x1="110" y1="84" x2="72" y2="80" stroke="#c8a090" stroke-width="1.2" stroke-linecap="round" opacity=".7"/>

        <!-- Cheek blush -->
        <ellipse cx="26" cy="76" rx="10" ry="7" fill="rgba(255,140,140,.22)"/>
        <ellipse cx="94" cy="76" rx="10" ry="7" fill="rgba(255,140,140,.22)"/>

        <!-- === LEFT PAW (slides up over left eye) === -->
        <g id="catPawL" transform="translate(0,48)">
          <!-- Arm -->
          <rect x="26" y="28" width="20" height="28" rx="10" fill="#FF9A45"/>
          <!-- Paw pad -->
          <ellipse cx="36" cy="28" rx="13" ry="10" fill="#FFB87A"/>
          <!-- Toe beans (pink) -->
          <ellipse cx="27" cy="20" rx="5" ry="4.5" fill="#FFB87A"/>
          <ellipse cx="36" cy="18" rx="5.5" ry="5"  fill="#FFB87A"/>
          <ellipse cx="45" cy="20" rx="5" ry="4.5" fill="#FFB87A"/>
          <ellipse cx="27" cy="20" rx="3" ry="2.8" fill="#FFB0C8"/>
          <ellipse cx="36" cy="18" rx="3.3" ry="3"  fill="#FFB0C8"/>
          <ellipse cx="45" cy="20" rx="3" ry="2.8" fill="#FFB0C8"/>
          <!-- Center big bean -->
          <ellipse cx="36" cy="28" rx="5" ry="4" fill="#FFB0C8"/>
        </g>

        <!-- === RIGHT PAW === -->
        <g id="catPawR" transform="translate(0,48)">
          <rect x="74" y="28" width="20" height="28" rx="10" fill="#FF9A45"/>
          <ellipse cx="84" cy="28" rx="13" ry="10" fill="#FFB87A"/>
          <ellipse cx="75" cy="20" rx="5" ry="4.5" fill="#FFB87A"/>
          <ellipse cx="84" cy="18" rx="5.5" ry="5"  fill="#FFB87A"/>
          <ellipse cx="93" cy="20" rx="5" ry="4.5" fill="#FFB87A"/>
          <ellipse cx="75" cy="20" rx="3" ry="2.8" fill="#FFB0C8"/>
          <ellipse cx="84" cy="18" rx="3.3" ry="3"  fill="#FFB0C8"/>
          <ellipse cx="93" cy="20" rx="3" ry="2.8" fill="#FFB0C8"/>
          <ellipse cx="84" cy="28" rx="5" ry="4" fill="#FFB0C8"/>
        </g>
      </svg>
    </div>
    <div style="background:#fff;border-radius:14px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.08);animation:fadeIn .4s">
      <h2 style="font-size:1.15rem;font-weight:800;color:#111;margin-bottom:1.25rem;text-align:center">Chào mừng trở lại!</h2>
      <?php if($error): ?>
      <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;color:#991b1b"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" autocomplete="off">
        <div style="margin-bottom:.9rem">
          <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars(isset($_POST['email'])?$_POST['email']:'') ?>" required placeholder="your@email.com" class="form-input" autocomplete="off">
        </div>
        <div style="margin-bottom:1.1rem;position:relative">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem">
            <label style="display:block;font-weight:600;font-size:.82rem;color:#333">Mật khẩu</label>
            <a href="<?= APP_URL ?>/auth/forgot-password" style="font-size:.75rem;color:var(--red);text-decoration:none;font-weight:600">Quên mật khẩu?</a>
          </div>
          <input type="password" id="pw" name="password" required placeholder="••••••••" class="form-input" style="padding-right:3rem" autocomplete="new-password">
          <button type="button" id="pwToggle" onclick="togglePw()" style="position:absolute;right:.75rem;bottom:.6rem;background:none;border:none;cursor:pointer;color:#888"><i class="fa-solid fa-eye" id="pwEyeIco"></i></button>
        </div>
        <button type="submit" class="btn-red" style="width:100%;padding:.65rem;font-size:.95rem">Đăng nhập</button>
      </form>
      <?php if(GOOGLE_CLIENT_ID): ?>
      <div style="display:flex;align-items:center;gap:.6rem;margin:.9rem 0 .75rem">
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
        <span style="font-size:.75rem;color:#aaa;white-space:nowrap">hoặc</span>
        <div style="flex:1;height:1px;background:#e5e7eb"></div>
      </div>
      <a href="<?= APP_URL ?>/auth/google-login" style="display:flex;align-items:center;justify-content:center;gap:.6rem;width:100%;padding:.6rem;border:1.5px solid #dadce0;border-radius:8px;background:#fff;text-decoration:none;font-size:.88rem;font-weight:600;color:#3c4043;transition:box-shadow .15s" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.12)'" onmouseout="this.style.boxShadow='none'">
        <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.08 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.96 2.31-8.16 2.31-6.26 0-11.57-3.59-13.46-8.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
        Đăng nhập bằng Google
      </a>
      <?php endif; ?>
      <div style="text-align:center;margin-top:.9rem;font-size:.82rem;color:#999">
        Chưa có tài khoản? <a href="<?= APP_URL ?>/auth/register" style="color:var(--red);font-weight:600;text-decoration:none">Đăng ký ngay</a>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var svg   = document.getElementById('catSvg');
  var pawL  = document.getElementById('catPawL');
  var pawR  = document.getElementById('catPawR');
  var eyes  = document.getElementById('catEyes');
  var eyesC = document.getElementById('catEyesClosed');
  var pw    = document.getElementById('pw');
  var shown = false;

  // paw up = translateY(-48) covers eyes; paw default = translateY(48) below face
  function setPaws(up){
    var y = up ? '0' : '48';
    pawL.setAttribute('transform','translate(0,'+y+')');
    pawR.setAttribute('transform','translate(0,'+y+')');
  }
  function setEyes(open){
    eyes.style.opacity  = open ? '1' : '0';
    eyesC.style.opacity = open ? '0' : '1';
  }
  function setState(s){
    svg.className = 'state-' + s;
    if(s === 'watch'){
      setPaws(false); setEyes(true);
    } else if(s === 'cover'){
      setPaws(true);  setEyes(false);
    } else if(s === 'peek'){
      setPaws(false); setEyes(true);
    } else {
      setPaws(false); setEyes(true);
    }
  }

  var email = document.querySelector('input[name="email"]');
  if(email) email.addEventListener('focus', function(){ setState('watch'); });

  pw.addEventListener('focus', function(){ setState(shown ? 'peek' : 'cover'); });
  pw.addEventListener('input', function(){ setState(shown ? 'peek' : 'cover'); });
  pw.addEventListener('blur',  function(){ setTimeout(function(){ setState('watch'); }, 180); });

  window.togglePw = function(){
    shown = !shown;
    pw.type = shown ? 'text' : 'password';
    document.getElementById('pwEyeIco').className = shown ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    setState(shown ? 'peek' : 'cover');
  };

  setState('watch');
})();
</script>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
