<?php $pageTitle='Quên mật khẩu'; require_once __DIR__.'/../layouts/header.php'; ?>

<?php $flash=getFlash(); ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:#f5f5f5">
  <div style="width:100%;max-width:420px">

    <!-- Logo -->
    <div style="text-align:center;margin-bottom:1.25rem">
      <div style="display:inline-flex;align-items:center;gap:.65rem">
        <div style="width:46px;height:46px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.2rem">TH</div>
        <div><div style="font-weight:800;font-size:1rem;color:#111">TUẤN HUY COMPUTER</div><div style="font-size:.62rem;color:var(--red);letter-spacing:2px;font-weight:700">ĐẶT LẠI MẬT KHẨU</div></div>
      </div>
    </div>

    <div style="background:#fff;border-radius:14px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.08);animation:fadeIn .4s">

      <!-- Flash message -->
      <?php if($flash): ?>
      <div style="background:<?= $flash['type']==='success'?'#f0fdf4':'#fee2e2' ?>;border-left:4px solid <?= $flash['type']==='success'?'#22c55e':'#ef4444' ?>;padding:.65rem .9rem;border-radius:7px;margin-bottom:1rem;font-size:.85rem;color:<?= $flash['type']==='success'?'#166534':'#991b1b' ?>">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- BƯỚC 1: Nhập email -->
      <div id="fp-step1">
        <h2 style="font-size:1.05rem;font-weight:800;color:#111;margin-bottom:.4rem">Quên mật khẩu?</h2>
        <p style="font-size:.83rem;color:#888;margin-bottom:1.25rem">Nhập email đăng ký để nhận mã OTP đặt lại mật khẩu.</p>

        <div style="margin-bottom:1rem">
          <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Email</label>
          <input type="email" id="fp-email" placeholder="your@email.com" class="form-input" autocomplete="email" required>
        </div>
        <div id="fp-err1" style="display:none;background:#fee2e2;border-left:4px solid #ef4444;padding:.55rem .8rem;border-radius:7px;font-size:.82rem;color:#991b1b;margin-bottom:.9rem"></div>
        <button id="fp-send-btn" onclick="fpSendOtp()" class="btn-red" style="width:100%;padding:.65rem;font-size:.95rem;border:none;cursor:pointer">
          <i class="fa-solid fa-paper-plane"></i> Gửi mã OTP
        </button>
      </div>

      <!-- BƯỚC 2: Nhập OTP + mật khẩu mới (ẩn ban đầu) -->
      <div id="fp-step2" style="display:none">
        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1rem;padding:.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px">
          <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:1.1rem;flex-shrink:0"></i>
          <div>
            <div style="font-size:.82rem;font-weight:700;color:#166534">OTP đã được gửi!</div>
            <div style="font-size:.76rem;color:#4ade80" id="fp-sent-to"></div>
          </div>
        </div>

        <form method="POST" action="<?= APP_URL ?>/auth/reset-password" onsubmit="return fpValidate(this)">
          <input type="hidden" name="email" id="fp-email-hidden">

          <!-- OTP boxes -->
          <div style="margin-bottom:1.1rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem">
              <label style="font-weight:600;font-size:.82rem;color:#333">Mã OTP (6 chữ số)</label>
              <span id="fp-countdown" style="font-size:.75rem;color:#ef4444;font-weight:700"></span>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:center" id="fp-otp-boxes">
              <?php for($i=0;$i<6;$i++): ?>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                     class="fp-otp-box"
                     style="width:44px;height:52px;text-align:center;font-size:1.4rem;font-weight:800;border:2px solid #e5e7eb;border-radius:9px;outline:none;font-family:monospace;color:#111;transition:border-color .15s;background:#fafafa"
                     oninput="fpOtpInput(this,<?= $i ?>)"
                     onkeydown="fpOtpKeydown(event,this,<?= $i ?>)"
                     onpaste="fpOtpPaste(event,<?= $i ?>)">
              <?php endfor; ?>
            </div>
            <input type="hidden" name="otp" id="fp-otp-val">
          </div>

          <!-- Mật khẩu mới -->
          <div style="margin-bottom:.85rem">
            <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Mật khẩu mới</label>
            <input type="password" name="new_password" id="fp-pw-new" placeholder="Tối thiểu 6 ký tự" class="form-input" autocomplete="new-password" required>
          </div>
          <div style="margin-bottom:1.1rem">
            <label style="display:block;font-weight:600;font-size:.82rem;color:#333;margin-bottom:.3rem">Nhập lại mật khẩu</label>
            <input type="password" name="confirm_password" id="fp-pw-cf" placeholder="Nhập lại" class="form-input" autocomplete="new-password" required>
          </div>

          <div id="fp-err2" style="display:none;background:#fee2e2;border-left:4px solid #ef4444;padding:.55rem .8rem;border-radius:7px;font-size:.82rem;color:#991b1b;margin-bottom:.9rem"></div>

          <button type="submit" class="btn-red" style="width:100%;padding:.65rem;font-size:.95rem;border:none;cursor:pointer">
            <i class="fa-solid fa-key"></i> Đặt lại mật khẩu
          </button>
        </form>

        <div style="text-align:center;margin-top:.85rem;font-size:.78rem;color:#999">
          Không nhận được email?
          <button id="fp-resend-btn" onclick="fpResend()" style="background:none;border:none;color:var(--red);font-weight:700;cursor:pointer;font-size:.78rem;padding:0">Gửi lại</button>
          <span id="fp-resend-wait" style="color:#aaa"></span>
        </div>
      </div>

      <div style="text-align:center;margin-top:1rem;font-size:.82rem;color:#999">
        <a href="<?= APP_URL ?>/auth/login" style="color:var(--red);font-weight:600;text-decoration:none">← Quay lại đăng nhập</a>
      </div>
    </div>
  </div>
