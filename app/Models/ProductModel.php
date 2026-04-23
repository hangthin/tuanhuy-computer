<?php
require_once __DIR__ . '/../../config/database.php';

class ProductModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Public listing (exclude soft-deleted) ────────────────────────
    public function getAll($filters = array(), $page = 1, $limit = 12) {
        $where = array('p.is_active=1', 'p.is_deleted=0'); $params = array();
        if (!empty($filters['category'])) { $where[] = 'c.slug=?'; $params[] = $filters['category']; }
        if (!empty($filters['search']))   { $where[] = '(p.name LIKE ? OR p.short_desc LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
        if (!empty($filters['min_price'])) { $where[] = 'COALESCE(p.sale_price,p.price)>=?'; $params[] = $filters['min_price']; }
        if (!empty($filters['max_price'])) { $where[] = 'COALESCE(p.sale_price,p.price)<=?'; $params[] = $filters['max_price']; }
        if (!empty($filters['is_featured'])) { $where[] = 'p.is_featured=1'; }
        if (!empty($filters['is_new']))      { $where[] = 'p.is_new=1'; }
        if (!empty($filters['brand']))       { $where[] = 'p.brand_id=?'; $params[] = (int)$filters['brand']; }
        $sort = isset($filters['sort']) ? $filters['sort'] : 'newest';
        switch ($sort) {
            case 'price_asc':  $order = 'COALESCE(p.sale_price,p.price) ASC'; break;
            case 'price_desc': $order = 'COALESCE(p.sale_price,p.price) DESC'; break;
            case 'bestseller': $order = 'p.sold DESC'; break;
            case 'rating':     $order = 'p.rating DESC'; break;
            default:           $order = 'p.created_at DESC'; break;
        }
        $ws = implode(' AND ', $where);
        $offset = ($page-1)*$limit;
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name,
                    COALESCE(p.sale_price,p.price) AS final_price,
                    CASE WHEN p.sale_price>0 THEN ROUND((1-p.sale_price/p.price)*100) ELSE 0 END AS discount_pct
             FROM products p
             LEFT JOIN categories c ON p.category_id=c.id
             LEFT JOIN brands b ON p.brand_id=b.id
             WHERE {$ws} ORDER BY {$order} LIMIT {$limit} OFFSET {$offset}", $params);
    }

    public function countAll($filters = array()) {
        $where = array('p.is_active=1', 'p.is_deleted=0'); $params = array();
        if (!empty($filters['category'])) { $where[] = 'c.slug=?'; $params[] = $filters['category']; }
        if (!empty($filters['search']))   { $where[] = '(p.name LIKE ? OR p.short_desc LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
        if (!empty($filters['min_price'])) { $where[] = 'COALESCE(p.sale_price,p.price)>=?'; $params[] = $filters['min_price']; }
        if (!empty($filters['max_price'])) { $where[] = 'COALESCE(p.sale_price,p.price)<=?'; $params[] = $filters['max_price']; }
        if (!empty($filters['brand']))     { $where[] = 'p.brand_id=?'; $params[] = (int)$filters['brand']; }
        $ws = implode(' AND ',$where);
        return (int)$this->db->query("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id WHERE {$ws}", $params)->fetchColumn();
    }

    public function getBrandsForFilter($filters = array()) {
        $where = array('p.is_active=1', 'p.is_deleted=0', 'p.brand_id IS NOT NULL');
        $params = array();
        if (!empty($filters['category'])) { $where[] = 'c.slug=?'; $params[] = $filters['category']; }
        if (!empty($filters['search']))   { $where[] = '(p.name LIKE ? OR p.short_desc LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
        $ws = implode(' AND ', $where);
        return $this->db->fetchAll(
            "SELECT b.id, b.name, COUNT(p.id) AS cnt
             FROM products p
             LEFT JOIN categories c ON p.category_id=c.id
             LEFT JOIN brands b ON p.brand_id=b.id
             WHERE {$ws}
             GROUP BY b.id, b.name HAVING cnt>0 ORDER BY b.name ASC", $params);
    }

    public function getBySlug($slug) {
        $p = $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name,
                    COALESCE(p.sale_price,p.price) AS final_price,
                    CASE WHEN p.sale_price>0 THEN ROUND((1-p.sale_price/p.price)*100) ELSE 0 END AS discount_pct,
                    i.stock_quantity
             FROM products p
             LEFT JOIN categories c ON p.category_id=c.id
             LEFT JOIN brands b ON p.brand_id=b.id
             LEFT JOIN inventory i ON p.id=i.product_id
             WHERE p.slug=? AND p.is_active=1 AND p.is_deleted=0 LIMIT 1", array($slug));
        if ($p) $this->db->query("UPDATE products SET views=views+1 WHERE id=?", array($p['id']));
        return $p;
    }

    public function getById($id) {
        return $this->db->fetch(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    COALESCE(p.sale_price,p.price) AS final_price
             FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id
             WHERE p.id=?", array($id));   // không filter is_deleted — admin cần xem
    }

    public function getByCategory($slug, $search='', $sort='newest', $minPrice='', $maxPrice='', $limit=30) {
        return $this->getAll(array(
            'category'=>$slug, 'search'=>$search, 'sort'=>$sort,
            'min_price'=>$minPrice, 'max_price'=>$maxPrice,
        ), 1, $limit);
    }

    public function getFeatured($limit=8)    { return $this->getAll(array('is_featured'=>true),1,$limit); }
    public function getNew($limit=8)         { return $this->getAll(array('is_new'=>true),1,$limit); }
    public function getBestSellers($limit=6) { return $this->getAll(array('sort'=>'bestseller'),1,$limit); }

    public function getRelated($catId,$excludeId,$limit=4) {
        return $this->db->fetchAll(
            "SELECT p.*, COALESCE(p.sale_price,p.price) AS final_price
             FROM products p WHERE p.category_id=? AND p.id!=? AND p.is_active=1 AND p.is_deleted=0
             ORDER BY RAND() LIMIT {$limit}", array($catId,$excludeId));
    }

    public function getReviews($productId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.fullname FROM reviews r JOIN users u ON r.user_id=u.id
             WHERE r.product_id=? AND r.is_approved=1 ORDER BY r.created_at DESC", array($productId));
    }

    // ── Admin listing ─────────────────────────────────────────────────
    /**
     * @param bool $trash  true = chỉ hiện sản phẩm đã xóa mềm
     */
    public function getAllAdmin($search='',$page=1,$limit=20,$categoryId=0,$trash=false) {
        $where = $trash ? 'p.is_deleted=1' : 'p.is_deleted=0';
        $params = array();
        if ($search)      { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
        if ($categoryId>0){ $where .= ' AND p.category_id=?'; $params[] = $categoryId; }
        $offset = ($page-1)*$limit;
        return $this->db->fetchAll(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    COALESCE(p.sale_price,p.price) AS final_price,
                    COALESCE(p.sale_price,0) AS sale_price_val
             FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id
             WHERE {$where} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}", $params);
    }

    public function countAdmin($search='',$categoryId=0,$trash=false) {
        $where = $trash ? 'p.is_deleted=1' : 'p.is_deleted=0';
        $params = array();
        if ($search)      { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
        if ($categoryId>0){ $where .= ' AND p.category_id=?'; $params[] = $categoryId; }
        return (int)$this->db->query("SELECT COUNT(*) FROM products p WHERE {$where}",$params)->fetchColumn();
    }

    public function restore($id) {
        $this->db->query("UPDATE products SET is_deleted=0, deleted_at=NULL, deleted_by=NULL WHERE id=?", array($id));
    }

    // ── Write operations ──────────────────────────────────────────────
    public function create($d) {
        $this->db->query(
            "INSERT INTO products (category_id,brand_id,name,slug,sku,short_desc,description,price,sale_price,stock,image,is_featured,is_new,warranty,is_active,is_deleted)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,0)",
            array($d['category_id'],isset($d['brand_id'])?$d['brand_id']:null,$d['name'],$d['slug'],
                  isset($d['sku'])?$d['sku']:'',$d['short_desc']??'',$d['description']??'',$d['price'],
                  !empty($d['sale_price'])?(float)$d['sale_price']:null,$d['stock']??0,
                  isset($d['image'])?$d['image']:'default.jpg',$d['is_featured']?1:0,$d['is_new']?1:0,$d['warranty']??12));
        $id = (int)$this->db->lastInsertId();
        $this->db->query("INSERT INTO inventory (product_id,stock_quantity,min_stock) VALUES (?,?,5) ON DUPLICATE KEY UPDATE stock_quantity=?",
            array($id,$d['stock']??0,$d['stock']??0));
        return $id;
    }

    public function update($id,$d) {
        $this->db->query(
            "UPDATE products SET category_id=?,brand_id=?,name=?,slug=?,sku=?,short_desc=?,description=?,
             price=?,sale_price=?,stock=?,is_featured=?,is_new=?,warranty=?,updated_at=NOW() WHERE id=?",
            array($d['category_id'],isset($d['brand_id'])?$d['brand_id']:null,$d['name'],$d['slug'],
                  $d['sku']??'',$d['short_desc']??'',$d['description']??'',$d['price'],
                  !empty($d['sale_price'])?(float)$d['sale_price']:null,$d['stock']??0,
                  $d['is_featured']?1:0,$d['is_new']?1:0,$d['warranty']??12,$id));
        $this->db->query("UPDATE inventory SET stock_quantity=? WHERE product_id=?",array($d['stock']??0,$id));
    }

    public function updateImage($id,$image) { $this->db->query("UPDATE products SET image=? WHERE id=?",array($image,$id)); }

    // ── Multi-image support ───────────────────────────────────────────
    public function getImages($productId){
        $this->ensureImgTable();
        try {
            return $this->db->fetchAll(
                "SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, id ASC",
                array((int)$productId));
        } catch(\Exception $e){ return array(); }
    }

    private function ensureImgTable(){
        $this->db->query("CREATE TABLE IF NOT EXISTS `product_images` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `image` varchar(255) NOT NULL,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`), KEY `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function addImage($productId,$filename,$sortOrder=0){
        try{
            $this->ensureImgTable();
            $this->db->query("INSERT INTO product_images (product_id,image,sort_order) VALUES (?,?,?)",array((int)$productId,$filename,(int)$sortOrder));
            return (int)$this->db->lastInsertId();
        }catch(\Exception $e){ return 0; }
    }

    public function deleteImage($imageId){
        try{
            $row=$this->db->fetch("SELECT image FROM product_images WHERE id=?",array((int)$imageId));
            if($row&&$row['image']&&file_exists(UPLOAD_PATH.$row['image'])) @unlink(UPLOAD_PATH.$row['image']);
            $this->db->query("DELETE FROM product_images WHERE id=?",array((int)$imageId));
        }catch(\Exception $e){}
    }

    public function deleteAllImages($productId){
        try{
            $rows=$this->db->fetchAll("SELECT image FROM product_images WHERE product_id=?",array((int)$productId));
            foreach($rows as $r){ if($r['image']&&file_exists(UPLOAD_PATH.$r['image'])) @unlink(UPLOAD_PATH.$r['image']); }
            $this->db->query("DELETE FROM product_images WHERE product_id=?",array((int)$productId));
        }catch(\Exception $e){}
    }
}
