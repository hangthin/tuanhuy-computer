<?php $pageTitle='Đơn hàng'; require_once __DIR__.'/../layouts/header.php'; ?>

<style>
.acc-wrap{max-width:960px;margin:1.5rem auto;padding:0 1rem;display:grid;grid-template-columns:220px 1fr;gap:1.25rem;align-items:start}
.acc-side{background:#fff;border-radius:14px;overflow:hidden;box-shadow:var(--shadow);position:sticky;top:80px}
.acc-side-top{background:linear-gradient(135deg,var(--red),#b00000);padding:1.5rem 1rem 1.25rem;text-align:center}
.acc-avatar{width:64px;height:64px;background:rgba(255,255,255,.22);border:3px solid rgba(255,255,255,.35);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.5rem;margin:0 auto .65rem}
.acc-side-name{color:#fff;font-weight:800;font-size:.92rem;margin-bottom:.15rem}
.acc-side-email{color:rgba(255,255,255,.7);font-size:.7rem;word-break:break-all}
.acc-nav{padding:.5rem 0}
.acc-nav a{display:flex;align-items:center;gap:.65rem;padding:.6rem 1.1rem;color:#555;font-size:.83rem;transition:all var(--t);border-left:3px solid transparent}
.acc-nav a:hover{color:var(--text);background:#f9f9f9;border-left-color:#ddd}
.acc-nav a.active{color:var(--red);background:rgba(227,0,0,.05);border-left-color:var(--red);font-weight:600}
.acc-nav a i{width:16px;text-align:center;font-size:.82rem;opacity:.7}
.acc-nav a.active i{opacity:1}
.acc-nav .nav-sep{height:1px;background:#f0f0f0;margin:.35rem .8rem}
.acc-card{background:#fff;border-radius:14px;padding:1.5rem;box-shadow:var(--shadow)}

/* Order cards */
.ord-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:box-shadow .2s,border-color .2s;margin-bottom:.75rem;cursor:pointer}
.ord-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.09);border-color:#d1d5db}
.ord-card.expanded{border-color:var(--red);box-shadow:0 4px 20px rgba(227,0,0,.08)}
.ord-head{padding:.9rem 1.1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.ord-code{font-weight:800;color:var(--red);font-size:.9rem;letter-spacing:.3px;flex-shrink:0}
.ord-meta{color:#888;font-size:.76rem;display:flex;align-items:center;gap:.4rem}
.ord-meta i{opacity:.6;font-size:.7rem}
.ord-total{font-weight:800;color:var(--text);font-size:.92rem;margin-left:auto}
.ord-body{border-top:1px solid #f5f5f5;padding:.85rem 1.1rem;display:none;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
.ord-card.expanded .ord-body{display:block}
.ord-item{display:flex;align-items:center;gap:.75rem;padding:.45rem 0;border-bottom:1px solid #fafafa}
.ord-item:last-child{border-bottom:none}
.ord-item-img{width:48px;height:48px;border-radius:7px;object-fit:cover;border:1px solid #eee;flex-shrink:0;background:#f5f5f5}
.ord-item-name{flex:1;font-size:.82rem;font-weight:500;color:var(--text);line-height:1.4}
.ord-item-name span{display:block;color:#999;font-size:.73rem;font-weight:400}
.ord-item-price{font-weight:700;color:var(--red);font-size:.82rem;text-align:right;flex-shrink:0}
.ord-summary{display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;padding:.75rem 0 .3rem;border-top:1px solid #f5f5f5;margin-top:.5rem}
.ord-sum-row{display:flex;justify-content:space-between;font-size:.79rem}
.ord-sum-row.total{font-weight:800;font-size:.88rem;color:var(--red)}
.ord-sum-row .lbl{color:#888}
.ord-cancel-btn{display:inline-flex;align-items:center;gap:.35rem;background:#fff;border:1.5px solid #fca5a5;color:#ef4444;padding:.4rem .9rem;border-radius:7px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--t)}
.ord-cancel-btn:hover{background:#fef2f2}
.ord-reorder-btn{display:inline-flex;align-items:center;gap:.35rem;background:var(--red);border:none;color:#fff;padding:.4rem .9rem;border-radius:7px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--t)}
.ord-reorder-btn:hover{background:var(--red-dk)}

/* Status */
.sbadge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 10px;border-radius:99px;font-size:.7rem;font-weight:600;flex-shrink:0}
.sbadge.pending   {background:#fef9c3;color:#854d0e}
.sbadge.confirmed {background:#dbeafe;color:#1e40af}
.sbadge.processing{background:#fde8d8;color:#9a3412}
.sbadge.shipping  {background:#e0f2fe;color:#075985}
.sbadge.delivered {background:#dcfce7;color:#166534}
.sbadge.cancelled {background:#fee2e2;color:#991b1b}

.filter-tabs{display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:1rem}
.ftab{background:#fff;border:1.5px solid var(--border);color:#666;padding:.32rem .85rem;border-radius:99px;font-size:.76rem;font-weight:600;cursor:pointer;transition:all var(--t)}
.ftab:hover{border-color:#aaa;color:var(--text)}
.ftab.active{background:var(--red);border-color:var(--red);color:#fff}

@media(max-width:680px){
  .acc-wrap{grid-template-columns:1fr}
  .acc-side{position:static}
  .ord-summary{grid-template-columns:1fr}
}
</style>

<div class="acc-wrap">
  <!-- Sidebar -->
  <aside class="acc-side">
    <div class="acc-side-top">
      <div class="acc-avatar"><?= strtoupper(mb_substr($_SESSION['user_name']??'U',0,1,'UTF-8')) ?></div>
      <div class="acc-side-name"><?= htmlspecialchars($_SESSION['user_name']??'') ?></div>
      <div class="acc-side-email"><?= htmlspecialchars($_SESSION['user_email']??'') ?></div>
    </div>
    <nav class="acc-nav">
      <a href="<?= APP_URL ?>/account"><i class="fa-solid fa-user"></i>Thông tin cá nhân</a>
      <a href="<?= APP_URL ?>/account/orders" class="active"><i class="fa-solid fa-box-open"></i>Đơn hàng của tôi</a>
      <div class="nav-sep"></div>
      <a href="<?= APP_URL ?>/auth/logout" style="color:#ef4444" onclick="return confirm('Đăng xuất?')"><i class="fa-solid fa-arrow-right-from-bracket"></i>Đăng xuất</a>
    </nav>
  </aside>

  <!-- Main -->
  <div>
    <div class="acc-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <h2 style="font-weight:800;font-size:1rem;display:flex;align-items:center;gap:.5rem">
          <i class="fa-solid fa-box-open" style="color:var(--red);font-size:.9rem"></i>
          Lịch sử đơn hàng
          <?php if(!empty($orders)): ?>
          <span style="background:#f3f4f6;color:#666;padding:1px 8px;border-radius:99px;font-size:.72rem;font-weight:600"><?= count($orders) ?></span>
          <?php endif; ?>
        </h2>
        <a href="<?= APP_URL ?>/products" style="display:inline-flex;align-items:center;gap:.35rem;color:var(--red);font-size:.78rem;font-weight:600">
          <i class="fa-solid fa-cart-shopping"></i> Mua thêm
        </a>
      </div>

      <?php if(empty($orders)): ?>
      <div style="text-align:center;padding:3.5rem 1rem">
        <div style="width:72px;height:72px;background:#f9f9f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
          <i class="fa-solid fa-box-open" style="font-size:1.8rem;color:#ccc"></i>
        </div>
        <div style="font-weight:700;font-size:.95rem;margin-bottom:.4rem;color:#333">Chưa có đơn hàng nào</div>
        <div style="color:#999;font-size:.82rem;margin-bottom:1.25rem">Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!</div>
        <a href="<?= APP_URL ?>/products" class="btn-red" style="text-decoration:none">Mua sắm ngay</a>
      </div>
      <?php else: ?>

      <!-- Filter tabs -->
      <?php
      $statusMap = array(
        'all'        => array('Tất cả', '', 'fa-list'),
        'pending'    => array('Chờ xác nhận', 'pending', 'fa-clock'),
        'confirmed'  => array('Đã xác nhận', 'confirmed', 'fa-circle-check'),
        'processing' => array('Đang xử lý', 'processing', 'fa-gear'),
        'shipping'   => array('Đang giao', 'shipping', 'fa-truck'),
        'delivered'  => array('Đã giao', 'delivered', 'fa-house-circle-check'),
        'cancelled'  => array('Đã hủy', 'cancelled', 'fa-ban'),
      );
      $counts = array('all'=>count($orders));
      foreach($orders as $o){ $counts[$o['status']] = ($counts[$o['status']]??0)+1; }
      ?>
      <div class="filter-tabs" id="ord-filters">
        <?php foreach($statusMap as $k=>$v): if(($counts[$k]??0)===0&&$k!=='all') continue; ?>
        <button class="ftab <?= $k==='all'?'active':'' ?>" data-filter="<?= $k ?>" onclick="ordFilter('<?= $k ?>',this)">
          <i class="fa-solid <?= $v[2] ?>"></i> <?= $v[0] ?>
          <?php if(($counts[$k]??0)>0&&$k!=='all'): ?><span style="background:rgba(255,255,255,.3);padding:0 5px;border-radius:99px;font-size:.65rem;margin-left:2px"><?= $counts[$k] ?></span><?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Order list -->
      <div id="ord-list">
      <?php
      $db = Database::getInstance();
      $paymentLabels = array('cod'=>'COD','bank'=>'Chuyển khoản','momo'=>'MoMo','vnpay'=>'VNPay');
      foreach($orders as $o):
        $items = $db->fetchAll(
          "SELECT od.product_name, od.quantity, od.price AS unit_price, p.image
           FROM order_details od LEFT JOIN products p ON od.product_id=p.id
           WHERE od.order_id=?", array($o['id'])
        );
        $s = $o['status'];
        $sLabel = $statusMap[$s][0] ?? $s;
        $sIcon = array('pending'=>'fa-clock','confirmed'=>'fa-circle-check','processing'=>'fa-gear','shipping'=>'fa-truck','delivered'=>'fa-house-circle-check','cancelled'=>'fa-ban')[$s] ?? 'fa-circle';
      ?>
      <div class="ord-card" data-status="<?= $s ?>" onclick="ordToggle(this)">
        <div class="ord-head">
          <div>
            <div class="ord-code">#<?= $o['order_code'] ?></div>
            <div class="ord-meta" style="margin-top:.18rem">
              <i class="fa-regular fa-calendar"></i><?= date('d/m/Y', strtotime($o['created_at'])) ?>
              <span style="color:#ddd">·</span>
              <i class="fa-solid fa-box"></i><?= $o['item_count'] ?> sản phẩm
              <span style="color:#ddd">·</span>
              <i class="fa-solid fa-credit-card"></i><?= $paymentLabels[strtolower($o['payment_method']??'')] ?? strtoupper($o['payment_method']) ?>
            </div>
          </div>
          <span class="sbadge <?= $s ?>"><i class="fa-solid <?= $sIcon ?>"></i><?= $sLabel ?></span>
          <div class="ord-total"><?= formatPrice($o['total']) ?></div>
          <i class="fa-solid fa-chevron-down" style="color:#ccc;font-size:.75rem;transition:transform .2s" id="chev-<?= $o['id'] ?>"></i>
        </div>

        <div class="ord-body">
          <!-- Sản phẩm -->
          <?php foreach($items as $it): ?>
          <div class="ord-item">
            <?php if(!empty($it['image'])): ?>
            <img class="ord-item-img" src="<?= UPLOAD_URL.htmlspecialchars($it['image']) ?>" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect fill=%22%23f5f5f5%22 width=%2248%22 height=%2248%22/><text x=%2224%22 y=%2230%22 text-anchor=%22middle%22 font-size=%2218%22>📦</text></svg>'">
            <?php else: ?>
            <div class="ord-item-img" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem">📦</div>
            <?php endif; ?>
            <div class="ord-item-name">
              <?= htmlspecialchars($it['product_name']) ?>
              <span>x<?= $it['quantity'] ?></span>
            </div>
            <div class="ord-item-price"><?= formatPrice($it['unit_price'] * $it['quantity']) ?></div>
          </div>
          <?php endforeach; ?>

          <!-- Tóm tắt giá -->
          <div class="ord-summary">
            <div>
              <div class="ord-sum-row"><span class="lbl">Tạm tính</span><span><?= formatPrice($o['subtotal']??$o['total']) ?></span></div>
              <?php if(($o['shipping_fee']??0)>0): ?>
              <div class="ord-sum-row"><span class="lbl">Phí vận chuyển</span><span><?= formatPrice($o['shipping_fee']) ?></span></div>
              <?php else: ?>
              <div class="ord-sum-row"><span class="lbl">Phí vận chuyển</span><span style="color:#22c55e;font-weight:600">Miễn phí</span></div>
              <?php endif; ?>
              <?php if(($o['discount']??0)>0): ?>
              <div class="ord-sum-row"><span class="lbl">Giảm giá</span><span style="color:#22c55e">-<?= formatPrice($o['discount']) ?></span></div>
              <?php endif; ?>
              <div class="ord-sum-row total" style="margin-top:.4rem;padding-top:.4rem;border-top:1px dashed #eee"><span>Tổng cộng</span><span><?= formatPrice($o['total']) ?></span></div>
            </div>
            <div>
              <?php if(!empty($o['address'])): ?>
              <div class="ord-sum-row" style="align-items:flex-start">
                <span class="lbl" style="flex-shrink:0">Địa chỉ</span>
                <span style="text-align:right;font-size:.76rem"><?= htmlspecialchars(trim($o['address'].', '.($o['district']??'').', '.($o['city']??''), ', ')) ?></span>
              </div>
              <?php endif; ?>
              <?php if(!empty($o['notes'])): ?>
              <div class="ord-sum-row" style="align-items:flex-start;margin-top:.3rem">
                <span class="lbl" style="flex-shrink:0">Ghi chú</span>
                <span style="text-align:right;font-size:.76rem;color:#888;font-style:italic"><?= htmlspecialchars($o['notes']) ?></span>
              </div>
              <?php endif; ?>
              <!-- Actions -->
              <div style="display:flex;gap:.5rem;margin-top:.75rem;justify-content:flex-end">
                <?php if($s==='pending'): ?>
                <button class="ord-cancel-btn" onclick="event.stopPropagation();ordCancel(<?= $o['id'] ?>,'<?= $o['order_code'] ?>')">
                  <i class="fa-solid fa-xmark"></i> Hủy đơn
                </button>
                <?php endif; ?>
                <?php if($s==='delivered'): ?>
                <a href="<?= APP_URL ?>/products" class="ord-reorder-btn" onclick="event.stopPropagation()">
                  <i class="fa-solid fa-rotate-right"></i> Mua lại
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>

      <div id="ord-empty" style="display:none;text-align:center;padding:2.5rem;color:#aaa">
        <i class="fa-solid fa-filter" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>
        <div style="font-size:.85rem">Không có đơn hàng nào trong trạng thái này</div>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function ordToggle(card){
  var isOpen=card.classList.contains('expanded');
  document.querySelectorAll('.ord-card.expanded').forEach(function(c){
    c.classList.remove('expanded');
    var ch=c.querySelector('[id^="chev-"]');
    if(ch) ch.style.transform='';
  });
  if(!isOpen){
    card.classList.add('expanded');
    var id=card.querySelector('[id^="chev-"]');
    if(id) id.style.transform='rotate(180deg)';
  }
}
function ordFilter(key, btn){
  document.querySelectorAll('.ftab').forEach(function(b){b.classList.remove('active');});
  btn.classList.add('active');
  var cards=document.querySelectorAll('.ord-card');
  var shown=0;
  cards.forEach(function(c){
    if(key==='all'||c.dataset.status===key){ c.style.display=''; shown++; }
    else { c.style.display='none'; c.classList.remove('expanded'); }
  });
  document.getElementById('ord-empty').style.display=shown===0?'block':'none';
}
function ordCancel(id, code){
  if(!confirm('Hủy đơn hàng #'+code+'? Hành động này không thể hoàn tác.')) return;
  fetch('<?= APP_URL ?>/account/cancel-order', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({order_id:id})
  }).then(function(r){return r.json();}).then(function(d){
    if(d.success) location.reload();
    else alert(d.message||'Không thể hủy đơn hàng này');
  }).catch(function(){alert('Lỗi kết nối');});
}
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
