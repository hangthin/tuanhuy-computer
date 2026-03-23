<?php require_once __DIR__.'/layout_top.php';
$statusMap=array('pending'=>array('Chờ XN','bdg-pending'),'confirmed'=>array('Đã XN','bdg-confirmed'),'processing'=>array('Xử lý','bdg-processing'),'shipping'=>array('Đang giao','bdg-shipping'),'delivered'=>array('Đã giao','bdg-delivered'),'cancelled'=>array('Đã hủy','bdg-cancelled'));
?>
<div class="card" style="padding:1.1rem">
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.9rem">
    <form method="GET" style="display:flex;gap:.4rem;flex:1">
      <input type="text" name="s" value="<?= htmlspecialchars($search??'') ?>" placeholder="Mã đơn, tên, SĐT..." class="form-inp" style="max-width:230px">
      <select name="status" class="form-inp" style="max-width:150px">
        <option value="">Tất cả TT</option>
        <?php foreach($statusMap as $sv=>$sl): ?><option value="<?= $sv ?>" <?= ($status??'')===$sv?'selected':'' ?>><?= $sl[0] ?></option><?php endforeach; ?>
      </select>
      <button type="submit" class="btn-r" style="padding:.45rem .8rem"><i class="fas fa-search"></i></button>
    </form>
  </div>
  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Sản phẩm</th><th>Tổng tiền</th><th>TT thanh toán</th><th>Trạng thái</th><th>Ngày đặt</th><th>Cập nhật</th></tr></thead>
      <tbody>
        <?php foreach($orders as $o): ?>
        <tr>
          <td><a href="<?= APP_URL ?>/admin/orders/detail?id=<?= $o['id'] ?>" style="color:var(--red);font-weight:700;text-decoration:none"><?= $o['order_code'] ?></a></td>
          <td><div style="color:#ddd"><?= htmlspecialchars($o['fullname']) ?></div><div style="color:#555;font-size:.72rem"><?= $o['phone'] ?></div></td>
          <td>
            <?php
            $db2=Database::getInstance();
            $oItems=$db2->fetchAll("SELECT od.quantity,p.name,p.image,p.category_id FROM order_details od JOIN products p ON od.product_id=p.id WHERE od.order_id=? LIMIT 3",array($o['id']));
            $catIco=['1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-bolt','8'=>'fa-gamepad','9'=>'fa-hard-drive','10'=>'fa-screwdriver-wrench','11'=>'fa-headphones'];
            ?>
            <div style="display:flex;align-items:center;gap:.3rem;flex-wrap:nowrap">
              <?php foreach($oItems as $oi):
                $oIc=isset($catIco[$oi['category_id']])?$catIco[$oi['category_id']]:'fa-microchip';
              ?>
              <div style="position:relative;flex-shrink:0" title="<?= htmlspecialchars($oi['name']) ?> x<?= $oi['quantity'] ?>">
                <div style="width:36px;height:36px;background:#0f0f0f;border-radius:6px;overflow:hidden;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center">
                  <?php if(!empty($oi['image'])&&$oi['image']!=='default.jpg'): ?>
                  <img src="<?= UPLOAD_URL.htmlspecialchars($oi['image']) ?>" alt=""
                       style="width:36px;height:36px;object-fit:cover"
                       onerror="this.style.display='none';this.nextSibling.style.display='flex'">
                  <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;color:#2a2a2a;font-size:.7rem"><i class="fas <?= $oIc ?>"></i></div>
                  <?php else: ?>
                  <i class="fas <?= $oIc ?>" style="color:#2a2a2a;font-size:.75rem"></i>
                  <?php endif; ?>
                </div>
                <?php if($oi['quantity']>1): ?>
                <span style="position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;font-size:.5rem;font-weight:800;min-width:14px;height:14px;border-radius:99px;display:flex;align-items:center;justify-content:center;border:1.5px solid #141414"><?= $oi['quantity'] ?></span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
              <?php if($o['item_count']>3): ?><span style="color:#555;font-size:.7rem">+<?= $o['item_count']-3 ?></span><?php endif; ?>
            </div>
          </td>
          <td style="color:#4ade80;font-weight:700"><?= formatPrice($o['total']) ?></td>
          <td><span style="color:<?= $o['payment_status']==='paid'?'#4ade80':'#fbbf24' ?>;font-size:.75rem"><?= $o['payment_status']==='paid'?'✅ Đã TT':'⏳ Chờ TT' ?></span></td>
          <td><span class="badge <?= isset($statusMap[$o['status']])?$statusMap[$o['status']][1]:'bdg-pending' ?>"><?= isset($statusMap[$o['status']])?$statusMap[$o['status']][0]:$o['status'] ?></span></td>
          <td style="color:#555;font-size:.75rem"><?= date('d/m/Y H:i',strtotime($o['created_at'])) ?></td>
          <td>
            <?php if((int)($_SESSION['user_role']??0) !== 3): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/orders/status?id=<?= $o['id'] ?>" style="display:flex;gap:.25rem">
              <select name="status" class="form-inp" style="padding:.25rem .4rem;font-size:.72rem;min-width:100px">
                <?php foreach($statusMap as $sv=>$sl): ?><option value="<?= $sv ?>" <?= $o['status']===$sv?'selected':'' ?>><?= $sl[0] ?></option><?php endforeach; ?>
              </select>
              <button type="submit" class="btn-r" style="padding:.25rem .5rem;font-size:.7rem">✓</button>
            </form>
            <?php else: ?>
            <span style="color:#555;font-size:.72rem">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; if(empty($orders)): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:#555">Không có đơn hàng.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($totalPagesAdmin??1)>1): ?>
  <div style="display:flex;gap:.3rem;margin-top:.9rem;justify-content:center">
    <?php for($i=1;$i<=($totalPagesAdmin??1);$i++): ?>
    <a href="?page=<?= $i ?>&s=<?= urlencode($search??'') ?>&status=<?= $status??'' ?>" style="padding:.3rem .65rem;border-radius:5px;text-decoration:none;font-size:.78rem;<?= $i==($page??1)?'background:var(--red);color:#fff':'background:#1a1a1a;color:#aaa;border:1px solid #333' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
