<?php
require_once __DIR__.'/../Models/ProductModel.php';
require_once __DIR__.'/../Models/Models.php';

class ProductController {
    private $model;
    private $catModel;
    public function __construct() { $this->model=new ProductModel(); $this->catModel=new CategoryModel(); }

    public function index($categorySlug=null) {
        $category=null;
        if($categorySlug) $category=$this->catModel->getBySlug($categorySlug);
        $page=max(1,(int)(isset($_GET['page'])?$_GET['page']:1));
        $filters=array(
            'category'    => $categorySlug,
            'search'      => isset($_GET['q'])?$_GET['q']:'',
            'sort'        => isset($_GET['sort'])?$_GET['sort']:'newest',
            'min_price'   => isset($_GET['min_price'])?$_GET['min_price']:'',
            'max_price'   => isset($_GET['max_price'])?$_GET['max_price']:'',
            'is_featured' => !empty($_GET['is_featured']),
            'is_new'      => !empty($_GET['is_new']),
            'brand'       => isset($_GET['brand'])?(int)$_GET['brand']:0,
        );
        $limit=ITEMS_PER_PAGE;
        $brands=$this->model->getBrandsForFilter(array('category'=>$categorySlug,'search'=>$filters['search']));
        $products=$this->model->getAll($filters,$page,$limit);
        $total=$this->model->countAll($filters);
        $totalPages=(int)ceil($total/$limit);
        $categories=$this->catModel->getAll();
        $pageTitle=$category?htmlspecialchars($category['name']):'Tất cả sản phẩm';
        include __DIR__.'/../Views/products/list.php';
    }

    public function pcBuilder() {
        // Slot key = DB category slug (1:1 mapping, no translation needed)
        $slots = array(
            array('key'=>'man-hinh',   'label'=>'Màn hình',    'icon'=>'fa-desktop'),
            array('key'=>'vo-case',    'label'=>'Vỏ Case',     'icon'=>'fa-box-open'),
            array('key'=>'cpu',        'label'=>'CPU',          'icon'=>'fa-microchip'),
            array('key'=>'mainboard',  'label'=>'Mainboard',   'icon'=>'fa-server'),
            array('key'=>'ram',        'label'=>'RAM',          'icon'=>'fa-memory'),
            array('key'=>'card-do-hoa','label'=>'Card đồ họa', 'icon'=>'fa-display'),
            array('key'=>'ssd-o-cung', 'label'=>'Ổ cứng SSD',  'icon'=>'fa-hard-drive'),
            array('key'=>'tan-nhiet',  'label'=>'Tản nhiệt',   'icon'=>'fa-fan'),
            array('key'=>'nguon',      'label'=>'Nguồn (PSU)', 'icon'=>'fa-plug'),
        );
        $slotMeta    = array();
        $slotProducts = array();
        foreach ($slots as $s) {
            $slotMeta[$s['key']] = array('has_cat' => true);
            $raw = $this->model->getByCategory($s['key'], '', 'newest', '', '', 40);
            $slotProducts[$s['key']] = array();
            foreach ($raw as $p) {
                $specs = array();
                if (!empty($p['specs'])) { $dec = json_decode($p['specs'], true); if (is_array($dec)) $specs = $dec; }
                $slotProducts[$s['key']][] = array(
                    'id'        => (int)$p['id'],
                    'name'      => $p['name'],
                    'slug'      => $p['slug'],
                    'image'     => !empty($p['image']) ? UPLOAD_URL.'/'.$p['image'] : '',
                    'price'     => (float)($p['final_price'] ?? $p['price']),
                    'short_desc'=> $p['short_desc'] ?? '',
                    'specs'     => $specs,
                    'brand'     => $p['brand_name'] ?? '',
                    'sold'      => (int)($p['sold'] ?? 0),
                );
            }
        }
        $categories = $this->catModel->getAll();
        $pageTitle = 'Build PC Gaming';
        include __DIR__.'/../Views/products/pc_builder.php';
    }

    public function detail($slug) {
        if(!$slug){header('Location:'.APP_URL.'/products');exit;}
        $product=$this->model->getBySlug($slug);
        if(!$product){http_response_code(404);$pageTitle='404';include __DIR__.'/../Views/layouts/header.php';echo '<div style="text-align:center;padding:4rem">Sản phẩm không tồn tại. <a href="'.APP_URL.'/products">Quay lại</a></div>';include __DIR__.'/../Views/layouts/footer.php';return;}
        $related=$this->model->getRelated($product['category_id'],$product['id'],4);
        $reviews=$this->model->getReviews($product['id']);
        $categories=$this->catModel->getAll();
        // Fetch multi-images
        $productImages=$this->model->getImages($product['id']);
        $pageTitle=$product['name'];
        include __DIR__.'/../Views/products/detail.php';
    }
}
