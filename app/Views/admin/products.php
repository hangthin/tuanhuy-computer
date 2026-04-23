<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$currentCatId = $currentCatId ?? 0;
$search       = $search       ?? '';
$trash        = $trash        ?? false;
$trashCount   = $trashCount   ?? 0;
$canDel       = isAdmin();
$totalPages   = $totalPagesAdmin ?? 1;
$currentPage  = $page ?? 1;
$qBase        = 's='.urlencode($search).($currentCatId?'&cat='.$currentCatId:'').($trash?'&trash=1':'');
?>
<style>
/* ── Products page ─────────────────────────────────────── */
.prd-wrap{background:#141414;border:1px solid #1e1e1e;border-radius:12px;overflow:hidden}

/* Toolbar */
.prd-toolbar{padding:.85rem 1rem;display:flex;gap:.55rem;flex-wrap:wrap;align-items:center;border-bottom:1px solid #1a1a1a;background:#111}
.prd-toolbar-left{display:flex;gap:.4rem;flex:1;min-width:0}
.prd-search{display:flex;gap:.3rem;flex:1;max-width:320px}
.prd-search input{flex:1;min-width:0}

/* View tabs */
.prd-vtabs{display:flex;gap:.35rem;padding:.65rem 1rem;border-bottom:1px solid #1a1a1a}
.prd-vtab{display:inline-flex;align-items:center;gap:.35rem;padding:.32rem .9rem;border-radius:7px;font-size:.77rem;font-weight:600;text-decoration:none;border:1px solid transparent;transition:all .15s;cursor:pointer}
.prd-vtab.active{background:var(--red);color:#fff;border-color:var(--red)}
.prd-vtab.inactive{background:#1a1a1a;color:#666;border-color:#2a2a2a}
.prd-vtab.inactive:hover{color:#aaa;border-color:#3a3a3a}
.prd-vtab-trash-active{background:#1a0505;color:#fca5a5;border-color:rgba(239,68,68,.35)}
.prd-vtab-trash-active:hover{opacity:.9}
.vtab-badge{background:rgba(239,68,68,.2);color:#f87171;font-size:.62rem;padding:1px 5px;border-radius:99px;font-weight:700}

/* Category filter */
.prd-cats{display:flex;flex-wrap:wrap;gap:.3rem;padding:.65rem 1rem;border-bottom:1px solid #1a1a1a}
.prd-cat{display:inline-flex;align-items:center;gap:.3rem;padding:.26rem .7rem;border-radius:99px;font-size:.73rem;font-weight:600;text-decoration:none;border:1px solid #222;color:#666;background:#111;transition:all .15s;white-space:nowrap}
.prd-cat:hover{border-color:var(--red);color:var(--red)}
.prd-cat.active{background:var(--red);color:#fff;border-color:var(--red)}
.prd-cat-cnt{font-size:.63rem;padding:1px 5px;border-radius:99px;font-weight:700}
.prd-cat.active .prd-cat-cnt{background:rgba(255,255,255,.25);color:#fff}
.prd-cat:not(.active) .prd-cat-cnt{background:#1e1e1e;color:#555}

/* Trash notice */
.prd-trash-notice{margin:.65rem 1rem;padding:.5rem .85rem;background:rgba(127,29,29,.25);border:1px solid rgba(239,68,68,.2);border-radius:8px;display:flex;align-items:center;gap:.5rem;font-size:.77rem;color:#fca5a5}

/* Table */
.prd-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.prd-table{width:100%;border-collapse:collapse;min-width:640px}
.prd-table thead th{padding:.6rem .9rem;font-size:.7rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #1a1a1a;white-space:nowrap;background:#111}
.prd-table tbody tr{border-bottom:1px solid #161616;transition:background .12s}
.prd-table tbody tr:hover{background:#161616}
.prd-table tbody tr.trashed{opacity:.65}
.prd-table td{padding:.6rem .9rem;vertical-align:middle}
.prd-table td:first-child{padding-left:1rem}
.prd-table th:first-child{padding-left:1rem}

/* Product image */
.prd-img{width:48px;height:48px;background:#0f0f0f;border-radius:8px;overflow:hidden;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.prd-img img{width:100%;height:100%;object-fit:cover}

/* Product info */
.prd-name{font-weight:600;color:#ddd;font-size:.83rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px}
.prd-name.trashed{color:#555;text-decoration:line-through}
.prd-sub{font-size:.68rem;color:#444;margin-top:2px}

/* Price */
.prd-price{font-weight:700;font-size:.84rem;color:#4ade80;white-space:nowrap}
.prd-price.trashed{color:#555}
.prd-price-old{font-size:.7rem;color:#444;text-decoration:line-through}

/* Stock */
.prd-stock{font-weight:700;font-size:.83rem}
.stock-ok{color:#4ade80}
.stock-low{color:#fbbf24}
.stock-out{color:#f87171}
.stock-trashed{color:#555}

/* Category badge */
.cat-badge{background:#1e1e1e;color:#777;font-size:.7rem;padding:2px 8px;border-radius:99px;white-space:nowrap}

/* Status badges */
.prd-status{display:flex;flex-wrap:wrap;gap:.25rem;align-items:center}

/* Actions */
.prd-acts{display:flex;gap:.25rem;align-items:center;flex-wrap:nowrap}
.act-btn{display:inline-flex;align-items:center;justify-content:center;gap:.25rem;padding:.28rem .55rem;border-radius:6px;font-size:.7rem;font-weight:600;text-decoration:none;border:1px solid;cursor:pointer;transition:all .15s;white-space:nowrap;background:transparent;font-family:inherit}
.act-view{border-color:#2a2a2a;color:#777}.act-view:hover{border-color:#aaa;color:#ddd}
.act-edit{border-color:rgba(96,165,250,.35);color:#60a5fa}.act-edit:hover{border-color:#60a5fa;background:rgba(96,165,250,.08)}
.act-del{border-color:rgba(239,68,68,.3);color:#f87171}.act-del:hover{border-color:#f87171;background:rgba(239,68,68,.08)}
.act-restore{border-color:rgba(74,222,128,.35);color:#4ade80}.act-restore:hover{border-color:#4ade80;background:rgba(74,222,128,.08)}

/* Summary bar */
.prd-summary{padding:.55rem 1rem;border-bottom:1px solid #1a1a1a;font-size:.73rem;color:#555;display:flex;align-items:center;gap:1rem}
.prd-summary b{color:#888}

/* Pagination */
.prd-pages{padding:.75rem 1rem;display:flex;align-items:center;justify-content:center;gap:.3rem;flex-wrap:wrap;border-top:1px solid #1a1a1a}
.pg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 .5rem;border-radius:6px;text-decoration:none;font-size:.75rem;font-weight:600;border:1px solid #222;color:#666;background:#111;transition:all .15s}
.pg-btn:hover{border-color:#444;color:#ddd}
.pg-btn.active{background:var(--red);color:#fff;border-color:var(--red)}
.pg-btn.disabled{opacity:.3;pointer-events:none}

/* Empty */
.prd-empty{text-align:center;padding:3rem 1rem;color:#444}
.prd-empty i{font-size:2rem;margin-bottom:.75rem;display:block}

/* Responsive */
@media(max-width:768px){
  .prd-toolbar{flex-direction:column;align-items:stretch}
  .prd-toolbar-left{max-width:100%}
  .prd-search{max-width:100%}
  .prd-table thead th:nth-child(3),
  .prd-table tbody td:nth-child(3){display:none} /* hide category on mobile */
  .prd-name{max-width:160px}
}
@media(max-width:520px){
  .prd-table thead th:nth-child(5),
  .prd-table tbody td:nth-child(5){display:none} /* hide sold on mobile */
  .act-btn span{display:none} /* icon only on small */
}
</style>

<div class="prd-wrap">

  <!-- View tabs -->
  <div class="prd-vtabs">
    <a href="<?= APP_URL ?>/admin/products"
       class="prd-vtab <?= !$trash ? 'active' : 'inactive' ?>">
      <i class="fa-solid fa-box" style="font-size:.7rem"></i> Sản phẩm
    </a>
    <?php if($canDel): ?>
    <a href="<?= APP_URL ?>/admin/products?trash=1"
       class="prd-vtab <?= $trash ? 'prd-vtab-trash-active' : 'inactive' ?>">
      <i class="fa-solid fa-trash-can" style="font-size:.7rem"></i> Thùng rác
      <?php if($trashCount > 0): ?>
      <span class="vtab-badge"><?= $trashCount ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar -->
  <div class="prd-toolbar">
    <div class="prd-toolbar-left">
      <form method="GET" class="prd-search">
        <?php if($currentCatId): ?><input type="hidden" name="cat" value="<?= $currentCatId ?>"><?php endif; ?>
        <?php if($trash): ?><input type="hidden" name="trash" value="1"><?php endif; ?>
        <input type="text" name="s" value="<?= htmlspecialchars($search) ?>"
               placeholder="Tìm tên, SKU, thương hiệu..." class="form-inp"
               style="font-size:.8rem">
        <button type="submit" class="btn-r" style="padding:.42rem .75rem;flex-shrink:0">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <?php if($search): ?>
        <a href="<?= APP_URL ?>/admin/products<?= $trash?'?trash=1':($currentCatId?'?cat='.$currentCatId:'') ?>"
           class="btn-g" style="padding:.42rem .6rem;flex-shrink:0" title="Xóa tìm kiếm">
          <i class="fa-solid fa-xmark"></i>
        </a>
        <?php endif; ?>
      </form>
    </div>
    <?php if(!$trash): ?>
    <a href="<?= APP_URL ?>/admin/products/create" class="btn-r"
       style="text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;flex-shrink:0;padding:.42rem .9rem;font-size:.8rem">
      <i class="fa-solid fa-plus"></i> Thêm sản phẩm
    </a>
    <?php endif; ?>
    <div class="btn-export-group" style="display:flex;gap:.3rem;flex-shrink:0">
      <button onclick="window.print()" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.42rem .65rem">
        <i class="fa-solid fa-print"></i> In
      </button>
      <?php $pdfQ = http_build_query(array_filter(['type'=>'products','s'=>$search,'cat'=>$currentCatId?:(null)])); ?>
      <a href="<?= APP_URL ?>/admin/export-pdf?<?= $pdfQ ?>" target="_blank" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.42rem .65rem;text-decoration:none">
        <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> PDF
      </a>
    </div>
  </div>

  <?php if(!$trash): ?>
  <!-- Category filter -->
  <div class="prd-cats">
    <a href="<?= APP_URL ?>/admin/products<?= $search?'?s='.urlencode($search):'' ?>"
       class="prd-cat <?= $currentCatId==0?'active':'' ?>">
      Tất cả
      <span class="prd-cat-cnt"><?= array_sum(array_column($categories,'product_count')) ?></span>
    </a>
    <?php foreach($categories as $cat):
      $href   = APP_URL.'/admin/products?cat='.$cat['id'].($search?'&s='.urlencode($search):'');
      $active = $currentCatId == $cat['id'];
    ?>
    <a href="<?= $href ?>" class="prd-cat <?= $active?'active':'' ?>">
      <i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-tag') ?>" style="font-size:.68rem"></i>
      <?= htmlspecialchars($cat['name']) ?>
      <span class="prd-cat-cnt"><?= $cat['product_count'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if($trash): ?>
  <div class="prd-trash-notice">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Sản phẩm trong thùng rác đã ẩn khỏi website. Chỉ Admin có thể khôi phục hoặc xóa vĩnh viễn.
  </div>
  <?php endif; ?>

  <!-- Summary -->
  <?php if(!empty($products)): ?>
  <div class="prd-summary">
    Hiển thị <b><?= count($products) ?></b> sản phẩm
    <?php if($totalPages > 1): ?>· Trang <b><?= $currentPage ?></b> / <b><?= $totalPages ?></b><?php endif; ?>
    <?php if($search): ?>· Kết quả cho "<b><?= htmlspecialchars($search) ?></b>"<?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Table -->
  <div class="prd-table-wrap">
    <table class="prd-table">
      <thead>
        <tr>
          <th style="width:58px">Ảnh</th>
          <th>Sản phẩm</th>
          <th>Danh mục</th>
          <th style="text-align:right">Giá bán</th>
          <th style="text-align:center">Kho</th>
          <?php if($trash): ?>
          <th>Xóa lúc</th>
          <?php else: ?>
          <th style="text-align:center">Đã bán</th>
          <th>Trạng thái</th>
          <?php endif; ?>
          <th style="text-align:right">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $catIcons = array('1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse',
                          '5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-bolt','8'=>'fa-gamepad',
                          '9'=>'fa-hard-drive','10'=>'fa-screwdriver-wrench','11'=>'fa-headphones');
        foreach($products as $p):
          $pIc     = $catIcons[$p['category_id']] ?? 'fa-microchip';
          $stockCls = $trash ? 'stock-trashed' : ($p['stock'] > 10 ? 'stock-ok' : ($p['stock'] > 0 ? 'stock-low' : 'stock-out'));
        ?>
        <tr class="<?= $trash?'trashed':'' ?>">

          <!-- Ảnh -->
          <td>
            <div class="prd-img">
              <?php if(!empty($p['image']) && $p['image'] !== 'default.jpg'): ?>
              <img src="<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" alt=""
                   style="<?= $trash?'filter:grayscale(.8)':'' ?>"
                   onerror="this.parentNode.innerHTML='<i class=\'fa-solid <?= $pIc ?>\' style=\'color:#2a2a2a;font-size:.9rem\'></i>'">
              <?php else: ?>
              <i class="fa-solid <?= $pIc ?>" style="color:#2a2a2a;font-size:.95rem"></i>
              <?php endif; ?>
            </div>
          </td>

          <!-- Tên -->
          <td>
            <div class="prd-name <?= $trash?'trashed':'' ?>" title="<?= htmlspecialchars($p['name']) ?>">
              <?= htmlspecialchars($p['name']) ?>
            </div>
            <div class="prd-sub">
              <?= htmlspecialchars($p['sku'] ?? '') ?>
              <?= !empty($p['brand_name']) ? ' &middot; '.htmlspecialchars($p['brand_name']) : '' ?>
            </div>
          </td>

          <!-- Danh mục -->
          <td><span class="cat-badge"><?= htmlspecialchars($p['category_name'] ?? '') ?></span></td>

          <!-- Giá -->
          <td style="text-align:right">
            <div class="prd-price <?= $trash?'trashed':'' ?>"><?= formatPrice($p['final_price']) ?></div>
            <?php if(!$trash && $p['sale_price'] && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']): ?>
            <div class="prd-price-old"><?= formatPrice($p['price']) ?></div>
            <?php endif; ?>
          </td>

          <!-- Kho -->
          <td style="text-align:center">
            <span class="prd-stock <?= $stockCls ?>"><?= $p['stock'] ?></span>
          </td>

          <?php if($trash): ?>
          <!-- Xóa lúc -->
          <td style="color:#555;font-size:.74rem;white-space:nowrap">
            <?= $p['deleted_at'] ? date('d/m/Y', strtotime($p['deleted_at'])) : '—' ?>
          </td>
          <?php else: ?>
          <!-- Đã bán -->
          <td style="text-align:center;color:#777;font-size:.82rem"><?= $p['sold'] ?></td>

          <!-- Trạng thái -->
          <td>
            <div class="prd-status">
              <?php if($p['is_active']): ?>
              <span class="badge bdg-delivered">Hiển thị</span>
              <?php else: ?>
              <span class="badge bdg-cancelled">Ẩn</span>
              <?php endif; ?>
              <?php if($p['is_featured']): ?>
              <span class="badge bdg-processing">HOT</span>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>

          <!-- Thao tác -->
          <td style="text-align:right">
            <div class="prd-acts" style="justify-content:flex-end">
              <?php if($trash): ?>
                <?php if($canDel): ?>
                <a href="<?= APP_URL ?>/admin/products/restore?id=<?= $p['id'] ?>"
                   class="act-btn act-restore"
                   onclick="return confirm('Khôi phục sản phẩm này?')">
                  <i class="fa-solid fa-rotate-left"></i> <span>Khôi phục</span>
                </a>
                <?php endif; ?>
              <?php else: ?>
                <a href="<?= APP_URL ?>/products/detail/<?= $p['slug'] ?>"
                   target="_blank" class="act-btn act-view" title="Xem trang">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <a href="<?= APP_URL ?>/admin/products/edit?id=<?= $p['id'] ?>"
                   class="act-btn act-edit" title="Chỉnh sửa">
                  <i class="fa-solid fa-pen-to-square"></i> <span>Sửa</span>
                </a>
                <?php if($canDel): ?>
                <a href="<?= APP_URL ?>/admin/products/delete?id=<?= $p['id'] ?>"
                   class="act-btn act-del" title="Xóa"
                   onclick="return confirm('Chuyển vào thùng rác?\n\n<?= addslashes(htmlspecialchars(mb_substr($p['name'],0,50))) ?>')">
                  <i class="fa-solid fa-trash"></i>
                </a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($products)): ?>
        <tr>
          <td colspan="8">
            <div class="prd-empty">
              <i class="fa-solid fa-<?= $trash?'trash-can':'box-open' ?>"></i>
              <?= $trash ? 'Thùng rác trống.' : ($search ? 'Không tìm thấy sản phẩm nào.' : 'Chưa có sản phẩm nào.') ?>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($totalPages > 1): ?>
  <div class="prd-pages">
    <a href="?page=<?= max(1,$currentPage-1) ?>&<?= $qBase ?>"
       class="pg-btn <?= $currentPage<=1?'disabled':'' ?>">
      <i class="fa-solid fa-chevron-left" style="font-size:.65rem"></i>
    </a>
    <?php
    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);
    if($start > 1): ?>
    <a href="?page=1&<?= $qBase ?>" class="pg-btn">1</a>
    <?php if($start > 2): ?><span style="color:#444;font-size:.75rem;padding:0 .2rem">…</span><?php endif; ?>
    <?php endif; ?>
    <?php for($i=$start; $i<=$end; $i++): ?>
    <a href="?page=<?= $i ?>&<?= $qBase ?>" class="pg-btn <?= $i==$currentPage?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($end < $totalPages): ?>
    <?php if($end < $totalPages-1): ?><span style="color:#444;font-size:.75rem;padding:0 .2rem">…</span><?php endif; ?>
    <a href="?page=<?= $totalPages ?>&<?= $qBase ?>" class="pg-btn"><?= $totalPages ?></a>
    <?php endif; ?>
    <a href="?page=<?= min($totalPages,$currentPage+1) ?>&<?= $qBase ?>"
       class="pg-btn <?= $currentPage>=$totalPages?'disabled':'' ?>">
      <i class="fa-solid fa-chevron-right" style="font-size:.65rem"></i>
    </a>
  </div>
  <?php endif; ?>

</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
