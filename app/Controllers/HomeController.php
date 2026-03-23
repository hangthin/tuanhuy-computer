<?php
require_once __DIR__.'/../Models/ProductModel.php';
require_once __DIR__.'/../Models/Models.php';

class HomeController {
    public function index($p=null) {
        $pm=$c=new ProductModel();
        $cm=new CategoryModel();
        $featured    = $pm->getFeatured(8);
        $newProducts = $pm->getNew(8);
        $bestSellers = $pm->getBestSellers(6);
        $categories  = $cm->getAll();
        include __DIR__.'/../Views/home/index.php';
    }
    public function page404($p=null) {
        http_response_code(404);
        $pageTitle='404';
        include __DIR__.'/../Views/layouts/header.php';
        echo '<div style="text-align:center;padding:5rem 1rem"><div style="font-size:5rem;font-weight:900;color:var(--red)">404</div><p style="margin-bottom:1.25rem">Trang không tồn tại.</p><a href="'.APP_URL.'/" class="btn-red">Về trang chủ</a></div>';
        include __DIR__.'/../Views/layouts/footer.php';
    }
}
