<?php require_once __DIR__.'/layout_top.php'; ?>
<?php $canEdit = in_array((int)($_SESSION['user_role']??0),[1,2]); ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1rem;align-items:start">

  <!-- List -->
  <div class="card" style="padding:1.1rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.9rem">
      <span style="font-weight:700;color:#fff;font-size:.9rem">Danh sách danh mục</span>
      <span style="font-size:.72rem;color:#555"><?= count($categories) ?> danh mục</span>
    </div>
    <div style="overflow-x:auto">
      <table class="adm-table">
        <thead><tr>
          <th style="width:40px">Icon</th>
          <th>Tên danh mục</th>
          <th>Slug</th>
          <th>Thứ tự</th>
          <th>SP</th>
          <th>TT</th>
          <?php if($canEdit): ?><th>Sửa</th><?php endif; ?>
        </tr></thead>
        <tbody>
          <?php foreach($categories as $cat): ?>
          <tr>
            <td style="text-align:center;font-size:1.2rem"><?= $cat['icon'] ?></td>
            <td>
              <div style="font-weight:600;color:#ddd"><?= htmlspecialchars($cat['name']) ?></div>
              <?php if(!empty($cat['description'])): ?>
              <div style="font-size:.7rem;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px"><?= htmlspecialchars($cat['description']) ?></div>
              <?php endif; ?>
            </td>
            <td><code style="background:#1a1a1a;color:#aaa;padding:1px 6px;border-radius:4px;font-size:.72rem"><?= htmlspecialchars($cat['slug']) ?></code></td>
            <td style="color:#777;text-align:center"><?= $cat['sort_order'] ?></td>
            <td><span style="background:#222;color:#888;padding:1px 7px;border-radius:99px;font-size:.72rem;font-weight:600"><?= $cat['product_count'] ?? 0 ?></span></td>
            <td><?php if($cat['is_active']): ?><span class="badge bdg-delivered">Hoạt động</span><?php else: ?><span class="badge bdg-cancelled">Tắt</span><?php endif; ?></td>
            <?php if($canEdit): ?>
            <td>
              <button onclick="catEdit(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)"
                      class="btn-g" style="padding:.25rem .55rem;font-size:.7rem;border-color:rgba(96,165,250,.4);color:#60a5fa">
                <i class="fas fa-edit"></i>
              </button>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; if(empty($categories)): ?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:#555">Chưa có danh mục.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Form -->
  <?php if($canEdit): ?>
  <div class="card" style="padding:1.1rem;position:sticky;top:1rem">
    <div style="font-weight:700;color:#fff;font-size:.875rem;margin-bottom:.9rem" id="cat-form-title">
      <i class="fas fa-plus" style="color:var(--red);margin-right:.35rem"></i>Thêm danh mục
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/categories/save" id="cat-form">
      <input type="hidden" name="id" id="cat-id" value="0">

      <div style="margin-bottom:.65rem">
        <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Tên danh mục <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" id="cat-name" class="form-inp" required placeholder="VD: Laptop Gaming">
      </div>

      <div style="margin-bottom:.65rem">
        <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Icon (emoji)</label>
        <div style="display:flex;gap:.4rem">
          <input type="text" name="icon" id="cat-icon" class="form-inp" placeholder="💻" style="max-width:70px;text-align:center;font-size:1.2rem">
          <div style="display:flex;gap:.2rem;flex-wrap:wrap;align-items:center">
            <?php foreach(['🖥️','💻','📺','🖱️','⌨️','💾','⚡','🎮','💿','🔧','🎧','📦','🖨️','📱','🔌'] as $em): ?>
            <span onclick="document.getElementById('cat-icon').value='<?= $em ?>'"
                  style="cursor:pointer;font-size:1.1rem;padding:2px;border-radius:4px;transition:.1s"
                  onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background=''">
              <?= $em ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div style="margin-bottom:.65rem">
        <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Mô tả</label>
        <input type="text" name="description" id="cat-desc" class="form-inp" placeholder="Mô tả ngắn...">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.65rem">
        <div>
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Thứ tự hiển thị</label>
          <input type="number" name="sort_order" id="cat-sort" class="form-inp" value="0" min="0">
        </div>
        <div>
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Trạng thái</label>
          <select name="is_active" id="cat-active" class="form-inp">
            <option value="1">Hoạt động</option>
            <option value="0">Tắt</option>
          </select>
        </div>
      </div>

      <div style="display:flex;gap:.4rem">
        <button type="submit" class="btn-r" style="flex:1"><i class="fas fa-save"></i> Lưu</button>
        <button type="button" onclick="catReset()" class="btn-g" title="Đặt lại"><i class="fas fa-rotate-left"></i></button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<script>
function catEdit(cat) {
  document.getElementById('cat-form-title').innerHTML = '<i class="fas fa-edit" style="color:#60a5fa;margin-right:.35rem"></i>Sửa: ' + cat.name;
  document.getElementById('cat-id').value     = cat.id;
  document.getElementById('cat-name').value   = cat.name;
  document.getElementById('cat-icon').value   = cat.icon || '';
  document.getElementById('cat-desc').value   = cat.description || '';
  document.getElementById('cat-sort').value   = cat.sort_order || 0;
  document.getElementById('cat-active').value = cat.is_active ? '1' : '0';
  document.getElementById('cat-form').scrollIntoView({behavior:'smooth',block:'start'});
}
function catReset() {
  document.getElementById('cat-form-title').innerHTML = '<i class="fas fa-plus" style="color:var(--red);margin-right:.35rem"></i>Thêm danh mục';
  document.getElementById('cat-id').value     = '0';
  document.getElementById('cat-form').reset();
}
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
