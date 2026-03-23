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
        );
        $limit=ITEMS_PER_PAGE;
        $products=$this->model->getAll($filters,$page,$limit);
        $total=$this->model->countAll($filters);
        $totalPages=(int)ceil($total/$limit);
        $categories=$this->catModel->getAll();
        $pageTitle=$category?htmlspecialchars($category['name']):'Tất cả sản phẩm';
        include __DIR__.'/../Views/products/list.php';
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
