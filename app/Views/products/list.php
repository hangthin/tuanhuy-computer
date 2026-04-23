<?php require_once __DIR__.'/../layouts/header.php'; ?>

<div style="max-width:1280px;margin:1.25rem auto;padding:0 1rem;display:grid;grid-template-columns:220px 1fr;gap:1.25rem">

<!-- SIDEBAR -->
<aside style="background:#fff;border-radius:12px;padding:1.1rem;height:fit-content;border:1px solid #eee;position:sticky;top:75px">
  <h3 style="font-weight:800;color:#111;font-size:.9rem;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem"><i class="fa-solid fa-filter" style="color:var(--red)"></i> Bộ lọc</h3>
  <form method="GET" id="filter-form">
    <?php if(!empty($categorySlug)): ?><input type="hidden" name="cat" value="<?= $categorySlug ?>"><?php endif; ?>
    <?php if(!empty($filters['search'])): ?><input type="hidden" name="q" value="<?= htmlspecialchars($filters['search']) ?>"><?php endif; ?>
    <?php if(!empty($filters['brand'])): ?><input type="hidden" name="brand" value="<?= (int)$filters['brand'] ?>"><?php endif; ?>

    <!-- Sort -->
    <div style="margin-bottom:.9rem">
      <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:.4rem">Sắp xếp</div>
      <?php foreach(array(array('newest','Mới nhất'),array('price_asc','Giá tăng dần'),array('price_desc','Giá giảm dần'),array('bestseller','Bán chạy'),array('rating','Đánh giá cao')) as $sItem): $sv=$sItem[0];$sl=$sItem[1]; ?>
      <label style="display:flex;align-items:center;gap:.45rem;padding:.2rem 0;cursor:pointer;font-size:.82rem">
        <input type="radio" name="sort" value="<?= $sv ?>" <?= ($filters['sort']??'newest')===$sv?'checked':'' ?> onchange="document.getElementById('filter-form').submit()" style="accent-color:var(--red)">
        <?= $sl ?>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- Price -->
    <div style="margin-bottom:.9rem">
      <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:.4rem">Khoảng giá</div>
      <div style="display:flex;gap:.35rem">
        <input type="number" name="min_price" value="<?= $filters['min_price']??'' ?>" placeholder="Từ" class="form-input" style="font-size:.75rem;padding:.35rem .5rem">
        <input type="number" name="max_price" value="<?= $filters['max_price']??'' ?>" placeholder="Đến" class="form-input" style="font-size:.75rem;padding:.35rem .5rem">
      </div>
    </div>

    <!-- Categories -->
    <div style="margin-bottom:.9rem">
      <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:.4rem">Danh mục</div>
      <a href="<?= APP_URL ?>/products" style="display:block;padding:.2rem 0;font-size:.82rem;color:<?= !$category?'var(--red)':'#555' ?>;text-decoration:none">Tất cả</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= APP_URL ?>/products/<?= $cat['slug'] ?>" style="display:block;padding:.2rem 0;font-size:.82rem;color:<?= (isset($category['id'])&&$category['id']==$cat['id'])?'var(--red)':'#555' ?>;text-decoration:none" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='<?= (isset($category['id'])&&$category['id']==$cat['id'])?'var(--red)':'#555' ?>'">
        <i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-tag') ?>" style="width:14px;text-align:center;font-size:.75rem"></i> <?= htmlspecialchars($cat['name']) ?> <span style="color:#ccc;font-size:.7rem">(<?= $cat['product_count'] ?>)</span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Brands -->
    <?php if(!empty($brands)): ?>
    <div style="margin-bottom:.9rem">
      <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:.4rem">Thương hiệu</div>
      <?php foreach($brands as $br): $brActive=(int)($filters['brand']??0)===(int)$br['id']; ?>
      <label style="display:flex;align-items:center;justify-content:space-between;gap:.45rem;padding:.18rem 0;cursor:pointer;font-size:.82rem">
        <span style="display:flex;align-items:center;gap:.45rem">
          <input type="radio" name="brand" value="<?= $br['id'] ?>"
                 <?= $brActive?'checked':'' ?>
                 onchange="document.getElementById('filter-form').submit()"
                 style="accent-color:var(--red)">
          <span style="color:<?= $brActive?'var(--red)':'#333' ?>"><?= htmlspecialchars($br['name']) ?></span>
        </span>
        <span style="font-size:.68rem;color:#bbb">(<?= $br['cnt'] ?>)</span>
      </label>
      <?php endforeach; ?>
      <?php if(!empty($filters['brand'])): ?>
      <a href="?<?= http_build_query(array_merge($_GET,array('brand'=>'','page'=>1))) ?>"
         style="font-size:.72rem;color:#999;text-decoration:none;display:inline-block;margin-top:.2rem">
        <i class="fa-solid fa-xmark" style="font-size:.65rem"></i> Bỏ lọc thương hiệu
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Status -->
    <div style="margin-bottom:.9rem">
      <div style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:.4rem">Trạng thái</div>
      <label style="display:flex;align-items:center;gap:.45rem;font-size:.82rem;cursor:pointer;padding:.15rem 0">
        <input type="checkbox" name="is_new" value="1" <?= !empty($filters['is_new'])?'checked':'' ?> onchange="this.form.submit()" style="accent-color:var(--red)"> Hàng mới
      </label>
      <label style="display:flex;align-items:center;gap:.45rem;font-size:.82rem;cursor:pointer;padding:.15rem 0">
        <input type="checkbox" name="is_featured" value="1" <?= !empty($filters['is_featured'])?'checked':'' ?> onchange="this.form.submit()" style="accent-color:var(--red)"> Nổi bật
      </label>
    </div>

    <button type="submit" class="btn-red" style="width:100%;padding:.45rem">Áp dụng</button>
    <a href="<?= APP_URL ?>/products<?= !empty($categorySlug)?'/'.$categorySlug:'' ?>" style="display:block;text-align:center;color:#999;font-size:.75rem;margin-top:.4rem;text-decoration:none">Xóa bộ lọc</a>
  </form>
