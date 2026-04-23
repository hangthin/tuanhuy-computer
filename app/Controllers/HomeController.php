<?php
require_once __DIR__.'/../Models/ProductModel.php';
require_once __DIR__.'/../Models/Models.php';

class HomeController {
    public function index($p=null) {
        $pm=$c=new ProductModel();
        $cm=new CategoryModel();
        $featured    = $pm->getFeatured(10);
        $newProducts  = $pm->getNew(10);
        $newArrivals  = $newProducts; // alias expected by views
        $bestSellers = $pm->getBestSellers(8);
        $categories  = $cm->getAll();

        // Flash sale: products with active discount
        $db = Database::getInstance();
        $saleProducts = $db->fetchAll(
            "SELECT p.*, c.slug AS category_slug, c.name AS category_name,
                    b.name AS brand_name,
                    COALESCE(p.sale_price,p.price) AS final_price,
                    ROUND((1-p.sale_price/p.price)*100) AS discount_pct
             FROM products p
             LEFT JOIN categories c ON p.category_id=c.id
             LEFT JOIN brands b ON p.brand_id=b.id
             WHERE p.sale_price IS NOT NULL AND p.sale_price>0 AND p.sale_price<p.price
               AND p.is_active=1 AND p.is_deleted=0
             ORDER BY discount_pct DESC LIMIT 12"
        );

        // Per-category product rows
        $catSlugs = array('may-tinh-pc','laptop','man-hinh','chuot','ban-phim');
        $catProducts = array();
        foreach($catSlugs as $_slug){
            $catProducts[$_slug] = $pm->getByCategory($_slug,'','bestseller','','',6);
        }

        include __DIR__.'/../Views/home/index.php';
    }
    public function about($p=null) {
        $pageTitle = 'Giới thiệu';
        $metaDesc  = 'Tuấn Huy Computer – Chuyên cung cấp PC Gaming, Laptop, linh kiện máy tính chính hãng từ năm 2015. Bảo hành chính hãng, giao hàng toàn quốc.';
        include __DIR__.'/../Views/home/about.php';
    }

    public function contact($p=null) {
        $pageTitle = 'Liên hệ';
        $metaDesc  = 'Liên hệ với Tuấn Huy Computer – Hotline, email, địa chỉ cửa hàng và form gửi tin nhắn trực tuyến.';
        include __DIR__.'/../Views/home/contact.php';
    }

    public function page404($p=null) {
        http_response_code(404);
        $pageTitle='404';
        include __DIR__.'/../Views/layouts/header.php';
        echo '<div style="text-align:center;padding:5rem 1rem"><div style="font-size:5rem;font-weight:900;color:var(--red)">404</div><p style="margin-bottom:1.25rem">Trang không tồn tại.</p><a href="'.APP_URL.'/" class="btn-red">Về trang chủ</a></div>';
        include __DIR__.'/../Views/layouts/footer.php';
    }
}
