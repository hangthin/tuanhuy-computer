<?php require_once __DIR__.'/layout_top.php'; ?>
<style>
.inv-kpi{background:#1a1a1a;border:1px solid #222;border-radius:10px;padding:.9rem 1rem;display:flex;flex-direction:column;gap:.3rem}
.inv-kpi-lbl{font-size:.65rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.inv-kpi-val{font-size:1.45rem;font-weight:900;line-height:1}
.inv-filter{background:#1a1a1a;border:1px solid #222;border-radius:10px;padding:.7rem .9rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.75rem}
.inv-tab{padding:.2rem .65rem;border-radius:99px;font-size:.72rem;font-weight:600;cursor:pointer;border:1px solid #2a2a2a;color:#555;text-decoration:none;transition:all .15s}
.inv-tab:hover,.inv-tab.active{background:#222;color:#ddd;border-color:#333}
.inv-tab.tab-out.active{background:rgba(248,113,113,.12);color:#f87171;border-color:rgba(248,113,113,.3)}
.inv-tab.tab-low.active{background:rgba(251,191,36,.12);color:#fbbf24;border-color:rgba(251,191,36,.3)}
.inv-tab.tab-ok.active{background:rgba(74,222,128,.12);color:#4ade80;border-color:rgba(74,222,128,.3)}
.inv-row-out td{background:rgba(239,68,68,.05)!important}
.inv-row-low td{background:rgba(251,191,36,.05)!important}
.inv-row-ok  td{background:transparent}
.inv-cell{cursor:pointer;border-radius:5px;padding:.15rem .35rem;transition:background .15s;display:inline-block;min-width:38px;text-align:center}
.inv-cell:hover{background:#222}
.inv-cell.editing{background:#111;outline:none}
.inv-cell input{background:transparent;border:none;outline:none;color:inherit;font-size:inherit;font-weight:inherit;width:60px;text-align:center}
#bulk-bar{position:sticky;bottom:0;background:#1a1a1a;border-top:1px solid #2a2a2a;padding:.6rem 1rem;display:none;align-items:center;gap:.75rem;flex-wrap:wrap;z-index:10;border-radius:0 0 10px 10px}
.hist-row{display:grid;grid-template-columns:130px 120px 1fr 1fr 1fr;gap:.4rem;padding:.35rem .6rem;border-radius:6px;font-size:.74rem;transition:background .12s}
.hist-row:hover{background:rgba(255,255,255,.025)}
.hist-head{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#333;padding:.25rem .6rem}
</style>

<?php
$s   = $summary ?? array();
$totalSku  = (int)($s['total_sku']  ?? 0);
$outCnt    = (int)($s['out_cnt']    ?? 0);
$lowCnt    = (int)($s['low_cnt']    ?? 0);
$totalVal  = (float)($s['total_value'] ?? 0);
?>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-bottom:.75rem">
  <div class="inv-kpi">
    <div class="inv-kpi-lbl">Tổng SKU</div>
    <div class="inv-kpi-val" style="color:#ddd"><?= number_format($totalSku) ?></div>
    <div style="font-size:.7rem;color:#444">Sản phẩm đang theo dõi</div>
  </div>
  <div class="inv-kpi" style="<?= $outCnt > 0 ? 'border-color:rgba(248,113,113,.25)' : '' ?>">
    <div class="inv-kpi-lbl">Hết hàng</div>
    <div class="inv-kpi-val" style="color:<?= $outCnt > 0 ? '#f87171' : '#555' ?>"><?= $outCnt ?></div>
    <div style="font-size:.7rem;color:#444">Tồn kho = 0</div>
  </div>
  <div class="inv-kpi" style="<?= $lowCnt > 0 ? 'border-color:rgba(251,191,36,.25)' : '' ?>">
    <div class="inv-kpi-lbl">Sắp hết</div>
    <div class="inv-kpi-val" style="color:<?= $lowCnt > 0 ? '#fbbf24' : '#555' ?>"><?= $lowCnt ?></div>
    <div style="font-size:.7rem;color:#444">Tồn ≤ mức tối thiểu</div>
  </div>
  <div class="inv-kpi">
    <div class="inv-kpi-lbl">Giá trị kho</div>
    <div class="inv-kpi-val" style="color:#4ade80;font-size:1.15rem"><?= formatPrice($totalVal) ?></div>
    <div style="font-size:.7rem;color:#444">Tổng tồn × đơn giá</div>
  </div>
</div>

<!-- Filter Bar -->
<div class="inv-filter">
  <form method="GET" style="display:contents">
    <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên / SKU…"
      class="form-inp" style="width:200px;padding:.3rem .55rem;font-size:.78rem">
    <select name="cat" class="form-inp" style="width:160px;padding:.3rem .55rem;font-size:.78rem;background:#111;color:#888;border-color:#2a2a2a">
      <option value="">Tất cả danh mục</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <a href="?<?= http_build_query(array_merge($_GET, array('status'=>''))) ?>" class="inv-tab <?= $statusF==='' ? 'active' : '' ?>">Tất cả</a>
    <a href="?<?= http_build_query(array_merge($_GET, array('status'=>'out'))) ?>" class="inv-tab tab-out <?= $statusF==='out' ? 'active' : '' ?>">🔴 Hết hàng</a>
    <a href="?<?= http_build_query(array_merge($_GET, array('status'=>'low'))) ?>" class="inv-tab tab-low <?= $statusF==='low' ? 'active' : '' ?>">🟡 Sắp hết</a>
    <a href="?<?= http_build_query(array_merge($_GET, array('status'=>'ok')))  ?>" class="inv-tab tab-ok  <?= $statusF==='ok'  ? 'active' : '' ?>">🟢 Còn hàng</a>
    <button type="submit" class="btn-r" style="padding:.3rem .7rem;font-size:.75rem">Lọc</button>
    <?php if ($search || $catId || $statusF): ?>
    <a href="<?= APP_URL ?>/admin/inventory" style="font-size:.75rem;color:#555;text-decoration:none">✕ Xoá lọc</a>
    <?php endif; ?>
  </form>
  <div class="btn-export-group" style="margin-left:auto;display:flex;gap:.3rem;align-items:center">
    <a href="?<?= http_build_query(array_merge($_GET, array('export'=>'csv'))) ?>" class="btn-g"
      style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem;text-decoration:none">
      <i class="fa-solid fa-file-csv" style="color:#22c55e;font-size:.7rem"></i> CSV
    </a>
    <button onclick="window.print()" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem">
      <i class="fa-solid fa-print"></i> In
    </button>
    <?php $pdfQ = http_build_query(array_filter(['type'=>'inventory','s'=>$search,'cat'=>$catId?:(null),'status'=>$statusF])); ?>
    <a href="<?= APP_URL ?>/admin/export-pdf?<?= $pdfQ ?>" target="_blank" class="btn-g"
      style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem;text-decoration:none">
      <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> PDF
    </a>
  </div>
</div>

<!-- Inventory Table -->
<div class="card" style="padding:0;overflow:hidden">
  <div style="overflow-x:auto">
    <table class="adm-table" style="margin:0">
      <thead>
        <tr>
          <th style="width:36px"><input type="checkbox" id="chk-all" style="cursor:pointer"></th>
          <th>Sản phẩm</th>
          <th>Danh mục</th>
          <th>SKU</th>
          <th style="text-align:center">Tồn kho <span style="font-size:.6rem;color:#333;font-weight:400">(click để sửa)</span></th>
          <th style="text-align:center">Tối thiểu <span style="font-size:.6rem;color:#333;font-weight:400">(click để sửa)</span></th>
          <th>Cập nhật cuối</th>
          <th style="text-align:center">Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inventory as $item):
          $qty = (int)$item['stock_quantity'];
          $min = (int)$item['min_stock'];
          if ($qty <= 0)      { $rowCls='inv-row-out'; $badge='<span style="font-size:.65rem;background:rgba(248,113,113,.15);color:#f87171;padding:1px 6px;border-radius:99px">Hết</span>'; $qtyColor='#f87171'; }
          elseif ($qty<=$min) { $rowCls='inv-row-low'; $badge='<span style="font-size:.65rem;background:rgba(251,191,36,.15);color:#fbbf24;padding:1px 6px;border-radius:99px">Sắp hết</span>'; $qtyColor='#fbbf24'; }
          else                { $rowCls='inv-row-ok';  $badge='<span style="font-size:.65rem;background:rgba(74,222,128,.12);color:#4ade80;padding:1px 6px;border-radius:99px">OK</span>'; $qtyColor='#4ade80'; }
        ?>
        <tr class="<?= $rowCls ?>" data-pid="<?= $item['product_id'] ?>" data-qty="<?= $qty ?>" data-min="<?= $min ?>">
          <td><input type="checkbox" class="row-chk" data-pid="<?= $item['product_id'] ?>" style="cursor:pointer"></td>
          <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#ddd" title="<?= htmlspecialchars($item['name']) ?>"><?= htmlspecialchars($item['name']) ?></td>
          <td><span style="background:#222;padding:2px 6px;border-radius:99px;color:#777;font-size:.7rem"><?= htmlspecialchars($item['cat_name']) ?></span></td>
          <td style="color:#555;font-size:.75rem"><?= htmlspecialchars($item['sku'] ?? '-') ?></td>
          <td style="text-align:center">
            <span class="inv-cell" data-pid="<?= $item['product_id'] ?>" data-field="stock" data-val="<?= $qty ?>" style="font-size:.95rem;font-weight:900;color:<?= $qtyColor ?>"><?= $qty ?></span>
          </td>
          <td style="text-align:center">
            <span class="inv-cell" data-pid="<?= $item['product_id'] ?>" data-field="min" data-val="<?= $min ?>" style="color:#777"><?= $min ?></span>
          </td>
          <td style="color:#555;font-size:.74rem"><?= $item['last_restocked'] ? date('d/m/Y H:i', strtotime($item['last_restocked'])) : 'Chưa cập nhật' ?></td>
          <td style="text-align:center"><?= $badge ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($inventory)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:#444">Không có sản phẩm nào</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Bulk bar (sticky bottom) -->
  <div id="bulk-bar">
    <span id="bulk-count" style="font-size:.78rem;color:#888">0 đã chọn</span>
    <label style="font-size:.78rem;color:#777;display:flex;align-items:center;gap:.4rem">
      Đặt tồn kho:
      <input type="number" id="bulk-qty" min="0" value="0" class="form-inp" style="width:70px;padding:.25rem .4rem;font-size:.78rem">
    </label>
    <button id="bulk-submit" class="btn-r" style="padding:.28rem .7rem;font-size:.75rem">Cập nhật đã chọn</button>
    <button id="bulk-cancel" style="background:none;border:none;color:#555;cursor:pointer;font-size:.78rem">Bỏ chọn</button>
    <span id="bulk-msg" style="font-size:.75rem;color:#4ade80;display:none"></span>
  </div>
</div>

<!-- Restock History -->
<div class="card" style="margin-top:.75rem;padding:1rem">
  <div style="font-size:.82rem;font-weight:700;color:#ddd;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem">
    <i class="fa-solid fa-clock-rotate-left" style="color:#444;font-size:.75rem"></i> Lịch sử điều chỉnh kho (25 gần nhất)
  </div>
  <div class="hist-row hist-head" style="display:grid">
    <span>Thời gian</span><span>Người dùng</span><span>Sản phẩm</span><span>Tồn cũ → mới</span><span>Ghi chú</span>
  </div>
  <?php foreach ($restockLog as $log):
    $old = @json_decode($log['old_data'], true) ?? array();
    $new = @json_decode($log['new_data'], true) ?? array();
    $prodName  = $new['product_name'] ?? $old['product_name'] ?? '—';
    $isMinEdit = isset($old['min_stock']);
    if ($isMinEdit) {
      $oldV = $old['min_stock'] ?? '?'; $newV = $new['min_stock'] ?? '?';
      $label = 'Tối thiểu'; $arrow = $oldV.' → '.$newV;
      $color = '#a78bfa';
    } else {
      $oldV = $old['stock_quantity'] ?? '?'; $newV = $new['stock_quantity'] ?? '?';
      $label = 'Tồn kho';
      $diff  = is_numeric($newV) && is_numeric($oldV) ? $newV - $oldV : 0;
      $color = $diff >= 0 ? '#4ade80' : '#f87171';
      $arrow = $oldV.' → <span style="color:'.$color.';font-weight:700">'.$newV.'</span>';
    }
  ?>
  <div class="hist-row" style="display:grid">
    <span style="color:#555"><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
    <span style="color:#777"><?= htmlspecialchars($log['user_name']) ?></span>
    <span style="color:#bbb;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($prodName) ?>"><?= htmlspecialchars(mb_strlen($prodName)>30 ? mb_substr($prodName,0,28).'…' : $prodName) ?></span>
    <span><?= $arrow ?></span>
    <span style="color:#444;font-size:.68rem"><?= $label ?></span>
  </div>
  <?php endforeach; ?>
  <?php if (empty($restockLog)): ?>
  <div style="text-align:center;padding:1.5rem;color:#333;font-size:.8rem">Chưa có lịch sử điều chỉnh</div>
  <?php endif; ?>
</div>

<script>
var APP_URL = '<?= APP_URL ?>';

// ── Inline edit ──────────────────────────────────────────────────────────────
document.querySelectorAll('.inv-cell').forEach(function(cell) {
  cell.addEventListener('click', function() {
    if (cell.querySelector('input')) return; // already editing
    var val = cell.dataset.val;
    var orig = cell.textContent.trim();
    cell.classList.add('editing');
    cell.innerHTML = '<input type="number" min="0" value="'+val+'" style="width:60px">';
    var inp = cell.querySelector('input');
    inp.focus(); inp.select();

    var saved = false;
    function save() {
      if (saved) return;
      var newVal = parseInt(inp.value) || 0;
      if (newVal < 0) newVal = 0;
      if (String(newVal) === String(val)) { cell.classList.remove('editing'); cell.textContent = orig; return; }
      saved = true;
      cell.textContent = '…';
      var fd = new FormData();
      fd.append('product_id', cell.dataset.pid);
      fd.append('field', cell.dataset.field);
      if (cell.dataset.field === 'min') fd.append('min_stock', newVal);
      else fd.append('quantity', newVal);
      fetch(APP_URL+'/admin/inventory/ajax-update', {method:'POST',body:fd})
        .then(function(r){return r.json();})
        .then(function(data) {
          if (!data.success) { cell.textContent = orig; saved = false; return; }
          cell.dataset.val = data.value;
          var row = cell.closest('tr');
          var qty = cell.dataset.field === 'stock' ? data.value : parseInt(row.dataset.qty);
          var min = cell.dataset.field === 'min'   ? data.value : data.min_stock;
          row.dataset.qty = qty; row.dataset.min = min;
          refreshRow(row, qty, min);
          // Always sync stock cell color (may change when min edited)
          var stockCell = row.querySelector('.inv-cell[data-field="stock"]');
          if (stockCell) {
            var qc = qty <= 0 ? '#f87171' : (qty <= min ? '#fbbf24' : '#4ade80');
            stockCell.style.color = qc;
          }
          cell.textContent = data.value;
          cell.classList.remove('editing');
        })
        .catch(function(){ cell.textContent = orig; cell.classList.remove('editing'); saved = false; });
    }
    inp.addEventListener('keydown', function(e) { if (e.key==='Enter') { e.preventDefault(); save(); } if (e.key==='Escape') { saved=true; cell.classList.remove('editing'); cell.textContent = orig; } });
    inp.addEventListener('blur', save);
  });
});

function refreshRow(row, qty, min) {
  row.className = row.className.replace(/inv-row-\w+/g, '');
  var badge, cls;
  if (qty <= 0)        { cls='inv-row-out'; badge='<span style="font-size:.65rem;background:rgba(248,113,113,.15);color:#f87171;padding:1px 6px;border-radius:99px">Hết</span>'; }
  else if (qty <= min) { cls='inv-row-low'; badge='<span style="font-size:.65rem;background:rgba(251,191,36,.15);color:#fbbf24;padding:1px 6px;border-radius:99px">Sắp hết</span>'; }
  else                 { cls='inv-row-ok';  badge='<span style="font-size:.65rem;background:rgba(74,222,128,.12);color:#4ade80;padding:1px 6px;border-radius:99px">OK</span>'; }
  row.classList.add(cls);
  var badgeCell = row.querySelector('td:last-child');
  if (badgeCell) badgeCell.innerHTML = badge;
}

// ── Bulk select ──────────────────────────────────────────────────────────────
var chkAll    = document.getElementById('chk-all');
var bulkBar   = document.getElementById('bulk-bar');
var bulkCount = document.getElementById('bulk-count');
var bulkMsg   = document.getElementById('bulk-msg');

function updateBulkBar() {
  var checked = document.querySelectorAll('.row-chk:checked');
  if (checked.length > 0) {
    bulkBar.style.display = 'flex';
    bulkCount.textContent = checked.length + ' đã chọn';
  } else {
    bulkBar.style.display = 'none';
  }
}

chkAll.addEventListener('change', function() {
  document.querySelectorAll('.row-chk').forEach(function(c){ c.checked = chkAll.checked; });
  updateBulkBar();
});
document.querySelectorAll('.row-chk').forEach(function(c){
  c.addEventListener('change', function() {
    chkAll.checked = document.querySelectorAll('.row-chk:not(:checked)').length === 0;
    updateBulkBar();
  });
});

document.getElementById('bulk-cancel').addEventListener('click', function() {
  document.querySelectorAll('.row-chk').forEach(function(c){ c.checked = false; });
  chkAll.checked = false;
  updateBulkBar();
});

document.getElementById('bulk-submit').addEventListener('click', function() {
  var ids = Array.from(document.querySelectorAll('.row-chk:checked')).map(function(c){return c.dataset.pid;});
  if (!ids.length) return;
  var qty = parseInt(document.getElementById('bulk-qty').value) || 0;
  if (!confirm('Đặt tồn kho = '+qty+' cho '+ids.length+' sản phẩm?')) return;
  var fd = new FormData();
  ids.forEach(function(id){ fd.append('ids[]', id); });
  fd.append('quantity', qty);
  fetch(APP_URL+'/admin/inventory/bulk-update', {method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(data) {
      if (data.success) {
        bulkMsg.textContent = 'Đã cập nhật '+data.updated+' sản phẩm';
        bulkMsg.style.display = 'inline';
        setTimeout(function(){ location.reload(); }, 1000);
      }
    });
});
</script>
<?php require_once __DIR__.'./layout_bottom.php'; ?>