</div>

<script>
var fpEmail='';
var fpTimer=null;
var fpResendTimer=null;
var APP_URL='<?= APP_URL ?>';

function fpSendOtp(){
  var email=document.getElementById('fp-email').value.trim();
  var errEl=document.getElementById('fp-err1');
  var btn=document.getElementById('fp-send-btn');
  if(!email||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    errEl.textContent='Vui lòng nhập email hợp lệ';errEl.style.display='block';return;
  }
  errEl.style.display='none';
  btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
  fetch(APP_URL+'/auth/send-otp',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:email})})
  .then(function(r){return r.json();})
  .then(function(d){
    btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Gửi mã OTP';
    if(d.success){
      fpEmail=email;
      document.getElementById('fp-email-hidden').value=email;
      document.getElementById('fp-sent-to').textContent='Mã OTP gửi đến: '+email;
      document.getElementById('fp-step1').style.display='none';
      document.getElementById('fp-step2').style.display='block';
      document.querySelectorAll('.fp-otp-box')[0].focus();
      fpStartCountdown(600);
      fpStartResendWait(60);
    } else {
      errEl.textContent=d.message||'Có lỗi xảy ra';errEl.style.display='block';
    }
  })
  .catch(function(){
    btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Gửi mã OTP';
    errEl.textContent='Lỗi kết nối, vui lòng thử lại';errEl.style.display='block';
  });
}

function fpStartCountdown(secs){
  clearInterval(fpTimer);
  var el=document.getElementById('fp-countdown');
  function tick(){
    if(secs<=0){el.textContent='OTP đã hết hạn';clearInterval(fpTimer);return;}
    var m=Math.floor(secs/60);var s=secs%60;
    el.textContent=m+':'+(s<10?'0':'')+s;secs--;
  }
  tick();fpTimer=setInterval(tick,1000);
}

function fpStartResendWait(secs){
  clearInterval(fpResendTimer);
  var btn=document.getElementById('fp-resend-btn');
  var wait=document.getElementById('fp-resend-wait');
  btn.style.display='none';
  function tick(){
    if(secs<=0){btn.style.display='';wait.textContent='';clearInterval(fpResendTimer);return;}
    wait.textContent='('+secs+'s)';secs--;
  }
  tick();fpResendTimer=setInterval(tick,1000);
}

function fpResend(){
  if(!fpEmail) return;
  var btn=document.getElementById('fp-resend-btn');
  btn.style.display='none';
  fetch(APP_URL+'/auth/send-otp',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:fpEmail})})
  .then(function(r){return r.json();})
  .then(function(d){
    if(d.success){
      fpStartCountdown(600);fpStartResendWait(60);
      document.getElementById('fp-err2').style.display='none';
    } else {
      document.getElementById('fp-err2').textContent=d.message||'Có lỗi xảy ra';
      document.getElementById('fp-err2').style.display='block';
      btn.style.display='';
    }
  })
  .catch(function(){btn.style.display='';});
}

function fpOtpInput(el,idx){
  el.value=el.value.replace(/[^0-9]/g,'');
  var boxes=document.querySelectorAll('.fp-otp-box');
  if(el.value&&idx<5) boxes[idx+1].focus();
  fpCollectOtp();
  el.style.borderColor=el.value?'var(--red)':'#e5e7eb';
}
function fpOtpKeydown(e,el,idx){
  var boxes=document.querySelectorAll('.fp-otp-box');
  if(e.key==='Backspace'&&!el.value&&idx>0){boxes[idx-1].focus();boxes[idx-1].value='';fpCollectOtp();}
  if(e.key==='ArrowLeft'&&idx>0) boxes[idx-1].focus();
  if(e.key==='ArrowRight'&&idx<5) boxes[idx+1].focus();
}
function fpOtpPaste(e,idx){
  e.preventDefault();
  var txt=(e.clipboardData||window.clipboardData).getData('text').replace(/[^0-9]/g,'').slice(0,6);
  var boxes=document.querySelectorAll('.fp-otp-box');
  for(var i=0;i<txt.length&&i+idx<6;i++){
    boxes[i+idx].value=txt[i];
    boxes[i+idx].style.borderColor='var(--red)';
  }
  if(idx+txt.length<=5) boxes[idx+txt.length].focus();
  fpCollectOtp();
}
function fpCollectOtp(){
  var val='';
  document.querySelectorAll('.fp-otp-box').forEach(function(b){val+=b.value;});
  document.getElementById('fp-otp-val').value=val;
}
function fpValidate(f){
  var otp=document.getElementById('fp-otp-val').value;
  var pw=document.getElementById('fp-pw-new').value;
  var cf=document.getElementById('fp-pw-cf').value;
  var err=document.getElementById('fp-err2');
  if(otp.length!==6){err.textContent='Vui lòng nhập đủ 6 số OTP';err.style.display='block';return false;}
  if(pw.length<6){err.textContent='Mật khẩu mới ít nhất 6 ký tự';err.style.display='block';return false;}
  if(pw!==cf){err.textContent='Mật khẩu nhập lại không khớp';err.style.display='block';return false;}
  err.style.display='none';return true;
}
// Trigger send if Enter pressed in email field
document.getElementById('fp-email').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();fpSendOtp();}});
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
