</div><!-- end main-wrap -->

<!-- ══ IMAGE SEARCH MODAL (global, shared across admin pages) ══ -->
<div id="ims-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:10000;align-items:center;justify-content:center;backdrop-filter:blur(4px)" onclick="if(event.target===this)imsClose()">
  <div style="background:#141414;border:1px solid #2a2a2a;border-radius:16px;width:min(780px,95vw);max-height:86vh;display:flex;flex-direction:column;animation:imsIn .22s cubic-bezier(.34,1.56,.64,1);box-shadow:0 32px 80px rgba(0,0,0,.6)">
    <!-- Header -->
    <div style="padding:.85rem 1rem .7rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;gap:.55rem;flex-shrink:0">
      <i class="fa-solid fa-magnifying-glass" style="color:var(--red);font-size:.85rem"></i>
      <span style="color:#fff;font-weight:700;font-size:.875rem;flex:1">Tìm ảnh sản phẩm</span>
      <span id="ims-count" style="font-size:.68rem;color:#444"></span>
      <button onclick="imsClose()" style="background:none;border:none;color:#444;cursor:pointer;font-size:.9rem;padding:.2rem .3rem"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <!-- Search bar -->
    <div style="padding:.65rem 1rem;border-bottom:1px solid #1a1a1a;display:flex;gap:.4rem;flex-shrink:0">
      <input id="ims-q" type="text" class="form-inp" placeholder="Tên sản phẩm..." style="flex:1;font-size:.82rem"
             onkeydown="if(event.key==='Enter')imsSearch()">
      <button onclick="imsSearch()" style="background:var(--red);border:none;color:#fff;padding:.38rem .85rem;border-radius:7px;cursor:pointer;font-size:.8rem;font-weight:600;font-family:inherit;white-space:nowrap;flex-shrink:0">
        <i class="fa-solid fa-search"></i> Tìm
      </button>
    </div>
    <!-- Hint -->
    <div id="ims-hint" style="padding:.4rem 1rem;font-size:.68rem;color:#333;flex-shrink:0">
      Tìm theo: "<span id="ims-q-hint"></span> product photo white background"
    </div>
    <!-- Body -->
    <div id="ims-body" style="flex:1;overflow-y:auto;padding:.75rem 1rem">
      <!-- Idle state -->
      <div id="ims-idle" style="text-align:center;padding:3rem 1rem;color:#2a2a2a">
        <i class="fa-solid fa-image" style="font-size:2.5rem;margin-bottom:.65rem;display:block"></i>
        <div style="font-size:.8rem">Nhập tên sản phẩm và nhấn Tìm</div>
      </div>
      <!-- Loading -->
      <div id="ims-loading" style="display:none;text-align:center;padding:3rem 1rem">
        <div style="width:32px;height:32px;border:3px solid #1e1e1e;border-top-color:var(--red);border-radius:50%;animation:imsSpn .7s linear infinite;margin:0 auto .65rem"></div>
        <div style="font-size:.78rem;color:#444">Đang tìm ảnh...</div>
      </div>
      <!-- Error -->
      <div id="ims-error" style="display:none;text-align:center;padding:2rem 1rem">
        <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:1.8rem;margin-bottom:.5rem;display:block"></i>
        <div id="ims-error-msg" style="font-size:.78rem;color:#f87171;max-width:480px;margin:0 auto;line-height:1.6"></div>
      </div>
      <!-- Results grid -->
      <div id="ims-grid" style="display:none;display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem"></div>
    </div>
    <!-- Footer -->
    <div style="padding:.5rem 1rem;border-top:1px solid #1a1a1a;font-size:.65rem;color:#2a2a2a;flex-shrink:0;display:flex;align-items:center;gap:.4rem">
      <span id="ims-provider-badge"></span> Click ảnh để chọn làm ảnh sản phẩm
    </div>
  </div>
</div>

