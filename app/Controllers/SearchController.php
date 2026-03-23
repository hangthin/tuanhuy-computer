<?php
require_once __DIR__.'/../Models/ProductModel.php';
require_once __DIR__.'/../Models/Models.php';
class SearchController {
    public function index($p=null){
        $q=trim(isset($_GET['q'])?$_GET['q']:'');
        $pm=new ProductModel();$categories=(new CategoryModel())->getAll();
        $page=max(1,(int)(isset($_GET['page'])?$_GET['page']:1));
        $filters=array('search'=>$q,'sort'=>isset($_GET['sort'])?$_GET['sort']:'newest');
        $products=$pm->getAll($filters,$page,ITEMS_PER_PAGE);
        $total=$pm->countAll($filters);$totalPages=(int)ceil($total/ITEMS_PER_PAGE);
        $pageTitle='Tìm kiếm: '.htmlspecialchars($q);$categorySlug=null;$category=null;
        include __DIR__.'/../Views/products/list.php';
    }
}