</aside>

<!-- MAIN -->
<main>
  <!-- Breadcrumb + Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.9rem">
    <div>
      <div style="font-size:.75rem;color:#999;margin-bottom:.15rem">
        <a href="<?= APP_URL ?>/" style="color:#999;text-decoration:none">Trang chủ</a> /
        <?php if($category): ?>
        <a href="<?= APP_URL ?>/products" style="color:#999;text-decoration:none">Sản phẩm</a> /
        <span style="color:#111"><?= htmlspecialchars($category['name']) ?></span>
        <?php else: ?><span style="color:#111">Tất cả sản phẩm</span><?php endif; ?>
      </div>
      <h1 style="font-size:1.1rem;font-weight:800;color:#111">
        <?= htmlspecialchars($pageTitle) ?>
        <span style="font-size:.85rem;font-weight:400;color:#999">(<?= $total ?> SP)</span>
      </h1>
    </div>
    <div style="display:flex;gap:.35rem;flex-wrap:wrap">
      <?php
        $_sq=array_filter(array('cat'=>$categorySlug?:null,'q'=>$filters['search']?:null,'brand'=>$filters['brand']?:null,'min_price'=>$filters['min_price']?:null,'max_price'=>$filters['max_price']?:null,'is_new'=>!empty($filters['is_new'])?1:null,'is_featured'=>!empty($filters['is_featured'])?1:null));
        foreach(array(array('newest','Mới nhất'),array('price_asc','Giá ↑'),array('price_desc','Giá ↓'),array('bestseller','Hot')) as $sTab): $sv=$sTab[0];$sl=$sTab[1]; ?>
      <a href="?<?= http_build_query(array_merge($_sq,array('sort'=>$sv))) ?>" style="padding:.28rem .65rem;border-radius:99px;font-size:.75rem;text-decoration:none;border:1px solid;<?= ($filters['sort']??'newest')===$sv?'background:var(--red);color:#fff;border-color:var(--red)':'background:#fff;color:#555;border-color:#e5e5e5' ?>"><?= $sl ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Grid -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.85rem">
    <?php if(empty($products)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:4rem 1rem;color:#999">
      <div style="margin-bottom:1rem"><i class="fa-solid fa-box-open" style="font-size:2.5rem;color:#ddd"></i></div>
      <p style="font-size:1rem">Không tìm thấy sản phẩm nào.</p>
      <a href="<?= APP_URL ?>/products" class="btn-red" style="display:inline-block;margin-top:1rem">Xem tất cả</a>
    </div>
    <?php else: ?>
    <?php foreach($products as $p): include __DIR__.'/product_card.php'; endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if($totalPages>1):
    $_pq=array_filter(array('sort'=>$filters['sort']??'newest','brand'=>$filters['brand']?:null,'min_price'=>$filters['min_price']?:null,'max_price'=>$filters['max_price']?:null,'is_new'=>!empty($filters['is_new'])?1:null,'is_featured'=>!empty($filters['is_featured'])?1:null));
    if(!empty($categorySlug)) $_pq['cat']=$categorySlug;
    if(!empty($filters['search'])) $_pq['q']=$filters['search'];
    $_pqs=http_build_query(array_filter($_pq));
  ?>
  <div style="display:flex;justify-content:center;gap:.35rem;margin-top:1.5rem;flex-wrap:wrap">
    <?php if($page>1): ?><a href="?page=<?= $page-1 ?>&<?= $_pqs ?>" style="width:36px;height:36px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid #e5e5e5;background:#fff;text-decoration:none;color:#555">‹</a><?php endif; ?>
    <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
    <a href="?page=<?= $i ?>&<?= $_pqs ?>" style="width:36px;height:36px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid;text-decoration:none;font-size:.85rem;<?= $i===$page?'background:var(--red);color:#fff;border-color:var(--red)':'background:#fff;color:#555;border-color:#e5e5e5' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($page<$totalPages): ?><a href="?page=<?= $page+1 ?>&<?= $_pqs ?>" style="width:36px;height:36px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid #e5e5e5;background:#fff;text-decoration:none;color:#555">›</a><?php endif; ?>
  </div>
  <?php endif; ?>
</main>
</div>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