<style>
@keyframes imsIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}
@keyframes imsSpn{to{transform:rotate(360deg)}}
.ims-thumb{position:relative;aspect-ratio:1;overflow:hidden;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:border-color .15s,transform .12s;background:#1a1a1a}
.ims-thumb:hover{border-color:var(--red);transform:scale(1.03)}
.ims-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.ims-thumb .ims-ov{position:absolute;inset:0;background:rgba(0,0,0,.55);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.25rem;opacity:0;transition:opacity .15s}
.ims-thumb:hover .ims-ov{opacity:1}
.ims-thumb .ims-sel-badge{background:var(--red);color:#fff;font-size:.65rem;font-weight:700;padding:3px 9px;border-radius:5px}
.ims-thumb .ims-src{font-size:.58rem;color:rgba(255,255,255,.65);max-width:90%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ims-thumb.ims-selected{border-color:#22c55e}
.ims-thumb.ims-loading{opacity:.5;pointer-events:none}
</style>

<script>
// Expose config to JS
window.APP_URL=window.APP_URL||'<?= defined("APP_URL") ? APP_URL : "" ?>';

var _imsCb=null;

function imsOpen(cb, defaultQuery){
  _imsCb=cb;
  document.getElementById('ims-modal').style.display='flex';
  var q=document.getElementById('ims-q');
  q.value=defaultQuery||'';
  document.getElementById('ims-q-hint').textContent=defaultQuery||'...';
  imsShowState('idle');
  if(defaultQuery) setTimeout(imsSearch,60);
  else q.focus();
}
function imsClose(){
  document.getElementById('ims-modal').style.display='none';
  _imsCb=null;
}
function imsShowState(state){
  ['idle','loading','error','grid'].forEach(function(s){
    var el=document.getElementById('ims-'+s);
    if(el) el.style.display=(s===state)?((s==='grid')?'grid':'block'):'none';
  });
  if(state!=='grid') document.getElementById('ims-count').textContent='';
}

// Fetch image URL as base64 blob (browser-side, bypasses server CORS/CF)
function imsFetchB64(url, onOk, onErr){
  fetch(url)
    .then(function(r){ return r.blob(); })
    .then(function(blob){
      var reader=new FileReader();
      reader.onload=function(e){ onOk(e.target.result, blob.type||'image/jpeg'); };
      reader.readAsDataURL(blob);
    })
    .catch(onErr);
}

function imsRenderGrid(images, provider){
  imsShowState('grid');
  document.getElementById('ims-count').textContent=images.length+' ảnh';
  var badge=document.getElementById('ims-provider-badge');
  if(badge) badge.innerHTML=
    provider==='pexels'  ? '<i class="fa-solid fa-image" style="color:#05a081"></i> Pexels'  :
    provider==='pixabay' ? '<i class="fa-solid fa-image" style="color:#23a361"></i> Pixabay' :
    provider==='bing'    ? '<i class="fa-brands fa-microsoft" style="color:#00a4ef"></i> Bing' :
                           '<i class="fa-brands fa-google" style="color:#4285f4"></i> Google';
  var grid=document.getElementById('ims-grid');
  grid.innerHTML='';
  images.forEach(function(img){
    var el=document.createElement('div');
    el.className='ims-thumb';
    el.title=img.title||'';
    el.innerHTML='<img src="'+img.thumb+'" loading="lazy" onerror="this.closest(\'.ims-thumb\').style.display=\'none\'">'
      +'<div class="ims-ov">'
      +'<span class="ims-sel-badge"><i class="fa-solid fa-check"></i> Chọn</span>'
      +'<span class="ims-src">'+(img.source||'')+'</span>'
      +'</div>';
    el.addEventListener('click',function(){
      document.querySelectorAll('.ims-thumb').forEach(function(x){x.classList.remove('ims-selected');});
      el.classList.add('ims-loading','ims-selected');
      if(_imsCb) _imsCb(img.url, img.thumb, img.title, el, null);
    });
    grid.appendChild(el);
  });
}

async function imsSearch(){
  var q=document.getElementById('ims-q').value.trim();
  if(!q){ document.getElementById('ims-q').focus(); return; }
  document.getElementById('ims-q-hint').textContent=q;
  imsShowState('loading');
  try{
    var r=await fetch(window.APP_URL+'/api/ai/search-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({query:q})
    });
    var d=await r.json();
    if(d.success&&d.images&&d.images.length){
      imsRenderGrid(d.images, d.provider||'');
    } else {
      imsShowState('error');
      document.getElementById('ims-error-msg').textContent=d.message||'Không tìm thấy ảnh.';
    }
  }catch(e){
    imsShowState('error');
    document.getElementById('ims-error-msg').textContent='Lỗi: '+e.message;
  }
}
</script>

<style>
#center-notif{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .25s}
#center-notif.show{opacity:1;pointer-events:auto}
#center-notif .cn-box{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:18px;padding:2.2rem 2.5rem;text-align:center;transform:scale(.88);transition:transform .25s cubic-bezier(.34,1.56,.64,1);min-width:260px;max-width:360px;box-shadow:0 24px 60px rgba(0,0,0,.6)}
#center-notif.show .cn-box{transform:scale(1)}
</style>
<div id="center-notif">
  <div class="cn-box">
    <div id="cn-icon" style="font-size:2.8rem;margin-bottom:.75rem"></div>
    <div id="cn-title" style="font-size:1.05rem;font-weight:800;color:#fff;margin-bottom:.35rem"></div>
    <div id="cn-sub" style="font-size:.8rem;color:#555;line-height:1.6"></div>
  </div>
</div>

<script>
window.addEventListener('load',function(){var l=document.getElementById('pg-loader');if(l){l.classList.add('hidden');setTimeout(function(){if(l.parentNode)l.parentNode.removeChild(l);},500);}});
function showToast(msg,type){type=type||'ok';var c=document.getElementById('toast-c');var t=document.createElement('div');t.className='toast '+type;t.innerHTML=(type==='ok'?'✅':'❌')+' '+msg;c.appendChild(t);setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';},3000);setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);},3300);}
function showCenterNotif(icon, title, sub, ms){
  ms = ms || 2800;
  document.getElementById('cn-icon').innerHTML  = icon;
  document.getElementById('cn-title').textContent = title;
  document.getElementById('cn-sub').textContent   = sub || '';
  var el = document.getElementById('center-notif');
  el.classList.add('show');
  el.onclick = function(){ el.classList.remove('show'); };
  setTimeout(function(){ el.classList.remove('show'); }, ms);
}
</script>
<?php
$_flash = getFlash();
if ($_flash):
    if ($_flash['type'] === 'success_center'):
        $fMsg = addslashes(htmlspecialchars($_flash['msg'], ENT_QUOTES, 'UTF-8'));
        $fSub = addslashes(htmlspecialchars($_flash['sub'] ?? '', ENT_QUOTES, 'UTF-8'));
?>
<script>document.addEventListener('DOMContentLoaded',function(){showCenterNotif('<?= $_flash["icon"] ?? "✅" ?>','<?= $fMsg ?>','<?= $fSub ?>');});</script>
<?php   else:
        $fType = ($_flash['type'] === 'success') ? 'ok' : 'err';
        $fMsg  = addslashes(htmlspecialchars($_flash['msg'], ENT_QUOTES, 'UTF-8'));
?>
<script>document.addEventListener('DOMContentLoaded',function(){showToast('<?= $fMsg ?>','<?= $fType ?>');});</script>
<?php   endif; endif; ?>
</body></html>
