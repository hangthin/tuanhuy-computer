<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$currentCatId = $currentCatId ?? 0;
$search       = $search ?? '';
$trash        = $trash  ?? false;
$trashCount   = $trashCount ?? 0;
$canDel       = isAdmin();
$qTrash       = $trash ? '&trash=1' : '';
?>
<div class="card" style="padding:1.1rem">

  <!-- ── View mode tabs: Active / Thùng rác ── -->
  <div style="display:flex;gap:.4rem;margin-bottom:.9rem">
    <a href="<?= APP_URL ?>/admin/products"
       style="display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .85rem;border-radius:7px;font-size:.78rem;font-weight:600;text-decoration:none;transition:all .15s;<?= !$trash?'background:var(--red);color:#fff':'background:#1a1a1a;color:#666;border:1px solid #2a2a2a' ?>">
      <i class="fas fa-box" style="font-size:.7rem"></i>Sản phẩm
    </a>
    <?php if($canDel): ?>
    <a href="<?= APP_URL ?>/admin/products?trash=1"
       style="display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .85rem;border-radius:7px;font-size:.78rem;font-weight:600;text-decoration:none;transition:all .15s;<?= $trash?'background:#7f1d1d;color:#fca5a5;border:1px solid rgba(239,68,68,.3)':'background:#1a1a1a;color:#666;border:1px solid #2a2a2a' ?>">
      <i class="fas fa-trash-can" style="font-size:.7rem"></i>Thùng rác
      <?php if($trashCount>0): ?>
      <span style="background:rgba(239,68,68,.2);color:#f87171;font-size:.62rem;padding:1px 6px;border-radius:99px;font-weight:700"><?= $trashCount ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar: search + add -->
  <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:.75rem">
    <form method="GET" style="display:flex;gap:.4rem;flex:1">
      <?php if($currentCatId): ?><input type="hidden" name="cat" value="<?= $currentCatId ?>"><?php endif; ?>
      <?php if($trash): ?><input type="hidden" name="trash" value="1"><?php endif; ?>
      <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên, SKU..." class="form-inp" style="max-width:280px">
      <button type="submit" class="btn-r" style="padding:.45rem .8rem"><i class="fas fa-search"></i></button>
      <?php if($search): ?><a href="<?= APP_URL ?>/admin/products<?= $trash?'?trash=1':($currentCatId?'?cat='.$currentCatId:'') ?>" class="btn-g" style="padding:.45rem .65rem;font-size:.75rem" title="Xóa tìm kiếm"><i class="fas fa-xmark"></i></a><?php endif; ?>
    </form>
    <?php if(!$trash): ?>
    <a href="<?= APP_URL ?>/admin/products/create" class="btn-r" style="text-decoration:none;display:flex;align-items:center;gap:.3rem"><i class="fas fa-plus"></i>Thêm mới</a>
    <?php endif; ?>
  </div>

  <?php if(!$trash): ?>
  <!-- Category filter tabs (active mode only) -->
  <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.9rem;padding-bottom:.8rem;border-bottom:1px solid #1a1a1a">
    <a href="<?= APP_URL ?>/admin/products<?= $search?'?s='.urlencode($search):'' ?>"
       style="display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .7rem;border-radius:99px;font-size:.74rem;font-weight:600;text-decoration:none;border:1px solid;transition:all .15s;<?= $currentCatId==0?'background:var(--red);color:#fff;border-color:var(--red)':'background:#0f0f0f;color:#666;border-color:#2a2a2a' ?>">
      Tất cả
    </a>
    <?php foreach($categories as $cat):
      $href = APP_URL.'/admin/products?cat='.$cat['id'].($search?'&s='.urlencode($search):'');
      $active = $currentCatId == $cat['id'];
    ?>
    <a href="<?= $href ?>"
       style="display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .7rem;border-radius:99px;font-size:.74rem;font-weight:600;text-decoration:none;border:1px solid;transition:all .15s;<?= $active?'background:var(--red);color:#fff;border-color:var(--red)':'background:#0f0f0f;color:#666;border-color:#2a2a2a' ?>"
       onmouseover="if(!<?= $active?'true':'false' ?>)this.style.borderColor='var(--red)',this.style.color='var(--red)'"
       onmouseout="if(!<?= $active?'true':'false' ?>)this.style.borderColor='#2a2a2a',this.style.color='#666'">
      <span><?= $cat['icon'] ?></span><?= htmlspecialchars($cat['name']) ?>
      <span style="<?= $active?'background:rgba(255,255,255,.25);color:#fff':'background:#1e1e1e;color:#555' ?>;font-size:.65rem;padding:1px 5px;border-radius:99px;font-weight:700"><?= $cat['product_count'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if($trash): ?>
  <div style="padding:.5rem .75rem;background:rgba(127,29,29,.25);border:1px solid rgba(239,68,68,.2);border-radius:8px;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#fca5a5">
    <i class="fas fa-triangle-exclamation"></i>
    Các sản phẩm trong thùng rác đã bị ẩn khỏi website. Chỉ Admin có thể khôi phục hoặc xóa vĩnh viễn.
  </div>
  <?php endif; ?>

  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr>
        <th style="width:58px">Ảnh</th>
        <th>Sản phẩm</th>
        <th>Danh mục</th>
        <th>Giá bán</th>
        <th>Kho</th>
        <?php if($trash): ?>
        <th>Xóa lúc</th>
        <?php else: ?>
        <th>Đã bán</th>
        <th>Trạng thái</th>
        <?php endif; ?>
        <th>Thao tác</th>
      </tr></thead>
      <tbody>
        <?php
        $catIcons=['1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-bolt','8'=>'fa-gamepad','9'=>'fa-hard-drive','10'=>'fa-screwdriver-wrench','11'=>'fa-headphones'];
        foreach($products as $p):
        $pIc=isset($catIcons[$p['category_id']])?$catIcons[$p['category_id']]:'fa-microchip';
        ?>
        <tr style="<?= $trash?'opacity:.75':'' ?>">
          <td>
            <div style="width:50px;height:50px;background:#0f0f0f;border-radius:8px;overflow:hidden;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center">
              <?php if(!empty($p['image'])&&$p['image']!=='default.jpg'): ?>
              <img src="<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>" alt=""
                   style="width:50px;height:50px;object-fit:cover;<?= $trash?'filter:grayscale(1)':'' ?>"
                   onerror="this.style.display='none';this.nextSibling.style.display='flex'">
              <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;color:#2a2a2a">
                <i class="fas <?= $pIc ?>" style="font-size:.9rem"></i>
              </div>
              <?php else: ?>
              <i class="fas <?= $pIc ?>" style="color:#2a2a2a;font-size:1rem"></i>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <div style="font-weight:600;color:<?= $trash?'#666':'#ddd' ?>;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;<?= $trash?'text-decoration:line-through':'' ?>"><?= htmlspecialchars($p['name']) ?></div>
            <div style="font-size:.7rem;color:#555"><?= htmlspecialchars($p['sku']??'') ?><?= !empty($p['brand_name'])?' · '.htmlspecialchars($p['brand_name']):'' ?></div>
          </td>
          <td><span style="background:#222;padding:2px 7px;border-radius:99px;color:#888;font-size:.72rem"><?= htmlspecialchars($p['category_name']??'') ?></span></td>
          <td>
            <div style="color:<?= $trash?'#555':'#4ade80' ?>;font-weight:700;font-size:.85rem"><?= formatPrice($p['final_price']) ?></div>
            <?php if(!$trash&&$p['sale_price']&&$p['sale_price']>0&&$p['sale_price']<$p['price']): ?>
            <div style="color:#555;text-decoration:line-through;font-size:.72rem"><?= formatPrice($p['price']) ?></div>
            <?php endif; ?>
          </td>
          <td style="font-weight:700;color:<?= $trash?'#555':($p['stock']>10?'#4ade80':($p['stock']>0?'#fbbf24':'#f87171')) ?>"><?= $p['stock'] ?></td>
          <?php if($trash): ?>
          <td style="color:#555;font-size:.75rem"><?= $p['deleted_at'] ? date('d/m/Y H:i', strtotime($p['deleted_at'])) : '—' ?></td>
          <?php else: ?>
          <td style="color:#777"><?= $p['sold'] ?></td>
          <td><?php if($p['is_active']): ?><span class="badge bdg-delivered">Hoạt động</span><?php else: ?><span class="badge bdg-cancelled">Tắt</span><?php endif; ?><?php if($p['is_featured']): ?><span class="badge bdg-processing" style="margin-left:3px">HOT</span><?php endif; ?></td>
          <?php endif; ?>
          <td>
            <div style="display:flex;gap:.25rem;flex-wrap:wrap">
              <?php if($trash): ?>
                <?php if($canDel): ?>
                <a href="<?= APP_URL ?>/admin/products/restore?id=<?= $p['id'] ?>"
                   class="btn-g" style="padding:.25rem .65rem;font-size:.7rem;border-color:rgba(74,222,128,.35);color:#4ade80"
                   onclick="return confirm('Khôi phục sản phẩm này?')">
                  <i class="fas fa-rotate-left"></i> Khôi phục
                </a>
                <?php endif; ?>
              <?php else: ?>
                <a href="<?= APP_URL ?>/products/detail/<?= $p['slug'] ?>" target="_blank" class="btn-g" style="padding:.25rem .55rem;font-size:.7rem" title="Xem"><i class="fas fa-eye"></i></a>
                <a href="<?= APP_URL ?>/admin/products/edit?id=<?= $p['id'] ?>" class="btn-g" style="padding:.25rem .55rem;font-size:.7rem;border-color:rgba(96,165,250,.4);color:#60a5fa"><i class="fas fa-edit"></i></a>
                <?php if($canDel): ?>
                <a href="<?= APP_URL ?>/admin/products/delete?id=<?= $p['id'] ?>"
                   class="btn-g" style="padding:.25rem .55rem;font-size:.7rem;border-color:rgba(239,68,68,.3);color:#f87171"
                   onclick="return confirm('Chuyển vào thùng rác?\n\n<?= addslashes(htmlspecialchars(mb_substr($p['name'],0,50))) ?>\n\n(Có thể khôi phục sau)')">
                  <i class="fas fa-trash"></i>
                </a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; if(empty($products)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:#555">
          <?= $trash ? 'Thùng rác trống.' : 'Không có sản phẩm nào.' ?>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($totalPagesAdmin??1)>1): ?>
  <div style="display:flex;gap:.3rem;margin-top:.9rem;justify-content:center">
    <?php $qBase='s='.urlencode($search).($currentCatId?'&cat='.$currentCatId:'').($trash?'&trash=1':''); for($i=1;$i<=($totalPagesAdmin??1);$i++): ?>
    <a href="?page=<?= $i ?>&<?= $qBase ?>" style="padding:.3rem .65rem;border-radius:5px;text-decoration:none;font-size:.78rem;<?= $i==($page??1)?'background:var(--red);color:#fff':'background:#1a1a1a;color:#aaa;border:1px solid #333' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
