<?php require_once __DIR__.'/layout_top.php'; ?>
<div class="card" style="padding:1.1rem">
  <div style="display:flex;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap">
    <span style="padding:.22rem .6rem;border-radius:99px;font-size:.7rem;background:rgba(239,68,68,.15);color:#f87171">🔴 Hết hàng</span>
    <span style="padding:.22rem .6rem;border-radius:99px;font-size:.7rem;background:rgba(251,191,36,.15);color:#fbbf24">🟡 Sắp hết (≤ min)</span>
    <span style="padding:.22rem .6rem;border-radius:99px;font-size:.7rem;background:rgba(74,222,128,.15);color:#4ade80">🟢 Còn hàng</span>
  </div>
  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>SKU</th><th>Tồn kho</th><th>Tối thiểu</th><th>Cập nhật cuối</th><th>Điều chỉnh</th></tr></thead>
      <tbody>
        <?php foreach($inventory as $item):
          $c=$item['stock_quantity']<=0?'#f87171':($item['stock_quantity']<=$item['min_stock']?'#fbbf24':'#4ade80');
        ?>
        <tr>
          <td style="color:#ddd;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($item['name']) ?></td>
          <td><span style="background:#222;padding:2px 6px;border-radius:99px;color:#777;font-size:.7rem"><?= htmlspecialchars($item['cat_name']) ?></span></td>
          <td style="color:#555;font-size:.75rem"><?= $item['sku']??'-' ?></td>
          <td style="font-size:.95rem;font-weight:900;color:<?= $c ?>"><?= $item['stock_quantity'] ?></td>
          <td style="color:#555"><?= $item['min_stock'] ?></td>
          <td style="color:#555;font-size:.75rem"><?= $item['last_restocked']?date('d/m/Y',strtotime($item['last_restocked'])):'Chưa cập nhật' ?></td>
          <td>
            <form method="POST" action="<?= APP_URL ?>/admin/inventory/update" style="display:flex;gap:.3rem">
              <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
              <input type="number" name="quantity" value="<?= $item['stock_quantity'] ?>" min="0" class="form-inp" style="width:65px;padding:.25rem .4rem;font-size:.78rem">
              <button type="submit" class="btn-r" style="padding:.25rem .55rem;font-size:.72rem">Cập nhật</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
