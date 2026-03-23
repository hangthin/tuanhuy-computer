<?php require_once __DIR__.'/layout_top.php'; ?>
<div class="card" style="padding:1.1rem">
  <div style="display:flex;gap:.5rem;margin-bottom:.9rem">
    <form method="GET" style="display:flex;gap:.4rem;flex:1">
      <input type="text" name="s" value="<?= htmlspecialchars($search??'') ?>" placeholder="Tên, email..." class="form-inp" style="max-width:260px">
      <button type="submit" class="btn-r" style="padding:.45rem .8rem"><i class="fas fa-search"></i></button>
    </form>
    <span style="color:#555;font-size:.82rem;align-self:center">Tổng: <?= $totalCustomers ?? 0 ?> KH</span>
  </div>
  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr><th>ID</th><th>Khách hàng</th><th>Email</th><th>SĐT</th><th>Thành phố</th><th>Ngày ĐK</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
      <tbody>
        <?php foreach($customers as $c): ?>
        <tr>
          <td style="color:#555">#<?= $c['id'] ?></td>
          <td><div style="display:flex;align-items:center;gap:.5rem"><div style="width:28px;height:28px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0"><?= strtoupper(mb_substr($c['fullname'],0,1)) ?></div><span style="color:#ddd"><?= htmlspecialchars($c['fullname']) ?></span></div></td>
          <td style="color:#777;font-size:.8rem"><?= htmlspecialchars($c['email']) ?></td>
          <td style="color:#777;font-size:.8rem"><?= $c['phone']??'-' ?></td>
          <td style="color:#666;font-size:.8rem"><?= htmlspecialchars($c['city']??'-') ?></td>
          <td style="color:#555;font-size:.78rem"><?= date('d/m/Y',strtotime($c['created_at'])) ?></td>
          <td><span class="badge <?= $c['is_active']?'bdg-delivered':'bdg-cancelled' ?>"><?= $c['is_active']?'Hoạt động':'Bị khóa' ?></span></td>
          <td><a href="<?= APP_URL ?>/admin/customers/toggle?id=<?= $c['id'] ?>" class="btn-g" style="padding:.25rem .6rem;font-size:.72rem;<?= $c['is_active']?'border-color:rgba(239,68,68,.3);color:#f87171':'border-color:rgba(34,197,94,.3);color:#4ade80' ?>" onclick="return confirm('<?= $c['is_active']?'Khóa':'Mở khóa' ?> tài khoản?')"><?= $c['is_active']?'🔒 Khóa':'🔓 Mở' ?></a></td>
        </tr>
        <?php endforeach; if(empty($customers)): ?><tr><td colspan="8" style="text-align:center;padding:2rem;color:#555">Không tìm thấy.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($totalPagesAdmin??1)>1): ?>
  <div style="display:flex;gap:.3rem;margin-top:.9rem;justify-content:center">
    <?php for($i=1;$i<=($totalPagesAdmin??1);$i++): ?>
    <a href="?page=<?= $i ?>&s=<?= urlencode($search??'') ?>" style="padding:.3rem .65rem;border-radius:5px;text-decoration:none;font-size:.78rem;<?= $i==($page??1)?'background:var(--red);color:#fff':'background:#1a1a1a;color:#aaa;border:1px solid #333' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
