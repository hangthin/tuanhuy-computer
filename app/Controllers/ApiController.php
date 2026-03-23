<?php
require_once __DIR__.'/../Models/Models.php';
require_once __DIR__.'/../Models/ProductModel.php';

class ApiController {

    public function __construct() {
        header('Content-Type: application/json; charset=utf-8');
    }

    // ── AUTH ──────────────────────────────────────────────────
    public function auth($action = null) {
        $b = json_decode(file_get_contents('php://input'), true) ?? array();

        if ($action === 'login') {
            $em = trim($b['email'] ?? '');
            $pw = $b['password'] ?? '';
            if (!$em || !$pw) { echo json_encode(array('success'=>false,'message'=>'Vui lòng nhập đầy đủ')); return; }
            $um = new UserModel();
            $u  = $um->findByEmail($em);
            $passOk = $u && (password_verify($pw,$u['password']) || $pw === $u['password']);
            if (!$passOk) { echo json_encode(array('success'=>false,'message'=>'Email hoặc mật khẩu không đúng')); return; }
            if (!$u['is_active']) { echo json_encode(array('success'=>false,'message'=>'Tài khoản đã bị khóa')); return; }
            $_SESSION['user_id']    = $u['id'];
            $_SESSION['user_name']  = $u['fullname'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['user_role']  = $u['role'];
            $um->updateLastLogin($u['id']);
            (new CartModel())->mergeGuestCart($u['id']);
            echo json_encode(array('success'=>true,'name'=>$u['fullname'],'role'=>$u['role'])); return;
        }

        if ($action === 'register') {
            $n  = trim($b['fullname'] ?? '');
            $em = trim($b['email'] ?? '');
            $ph = trim($b['phone'] ?? '');
            $pw = $b['password'] ?? '';
            if (!$n || !$em || !$pw) { echo json_encode(array('success'=>false,'message'=>'Vui lòng điền đầy đủ')); return; }
            if (!filter_var($em, FILTER_VALIDATE_EMAIL)) { echo json_encode(array('success'=>false,'message'=>'Email không hợp lệ')); return; }
            if (strlen($pw) < 6) { echo json_encode(array('success'=>false,'message'=>'Mật khẩu ít nhất 6 ký tự')); return; }
            $um = new UserModel();
            if ($um->findByEmail($em)) { echo json_encode(array('success'=>false,'message'=>'Email đã được đăng ký')); return; }
            $id = $um->create(array('fullname'=>$n,'email'=>$em,'phone'=>$ph,'password'=>$pw));
            $_SESSION['user_id']    = $id;
            $_SESSION['user_name']  = $n;
            $_SESSION['user_email'] = $em;
            $_SESSION['user_role']  = 0;
            (new CartModel())->mergeGuestCart($id);
            echo json_encode(array('success'=>true,'name'=>$n)); return;
        }

        echo json_encode(array('success'=>false,'message'=>'Unknown'));
    }

    // ── CART ──────────────────────────────────────────────────
    public function cart($action = null) {
        $b = json_decode(file_get_contents('php://input'), true) ?? array();

        if ($action === 'add') {
            $pid = (int)($b['product_id'] ?? 0);
            $qty = max(1, (int)($b['quantity'] ?? 1));
            if (!$pid) { echo json_encode(array('success'=>false,'message'=>'Sản phẩm không hợp lệ')); return; }
            $p = (new ProductModel())->getById($pid);
            if (!$p || $p['stock'] < 1) { echo json_encode(array('success'=>false,'message'=>'Sản phẩm hết hàng')); return; }
            (new CartModel())->add($pid, $qty);
            echo json_encode(array('success'=>true,'cart_count'=>getCartCount())); return;
        }

        if ($action === 'update') {
            $cid  = (int)($b['cart_id'] ?? 0);
            $qty  = (int)($b['quantity'] ?? 1);
            $cart = new CartModel();
            $cart->update($cid, $qty);
            $items = $cart->getItems();
            $sub   = calcCartSubtotal($items);
            $ship  = $sub >= 500000 ? 0 : 30000;
            $is    = 0;
            foreach ($items as $i) { if ($i['id'] == $cid) { $is = $i['unit_price'] * $qty; break; } }
            echo json_encode(array('success'=>true,'subtotal'=>$sub,'shipping'=>$ship,'total'=>$sub+$ship,'cart_count'=>getCartCount(),'item_subtotal'=>$is)); return;
        }

        if ($action === 'remove') {
            (new CartModel())->remove((int)($b['cart_id'] ?? 0));
            echo json_encode(array('success'=>true,'cart_count'=>getCartCount())); return;
        }

        echo json_encode(array('success'=>false,'message'=>'Unknown'));
    }

    // ── COUPON ────────────────────────────────────────────────
    public function coupon($action = null) {
        $b    = json_decode(file_get_contents('php://input'), true) ?? array();
        $code = strtoupper(trim($b['code'] ?? ''));
        $db   = Database::getInstance();
        $c    = $db->fetch("SELECT * FROM coupons WHERE code=? AND is_active=1", array($code));
        if (!$c) { echo json_encode(array('success'=>false,'message'=>'Mã không hợp lệ')); return; }
        if ($c['expires_at'] && strtotime($c['expires_at']) < time()) { echo json_encode(array('success'=>false,'message'=>'Mã đã hết hạn')); return; }
        if ($c['used_count'] >= $c['usage_limit']) { echo json_encode(array('success'=>false,'message'=>'Mã đã hết lượt')); return; }
        $items = (new CartModel())->getItems();
        $sub   = calcCartSubtotal($items);
        if ($sub < $c['min_order']) { echo json_encode(array('success'=>false,'message'=>'Đơn tối thiểu '.formatPrice($c['min_order']))); return; }
        $disc = $c['type'] === 'percent' ? $sub * $c['value'] / 100 : (float)$c['value'];
        if ($c['max_discount']) $disc = min($disc, (float)$c['max_discount']);
        $ship = $sub >= 500000 ? 0 : 30000;
        echo json_encode(array('success'=>true,'message'=>'Giảm '.formatPrice($disc),'discount'=>$disc,'new_total'=>max(0,$sub+$ship-$disc)));
    }

    // ── REVIEW ────────────────────────────────────────────────
    public function review($action = null) {
        if (!isLoggedIn()) { setFlash('error','Vui lòng đăng nhập'); header('Location:'.APP_URL.'/'); exit; }
        $pid     = (int)($_POST['product_id'] ?? 0);
        $rating  = min(5, max(1, (int)($_POST['rating'] ?? 5)));
        $title   = sanitize($_POST['title'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        if (!$pid) { header('Location:'.APP_URL.'/'); exit; }
        $db = Database::getInstance();
        $db->query("INSERT INTO reviews (product_id,user_id,rating,title,content) VALUES (?,?,?,?,?)",
            array($pid, $_SESSION['user_id'], $rating, $title, $content));
        $avg = $db->fetch("SELECT AVG(rating) r, COUNT(*) c FROM reviews WHERE product_id=? AND is_approved=1", array($pid));
        $db->query("UPDATE products SET rating=?, review_count=? WHERE id=?", array($avg['r'], $avg['c'], $pid));
        setFlash('success','Cảm ơn đánh giá của bạn!');
        header('Location:'.($_SERVER['HTTP_REFERER'] ?? APP_URL.'/')); exit;
    }

    // ── AI ────────────────────────────────────────────────────
    public function ai($action = null) {
        requireAdmin();

        // Test kết nối
        if ($action === 'test') {
            $key   = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
            $model = defined('AI_MODEL') && AI_MODEL ? AI_MODEL : 'llama-3.2-11b-vision-preview';
            if (!$key) { echo json_encode(array('ok'=>false,'msg'=>'Chưa cấu hình AI_API_KEY trong config/app.php')); return; }
            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$key,
                ),
                CURLOPT_POSTFIELDS => json_encode(array(
                    'model'      => $model,
                    'max_tokens' => 10,
                    'messages'   => array(array('role'=>'user','content'=>'hi')),
                )),
            ));
            $tr = curl_exec($ch);
            $tc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $td = json_decode($tr, true);
            $ok = ($tc === 200 && isset($td['choices']));
            echo json_encode(array('ok'=>$ok,'model'=>$model,'http'=>$tc,'msg'=>$ok ? 'Groq AI kết nối OK' : 'Lỗi: '.($td['error']['message'] ?? 'HTTP '.$tc)));
            return;
        }

        // Lưu ảnh
        if ($action === 'save-image') {
            try {
                $b       = json_decode(file_get_contents('php://input'), true) ?? array();
                $imgB64  = $b['image_b64'] ?? '';
                $imgUrl  = $b['image_url'] ?? '';
                $imgMime = $b['image_mime'] ?? 'image/jpeg';
                $extMap  = array('image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif');
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);

                if ($imgB64) {
                    $raw     = preg_replace('/^data:[^;]+;base64,/i', '', $imgB64);
                    $raw     = preg_replace('/\s+/', '', $raw);
                    $decoded = base64_decode($raw, true);
                    if (!$decoded) { echo json_encode(array('success'=>false,'message'=>'Dữ liệu ảnh không hợp lệ')); return; }
                    $ext   = $extMap[$imgMime] ?? 'jpg';
                    $fname = 'ai_'.uniqid().'.'.$ext;
                    file_put_contents(UPLOAD_PATH.$fname, $decoded);
                    echo json_encode(array('success'=>true,'filename'=>$fname,'url'=>UPLOAD_URL.$fname)); return;
                }

                if ($imgUrl && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                    $ctx  = stream_context_create(array('http'=>array('timeout'=>15,'user_agent'=>'Mozilla/5.0')));
                    $data = @file_get_contents($imgUrl, false, $ctx);
                    if (!$data) { echo json_encode(array('success'=>false,'message'=>'Không tải được ảnh từ URL')); return; }
                    $ext = 'jpg';
                    if (function_exists('finfo_buffer')) {
                        $fi   = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_buffer($fi, $data);
                        finfo_close($fi);
                        $ext  = $extMap[$mime] ?? 'jpg';
                    }
                    $fname = 'ai_'.uniqid().'.'.$ext;
                    file_put_contents(UPLOAD_PATH.$fname, $data);
                    echo json_encode(array('success'=>true,'filename'=>$fname,'url'=>UPLOAD_URL.$fname)); return;
                }

                echo json_encode(array('success'=>false,'message'=>'Không có dữ liệu ảnh'));
            } catch (Exception $e) {
                echo json_encode(array('success'=>false,'message'=>'Lỗi lưu ảnh: '.$e->getMessage()));
            }
            return;
        }

        // Lưu sản phẩm
        if ($action === 'save-product') {
            try {
                $b  = json_decode(file_get_contents('php://input'), true) ?? array();
                if (empty($b['name'])) { echo json_encode(array('success'=>false,'message'=>'Thiếu tên sản phẩm')); return; }
                $pm   = new ProductModel();
                $db   = Database::getInstance();
                $name = sanitize($b['name']);
                $slug = makeSlug($name).'-'.rand(100, 999);

                // Validate category_id
                $catId = (int)($b['category_id'] ?? 0);
                if ($catId > 0) {
                    $exists = $db->fetch("SELECT id FROM categories WHERE id=? AND is_active=1", array($catId));
                    if (!$exists) $catId = 0;
                }
                if ($catId <= 0) {
                    $first = $db->fetch("SELECT id FROM categories WHERE is_active=1 ORDER BY id ASC LIMIT 1");
                    $catId = $first ? $first['id'] : 1;
                }

                // Validate brand_id
                $brandId = null;
                if (!empty($b['brand_id'])) {
                    $bid = (int)$b['brand_id'];
                    if ($bid > 0) {
                        $exists  = $db->fetch("SELECT id FROM brands WHERE id=?", array($bid));
                        $brandId = $exists ? $bid : null;
                    }
                }

                $specs = null;
                if (!empty($b['specs']) && is_array($b['specs'])) {
                    $specs = json_encode($b['specs'], JSON_UNESCAPED_UNICODE);
                }

                $pid = $pm->create(array(
                    'category_id' => $catId,
                    'brand_id'    => $brandId,
                    'name'        => $name,
                    'slug'        => $slug,
                    'sku'         => $this->makeUniqueSku($db, sanitize($b['sku'] ?? '')),
                    'short_desc'  => sanitize($b['short_desc'] ?? ''),
                    'description' => sanitize($b['description'] ?? ''),
                    'price'       => min(100000000, max(1000000, (float)($b['price'] ?? 0))),
                    'sale_price'  => (!empty($b['sale_price']) && (float)$b['sale_price'] > 0) ? (float)$b['sale_price'] : null,
                    'stock'       => (int)($b['stock'] ?? 10),
                    'image'       => sanitize($b['image_filename'] ?? '') ?: null,
                    'is_featured' => !empty($b['is_featured']) ? 1 : 0,
                    'is_new'      => 1,
                    'warranty'    => (int)($b['warranty'] ?? 12),
                ));
                if ($specs) $db->query("UPDATE products SET specs=? WHERE id=?", array($specs, $pid));
                echo json_encode(array('success'=>true,'product_id'=>$pid,'message'=>'Đã lưu sản phẩm thành công!'));
            } catch (Exception $e) {
                echo json_encode(array('success'=>false,'message'=>'Lỗi DB: '.$e->getMessage()));
            }
            return;
        }

        // Kiểm tra trùng
        if ($action === 'check-duplicate') {
            $b         = json_decode(file_get_contents('php://input'), true) ?? array();
            $name      = trim($b['name'] ?? '');
            $sku       = trim($b['sku'] ?? '');
            $excludeId = (int)($b['exclude_id'] ?? 0);
            if (!$name) { echo json_encode(array('duplicate'=>false)); return; }
            $db = Database::getInstance();

            if ($sku) {
                $q = "SELECT id,name FROM products WHERE sku=? AND is_active=1".($excludeId ? " AND id!=?" : '');
                $r = $db->fetch($q, $excludeId ? array($sku,$excludeId) : array($sku));
                if ($r) { echo json_encode(array('duplicate'=>true,'type'=>'sku','message'=>'SKU "'.$sku.'" đã tồn tại: '.$r['name'])); return; }
            }

            $q2 = "SELECT id,name FROM products WHERE LOWER(name)=LOWER(?) AND is_active=1".($excludeId ? " AND id!=?" : '');
            $r2 = $db->fetch($q2, $excludeId ? array($name,$excludeId) : array($name));
            if ($r2) { echo json_encode(array('duplicate'=>true,'type'=>'exact','message'=>'Sản phẩm "'.$r2['name'].'" đã tồn tại')); return; }

            $genericWords = array('keyboard','laptop','pc','gaming','máy','tính','bàn','phím','chuột','màn','hình','tai','nghe','mainboard','case','nguồn','ổ','cứng','ram','cpu','gpu','card','đồ','họa','ssd','hdd','monitor','mouse','headset','headphone','speaker','loa','micro','webcam','hub','switch','router','printer','computer','desktop','notebook');
            $words = array_filter(
                preg_split('/[\s\-\/,\.]+/u', mb_strtolower($name)),
                function($w) use ($genericWords) {
                    return mb_strlen($w) >= 3 && !in_array($w, $genericWords) && (preg_match('/\d/', $w) || mb_strlen($w) >= 5);
                }
            );
            $specific = array_slice(array_values($words), 0, 4);

            if (count($specific) >= 2) {
                $likes  = implode(' AND ', array_map(function($w){ return "LOWER(name) LIKE ?"; }, $specific));
                $params = array_map(function($w){ return '%'.$w.'%'; }, $specific);
                if ($excludeId) $params[] = $excludeId;
                $similar = $db->fetchAll("SELECT id,name FROM products WHERE is_active=1 AND ".$likes.($excludeId ? ' AND id!=?' : '')." LIMIT 2", $params);
                if (!empty($similar)) {
                    $names = implode(', ', array_map(function($s){ return '"'.$s['name'].'"'; }, array_slice($similar, 0, 2)));
                    echo json_encode(array('duplicate'=>true,'type'=>'similar','message'=>'Sản phẩm tương tự đã tồn tại: '.$names)); return;
                }
            } elseif (count($specific) === 1) {
                $w = $specific[0];
                if (preg_match('/\d/', $w) && mb_strlen($w) >= 6) {
                    $q3 = "SELECT id,name FROM products WHERE LOWER(name) LIKE ? AND is_active=1".($excludeId ? " AND id!=?" : '');
                    $r3 = $db->fetch($q3, $excludeId ? array('%'.$w.'%',$excludeId) : array('%'.$w.'%'));
                    if ($r3) { echo json_encode(array('duplicate'=>true,'type'=>'model','message'=>'Mã model "'.$w.'" đã tồn tại: '.$r3['name'])); return; }
                }
            }

            echo json_encode(array('duplicate'=>false)); return;
        }

        // AI từ tên sản phẩm
        if ($action === 'generate-from-name') {
            $b      = json_decode(file_get_contents('php://input'), true) ?? array();
            $pName  = trim($b['product_name'] ?? '');
            if (!$pName) { echo json_encode(array('success'=>false,'message'=>'Cần nhập tên sản phẩm')); return; }
            $apiKey = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
            if (!$apiKey) { echo json_encode(array('success'=>false,'message'=>'Chưa cấu hình AI_API_KEY')); return; }
            $db     = Database::getInstance();
            $cats   = $db->fetchAll("SELECT id,name,slug FROM categories WHERE is_active=1 ORDER BY id");
            $brands = $db->fetchAll("SELECT id,name FROM brands ORDER BY name");
            echo json_encode($this->aiFromText($pName, $apiKey, $cats, $brands)); return;
        }

        // AI từ ảnh
        if ($action === 'generate') {
            $b       = json_decode(file_get_contents('php://input'), true) ?? array();
            $imgB64  = $b['image_b64'] ?? '';
            $imgMime = $b['image_mime'] ?? 'image/jpeg';
            $imgUrl  = $b['url'] ?? '';
            $apiKey  = defined('AI_API_KEY') && AI_API_KEY ? AI_API_KEY : '';
            if (!$imgB64 && !$imgUrl) { echo json_encode(array('success'=>false,'message'=>'Cần ảnh hoặc URL')); return; }
            if (!$apiKey) { echo json_encode(array('success'=>false,'message'=>'Chưa cấu hình AI_API_KEY')); return; }
            $db     = Database::getInstance();
            $cats   = $db->fetchAll("SELECT id,name,slug FROM categories WHERE is_active=1 ORDER BY id");
            $brands = $db->fetchAll("SELECT id,name FROM brands ORDER BY name");
            echo json_encode($this->aiFromImage($imgB64, $imgMime, $imgUrl, $apiKey, $cats, $brands)); return;
        }

        // Tìm kiếm ảnh — tự động chọn Bing / Pixabay / Google
        if ($action === 'search-image') {
            $b     = json_decode(file_get_contents('php://input'), true) ?? array();
            $query = trim($b['query'] ?? '');
            if (!$query) { echo json_encode(array('success'=>false,'message'=>'Thiếu từ khóa tìm kiếm')); return; }

            $serpKey    = defined('SERPAPI_KEY')        && SERPAPI_KEY       ? SERPAPI_KEY       : '';
            $bingKey    = defined('BING_SEARCH_KEY')   && BING_SEARCH_KEY   ? BING_SEARCH_KEY   : '';
            $pexelsKey  = defined('PEXELS_KEY')        && PEXELS_KEY        ? PEXELS_KEY        : '';
            $pixabayKey = defined('PIXABAY_KEY')       && PIXABAY_KEY       ? PIXABAY_KEY       : '';
            $googleKey  = defined('GOOGLE_SEARCH_KEY') && GOOGLE_SEARCH_KEY ? GOOGLE_SEARCH_KEY : '';
            $googleCx   = defined('GOOGLE_SEARCH_CX')  && GOOGLE_SEARCH_CX  ? GOOGLE_SEARCH_CX  : '';

            $searchQ = $query . ' product photo white background';

            // ── SerpApi Google Images ──────────────────────────────
            if ($serpKey) {
                $apiUrl = 'https://serpapi.com/search.json?'
                        . 'engine=google_images'
                        . '&q='       . urlencode($searchQ)
                        . '&num=9'
                        . '&safe=active'
                        . '&api_key=' . urlencode($serpKey);
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_SSL_VERIFYPEER => false,
                ));
                $resp    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if (!$curlErr && $code === 200) {
                    $data = json_decode($resp, true);
                    $images = array();
                    if (!empty($data['images_results'])) {
                        foreach (array_slice($data['images_results'], 0, 9) as $item) {
                            $images[] = array(
                                'url'    => $item['original'] ?? $item['thumbnail'],
                                'thumb'  => $item['thumbnail'],
                                'title'  => $item['title'] ?? '',
                                'source' => $item['source'] ?? 'google.com',
                            );
                        }
                    }
                    if (!empty($images)) {
                        echo json_encode(array('success'=>true,'images'=>$images,'query'=>$searchQ,'count'=>count($images),'provider'=>'google'));
                        return;
                    }
                }
                if ($curlErr || $code !== 200) {
                    $err = json_decode($resp ?? '', true);
                    $msg = isset($err['error']) ? $err['error'] : 'HTTP '.$code;
                    echo json_encode(array('success'=>false,'message'=>'SerpApi: '.$msg));
                    return;
                }
            }

            // ── Bing Image Search ──────────────────────────────────
            if ($bingKey) {
                $apiUrl = 'https://api.bing.microsoft.com/v7.0/images/search?'
                        . 'q=' . urlencode($searchQ)
                        . '&count=9&imageType=Photo&safeSearch=Moderate&aspect=Square';
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER     => array('Ocp-Apim-Subscription-Key: '.$bingKey),
                ));
                $resp    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if ($curlErr) { echo json_encode(array('success'=>false,'message'=>'Lỗi kết nối Bing: '.$curlErr)); return; }
                if ($code !== 200) {
                    $err = json_decode($resp, true);
                    $msg = isset($err['error']['message']) ? $err['error']['message'] : 'HTTP '.$code;
                    echo json_encode(array('success'=>false,'message'=>'Bing API: '.$msg)); return;
                }
                $data = json_decode($resp, true);
                $images = array();
                if (!empty($data['value'])) {
                    foreach ($data['value'] as $item) {
                        $images[] = array(
                            'url'    => $item['contentUrl'],
                            'thumb'  => $item['thumbnailUrl'],
                            'title'  => $item['name'] ?? '',
                            'source' => isset($item['hostPageDisplayUrl']) ? parse_url($item['hostPageDisplayUrl'], PHP_URL_HOST) : '',
                        );
                    }
                }
                echo json_encode(array('success'=>true,'images'=>$images,'query'=>$searchQ,'count'=>count($images),'provider'=>'bing'));
                return;
            }

            // ── Pexels Image Search ────────────────────────────────
            if ($pexelsKey) {
                $apiUrl = 'https://api.pexels.com/v1/search?'
                        . 'query='    . urlencode($searchQ)
                        . '&per_page=9&orientation=square';
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER     => array('Authorization: '.$pexelsKey),
                ));
                $resp    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if (!$curlErr && $code === 200) {
                    $data = json_decode($resp, true);
                    $images = array();
                    if (!empty($data['photos'])) {
                        foreach ($data['photos'] as $item) {
                            $images[] = array(
                                'url'    => $item['src']['large'] ?? $item['src']['original'],
                                'thumb'  => $item['src']['medium'],
                                'title'  => $item['alt'] ?? '',
                                'source' => 'pexels.com',
                            );
                        }
                    }
                    if (!empty($images)) {
                        echo json_encode(array('success'=>true,'images'=>$images,'query'=>$searchQ,'count'=>count($images),'provider'=>'pexels'));
                        return;
                    }
                }
            }

            // ── Pixabay Image Search ───────────────────────────────
            if ($pixabayKey) {
                $apiUrl = 'https://pixabay.com/api/?'
                        . 'key='        . urlencode($pixabayKey)
                        . '&q='         . urlencode($query)
                        . '&image_type=photo&per_page=9&safesearch=true&order=popular';
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER     => array(
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept: application/json, text/plain, */*',
                        'Accept-Language: en-US,en;q=0.9',
                        'Referer: https://pixabay.com/',
                    ),
                ));
                $resp    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if ($curlErr) { echo json_encode(array('success'=>false,'message'=>'Lỗi kết nối Pixabay: '.$curlErr)); return; }
                if ($code !== 200) {
                    $detail = trim(strip_tags($resp));
                    $detail = mb_substr($detail, 0, 120);
                    echo json_encode(array('success'=>false,'message'=>'Pixabay API: HTTP '.$code.($detail?' — '.$detail:''))); return;
                }
                $data = json_decode($resp, true);
                $images = array();
                if (!empty($data['hits'])) {
                    foreach ($data['hits'] as $item) {
                        $images[] = array(
                            'url'    => $item['webformatURL'],
                            'thumb'  => $item['previewURL'],
                            'title'  => $item['tags'] ?? '',
                            'source' => 'pixabay.com',
                        );
                    }
                }
                echo json_encode(array('success'=>true,'images'=>$images,'query'=>$query,'count'=>count($images),'provider'=>'pixabay'));
                return;
            }

            // ── Google Custom Search ───────────────────────────────
            if ($googleKey && $googleCx) {
                $apiUrl = 'https://www.googleapis.com/customsearch/v1?'
                        . 'key=' . urlencode($googleKey)
                        . '&cx=' . urlencode($googleCx)
                        . '&q='  . urlencode($searchQ)
                        . '&searchType=image&num=9&imgType=photo&safe=active';
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER     => array('Accept: application/json'),
                ));
                $resp    = curl_exec($ch);
                $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);
                if ($curlErr) { echo json_encode(array('success'=>false,'message'=>'Lỗi kết nối Google: '.$curlErr)); return; }
                if ($code !== 200) {
                    $err = json_decode($resp, true);
                    $msg = isset($err['error']['message']) ? $err['error']['message'] : 'HTTP '.$code;
                    if (strpos($msg, 'does not have') !== false || strpos($msg, 'billing') !== false) {
                        $msg .= ' — Cần bật billing tại console.cloud.google.com/billing hoặc cấu hình BING_SEARCH_KEY để dùng Bing thay thế.';
                    }
                    echo json_encode(array('success'=>false,'message'=>'Google API: '.$msg)); return;
                }
                $data = json_decode($resp, true);
                $images = array();
                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $images[] = array(
                            'url'    => $item['link'],
                            'thumb'  => isset($item['image']['thumbnailLink']) ? $item['image']['thumbnailLink'] : $item['link'],
                            'title'  => $item['title'] ?? '',
                            'source' => $item['displayLink'] ?? '',
                        );
                    }
                }
                echo json_encode(array('success'=>true,'images'=>$images,'query'=>$searchQ,'count'=>count($images),'provider'=>'google'));
                return;
            }

            echo json_encode(array('success'=>false,'message'=>'Chưa cấu hình API tìm ảnh. Thêm PEXELS_KEY, BING_SEARCH_KEY hoặc GOOGLE_SEARCH_KEY + GOOGLE_SEARCH_CX vào config/app.php'));
            return;
        }

        // ── Tách nền ảnh → nền trắng ────────────────────────────
        if ($action === 'remove-bg') {
            $b        = json_decode(file_get_contents('php://input'), true) ?? array();
            $filename = basename($b['filename'] ?? '');
            $removeBgKey = defined('REMOVEBG_KEY') && REMOVEBG_KEY ? REMOVEBG_KEY : '';

            if (!$removeBgKey) {
                echo json_encode(array('success'=>false,'message'=>'Chưa cấu hình REMOVEBG_KEY. Đăng ký miễn phí tại remove.bg rồi thêm key vào config/app.php'));
                return;
            }
            if (!$filename || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
                echo json_encode(array('success'=>false,'message'=>'Tên file không hợp lệ')); return;
            }
            $srcPath = UPLOAD_PATH . $filename;
            if (!file_exists($srcPath)) {
                echo json_encode(array('success'=>false,'message'=>'Không tìm thấy ảnh')); return;
            }

            // Gọi remove.bg
            $ch = curl_init('https://api.remove.bg/v1.0/removebg');
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => array('X-Api-Key: '.$removeBgKey),
                CURLOPT_POSTFIELDS     => array(
                    'image_file' => new CURLFile($srcPath),
                    'size'       => 'auto',
                    'format'     => 'png',
                ),
            ));
            $resp    = curl_exec($ch);
            $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr) { echo json_encode(array('success'=>false,'message'=>'Lỗi kết nối: '.$curlErr)); return; }
            if ($code !== 200) {
                $err = json_decode($resp, true);
                $msg = isset($err['errors'][0]['title']) ? $err['errors'][0]['title'] : 'HTTP '.$code;
                echo json_encode(array('success'=>false,'message'=>'remove.bg: '.$msg)); return;
            }

            // Ghép nền trắng lên ảnh trong suốt
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            $newFname = 'nobg_'.uniqid().'.jpg';
            $newPath  = UPLOAD_PATH.$newFname;

            if (function_exists('imagecreatefrompng')) {
                $tmp = tempnam(sys_get_temp_dir(), 'rbg_');
                file_put_contents($tmp, $resp);
                $fg = imagecreatefrompng($tmp);
                @unlink($tmp);
                if ($fg) {
                    $w  = imagesx($fg); $h = imagesy($fg);
                    $bg = imagecreatetruecolor($w, $h);
                    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                    imagealphablending($bg, true);
                    imagecopy($bg, $fg, 0, 0, 0, 0, $w, $h);
                    imagedestroy($fg);
                    imagejpeg($bg, $newPath, 92);
                    imagedestroy($bg);
                } else {
                    // GD không đọc được → lưu PNG thô
                    $newFname = 'nobg_'.uniqid().'.png';
                    $newPath  = UPLOAD_PATH.$newFname;
                    file_put_contents($newPath, $resp);
                }
            } else {
                $newFname = 'nobg_'.uniqid().'.png';
                $newPath  = UPLOAD_PATH.$newFname;
                file_put_contents($newPath, $resp);
            }

            echo json_encode(array('success'=>true,'filename'=>$newFname,'url'=>UPLOAD_URL.$newFname));
            return;
        }

        // ── Gắn logo watermark (góc trên-trái, giống logo trang chủ) ──
        if ($action === 'add-watermark') {
            $b        = json_decode(file_get_contents('php://input'), true) ?? array();
            $filename = basename($b['filename'] ?? '');

            if (!$filename || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
                echo json_encode(array('success'=>false,'message'=>'Tên file không hợp lệ')); return;
            }
            $srcPath = UPLOAD_PATH . $filename;
            if (!file_exists($srcPath)) {
                echo json_encode(array('success'=>false,'message'=>'Không tìm thấy ảnh')); return;
            }
            if (!function_exists('imagecreatefromjpeg')) {
                echo json_encode(array('success'=>false,'message'=>'Server chưa bật GD extension')); return;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($ext === 'jpg' || $ext === 'jpeg')   $img = imagecreatefromjpeg($srcPath);
            elseif ($ext === 'png')                   $img = imagecreatefrompng($srcPath);
            elseif (function_exists('imagecreatefromwebp') && $ext === 'webp')
                                                      $img = imagecreatefromwebp($srcPath);
            else { echo json_encode(array('success'=>false,'message'=>'Định dạng không hỗ trợ: '.$ext)); return; }
            if (!$img) { echo json_encode(array('success'=>false,'message'=>'Không đọc được ảnh')); return; }

            $iw = imagesx($img);
            $ih = imagesy($img);

            // ── Kích thước badge: ~12% chiều rộng ảnh, min 80px, max 160px ──
            $iconSz = (int)max(28, min(54, $iw * 0.055)); // ô vuông icon
            $gap    = (int)($iconSz * 0.22);
            $pad    = (int)($iconSz * 0.18);
            $r      = (int)($iconSz * 0.21); // bo góc icon & container

            // ── Font TTF ──
            $fontBold = null; $fontReg = null;
            foreach (array('C:/Windows/Fonts/arialbd.ttf','C:/Windows/Fonts/verdanab.ttf','C:/Windows/Fonts/calibrib.ttf') as $f)
                { if (file_exists($f)) { $fontBold = $f; break; } }
            foreach (array('C:/Windows/Fonts/arial.ttf','C:/Windows/Fonts/verdana.ttf','C:/Windows/Fonts/calibri.ttf') as $f)
                { if (file_exists($f)) { $fontReg = $f; break; } }
            if (!$fontReg) $fontReg = $fontBold;

            // Đo kích thước chữ
            $szName = max(7, (int)($iconSz * 0.38)); // "TUẤN HUY"
            $szSub  = max(5, (int)($iconSz * 0.26)); // "COMPUTER"
            $txtName = 'TUAN HUY';
            $txtSub  = 'COMPUTER';

            $tnW = 0; $tnH = 0; $tsW = 0; $tsH = 0;
            if ($fontBold && function_exists('imagettfbbox')) {
                $bb1 = imagettfbbox($szName, 0, $fontBold, $txtName);
                $tnW = abs($bb1[2]-$bb1[0]); $tnH = abs($bb1[7]-$bb1[1]);
                $bb2 = imagettfbbox($szSub, 0, $fontReg, $txtSub);
                $tsW = abs($bb2[2]-$bb2[0]); $tsH = abs($bb2[7]-$bb2[1]);
            } else {
                $tnW = imagefontwidth(3)*strlen($txtName); $tnH = imagefontheight(3);
                $tsW = imagefontwidth(2)*strlen($txtSub);  $tsH = imagefontheight(2);
            }
            $textW = max($tnW, $tsW);

            // ── Kích thước outer badge (background mờ) ──
            $bw = $pad + $iconSz + $gap + $textW + $pad;
            $bh = $pad + $iconSz + $pad;

            // Tạo badge canvas
            $badge = imagecreatetruecolor($bw, $bh);
            imagealphablending($badge, false);
            imagesavealpha($badge, true);

            // Nền tối bán trong suốt (giống dark2 = #1a1a1a, 72%)
            $dark = imagecolorallocatealpha($badge, 18, 18, 18, 35); // alpha 35/127 ≈ 72% opacity
            imagefilledrectangle($badge, 0, 0, $bw-1, $bh-1, $dark);

            // Bo góc ngoài
            $ro = (int)($bh * 0.28);
            $clearC = imagecolorallocatealpha($badge, 0, 0, 0, 127);
            // 4 góc bo
            imagefilledrectangle($badge, 0, 0, $ro-1, $ro-1, $clearC);
            imagefilledarc($badge, $ro, $ro, $ro*2, $ro*2, 180, 270, $dark, IMG_ARC_PIE);
            imagefilledrectangle($badge, $bw-$ro, 0, $bw-1, $ro-1, $clearC);
            imagefilledarc($badge, $bw-1-$ro, $ro, $ro*2, $ro*2, 270, 360, $dark, IMG_ARC_PIE);
            imagefilledrectangle($badge, 0, $bh-$ro, $ro-1, $bh-1, $clearC);
            imagefilledarc($badge, $ro, $bh-1-$ro, $ro*2, $ro*2, 90, 180, $dark, IMG_ARC_PIE);
            imagefilledrectangle($badge, $bw-$ro, $bh-$ro, $bw-1, $bh-1, $clearC);
            imagefilledarc($badge, $bw-1-$ro, $bh-1-$ro, $ro*2, $ro*2, 0, 90, $dark, IMG_ARC_PIE);

            imagealphablending($badge, true);
            $red   = imagecolorallocate($badge, 227, 0, 0);
            $white = imagecolorallocate($badge, 255, 255, 255);
            $redSub = imagecolorallocate($badge, 255, 80, 80);

            // ── Ô vuông đỏ bo góc (icon) ──
            $ix = $pad; $iy = $pad;
            imagefilledrectangle($badge, $ix+$r, $iy,         $ix+$iconSz-1-$r, $iy+$iconSz-1, $red);
            imagefilledrectangle($badge, $ix,    $iy+$r,      $ix+$iconSz-1,    $iy+$iconSz-1-$r, $red);
            imagefilledarc($badge, $ix+$r,            $iy+$r,            $r*2,$r*2, 180,270, $red, IMG_ARC_PIE);
            imagefilledarc($badge, $ix+$iconSz-1-$r,  $iy+$r,            $r*2,$r*2, 270,360, $red, IMG_ARC_PIE);
            imagefilledarc($badge, $ix+$r,            $iy+$iconSz-1-$r,  $r*2,$r*2,  90,180, $red, IMG_ARC_PIE);
            imagefilledarc($badge, $ix+$iconSz-1-$r,  $iy+$iconSz-1-$r,  $r*2,$r*2,   0, 90, $red, IMG_ARC_PIE);

            // Chữ "TH" trong ô đỏ
            $szTH = max(8, (int)($iconSz * 0.42));
            if ($fontBold && function_exists('imagettftext')) {
                $bbTH = imagettfbbox($szTH, 0, $fontBold, 'TH');
                $thW = abs($bbTH[2]-$bbTH[0]); $thH = abs($bbTH[7]-$bbTH[1]);
                imagettftext($badge, $szTH, 0, $ix+(int)(($iconSz-$thW)/2), $iy+(int)(($iconSz+$thH)/2)-1, $white, $fontBold, 'TH');
            } else {
                $f = 4;
                imagestring($badge, $f, $ix+(int)(($iconSz-imagefontwidth($f)*2)/2), $iy+(int)(($iconSz-imagefontheight($f))/2), 'TH', $white);
            }

            // ── Text bên phải: "TUAN HUY" + "COMPUTER" ──
            $tx = $pad + $iconSz + $gap;
            $ty1 = $iy + (int)(($iconSz - $tnH - $tsH - (int)($iconSz*0.08)) / 2);

            if ($fontBold && function_exists('imagettftext')) {
                imagettftext($badge, $szName, 0, $tx, $ty1 + $tnH, $white, $fontBold, $txtName);
                imagettftext($badge, $szSub,  0, $tx, $ty1 + $tnH + (int)($iconSz*0.08) + $tsH, $redSub, $fontReg, $txtSub);
            } else {
                imagestring($badge, 3, $tx, $ty1,           $txtName, $white);
                imagestring($badge, 2, $tx, $ty1+$tnH+2,    $txtSub,  $redSub);
            }

            // ── Merge lên ảnh gốc (góc trên-trái, padding = 1.5% chiều rộng) ──
            $marginX = (int)max(6, $iw * 0.015);
            $marginY = (int)max(6, $ih * 0.015);
            imagealphablending($img, true);
            // dùng imagecopy thay imagecopymerge để giữ alpha của badge
            imagecopy($img, $badge, $marginX, $marginY, 0, 0, $bw, $bh);
            imagedestroy($badge);

            // ── Lưu ──
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            $outExt   = ($ext === 'png') ? 'png' : 'jpg';
            $newFname = 'wm_'.uniqid().'.'.$outExt;
            $newPath  = UPLOAD_PATH.$newFname;
            if ($ext === 'png') { imagesavealpha($img, true); imagepng($img, $newPath); }
            else                imagejpeg($img, $newPath, 92);
            imagedestroy($img);

            echo json_encode(array('success'=>true,'filename'=>$newFname,'url'=>UPLOAD_URL.$newFname));
            return;
        }

        // ── Cập nhật filename ảnh phụ sau khi xử lý ────────────
        if ($action === 'update-extra-image') {
            $b       = json_decode(file_get_contents('php://input'), true) ?? array();
            $imgId   = (int)($b['img_id'] ?? 0);
            $newFile = basename($b['filename'] ?? '');
            if (!$imgId || !$newFile || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $newFile)) {
                echo json_encode(array('success'=>false,'message'=>'Thiếu dữ liệu')); return;
            }
            if (!file_exists(UPLOAD_PATH.$newFile)) {
                echo json_encode(array('success'=>false,'message'=>'File không tồn tại')); return;
            }
            $db = Database::getInstance();
            $db->query("UPDATE product_images SET image=? WHERE id=?", array($newFile, $imgId));
            echo json_encode(array('success'=>true,'filename'=>$newFile,'url'=>UPLOAD_URL.$newFile));
            return;
        }

        // Sắp xếp lại thứ tự ảnh phụ
        if ($action === 'reorder-images') {
            $b   = json_decode(file_get_contents('php://input'), true) ?? array();
            $pid = (int)($b['product_id'] ?? 0);
            $ids = isset($b['ids']) && is_array($b['ids']) ? $b['ids'] : array();
            if (!$pid || empty($ids)) { echo json_encode(array('success'=>false,'message'=>'Thiếu dữ liệu')); return; }
            $db = Database::getInstance();
            foreach ($ids as $order => $imgId) {
                $db->query("UPDATE product_images SET sort_order=? WHERE id=? AND product_id=?",
                    array((int)$order, (int)$imgId, $pid));
            }
            echo json_encode(array('success'=>true));
            return;
        }

        // Upload ảnh phụ
        if ($action === 'upload-extra-images') {
            $pid = (int)($_POST['product_id'] ?? 0);
            if (!$pid || empty($_FILES['extra_images'])) { echo json_encode(array('success'=>false,'message'=>'Thiếu dữ liệu')); return; }
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            $pm    = new ProductModel();
            $files = $_FILES['extra_images'];
            $count = is_array($files['name']) ? count($files['name']) : 0;
            $saved = 0;
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, array('jpg','jpeg','png','webp','gif'))) continue;
                if ($files['size'][$i] > 5 * 1024 * 1024) continue;
                $fname = 'prod_extra_'.uniqid().'.'.$ext;
                if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_PATH.$fname)) {
                    $pm->addImage($pid, $fname, $i);
                    $saved++;
                }
            }
            echo json_encode(array('success'=>true,'saved'=>$saved)); return;
        }

        echo json_encode(array('success'=>false,'message'=>'Unknown action'));
    }

    // ── AI: nhận diện từ ảnh (Groq) ─────────────────────────────
    private function aiFromImage($imgB64, $imgMime, $imgUrl, $apiKey, $cats, $brands) {
        $catList   = '';
        $brandList = '';
        foreach ($cats   as $c)  $catList   .= "id={$c['id']} name={$c['name']} slug={$c['slug']}\n";
        foreach ($brands as $br) $brandList .= "id={$br['id']} name={$br['name']}\n";

        $prompt = 'Bạn là chuyên gia nhận diện sản phẩm công nghệ máy tính. Phân tích hình ảnh và trả về DUY NHẤT một JSON object (không markdown, không ```json, không giải thích).

DANH MỤC HỆ THỐNG:
'.$catList.'
THƯƠNG HIỆU HỆ THỐNG:
'.$brandList.'
JSON bắt buộc:
{"name":"Tên đầy đủ hãng+model","brand":"Tên hãng","brand_id":null,"category_id":0,"category_slug":"","short_desc":"60-80 từ tiếng Việt","description":"130-180 từ tiếng Việt","price":0,"sale_price":0,"stock":10,"sku":"BRAND-MODEL","warranty":24,"specs":{"Thông số":"Giá trị"}}

GIÁ VND (bắt buộc trong khoảng hợp lý, KHÔNG được vượt quá giới hạn):
- Chuột: 150.000 – 5.000.000
- Bàn phím: 200.000 – 8.000.000
- Tai nghe: 200.000 – 6.000.000
- RAM 8GB: 300.000–600.000 | RAM 16GB: 600.000–1.200.000 | RAM 32GB: 1.200.000–2.500.000
- SSD 256GB: 400.000–700.000 | SSD 500GB: 600.000–1.200.000 | SSD 1TB: 1.000.000–2.500.000
- CPU Intel i3: 2.000.000–4.000.000 | i5: 3.500.000–7.000.000 | i7: 7.000.000–12.000.000 | i9: 12.000.000–25.000.000
- CPU AMD Ryzen 5: 3.000.000–6.000.000 | Ryzen 7: 6.000.000–11.000.000 | Ryzen 9: 10.000.000–22.000.000
- GPU RTX 3060: 7.000.000–12.000.000 | RTX 4060: 9.000.000–14.000.000 | RTX 4070: 13.000.000–20.000.000 | RTX 4090: 35.000.000–55.000.000
- Màn hình 24": 2.500.000–8.000.000 | 27": 4.000.000–15.000.000
- Laptop phổ thông: 8.000.000–18.000.000 | Laptop tầm trung: 18.000.000–30.000.000 | Laptop cao cấp: 30.000.000–70.000.000
- Mainboard: 2.000.000–10.000.000 | Case: 500.000–4.000.000 | PSU: 600.000–4.000.000
- price là số nguyên VND (ví dụ: 3500000). sale_price = 0 nếu không khuyến mãi.
- Giới hạn tối đa tuyệt đối: price <= 100.000.000
category_id và brand_id lấy từ danh sách trên. Specs 5-8 thông số. Key tiếng Việt.';

        $content = array();
        if ($imgB64) {
            $raw = preg_replace('/^data:[^;]+;base64,/i', '', $imgB64);
            $raw = preg_replace('/\s+/', '', $raw);
            $content[] = array('type'=>'image_url','image_url'=>array('url'=>'data:'.$imgMime.';base64,'.$raw));
        } elseif ($imgUrl) {
            $content[] = array('type'=>'image_url','image_url'=>array('url'=>$imgUrl));
        }
        $content[] = array('type'=>'text','text'=>$prompt);

        return $this->callGroq(array(array('role'=>'user','content'=>$content)), $apiKey);
    }

    // ── AI: sinh thông tin từ tên (Groq) ─────────────────────
    private function aiFromText($productName, $apiKey, $cats, $brands) {
        $catList   = '';
        $brandList = '';
        foreach ($cats   as $c)  $catList   .= "id={$c['id']} name={$c['name']} slug={$c['slug']}\n";
        foreach ($brands as $br) $brandList .= "id={$br['id']} name={$br['name']}\n";

        $prompt = 'Bạn là chuyên gia sản phẩm công nghệ máy tính Việt Nam. Dựa tên sản phẩm, điền thông tin đầy đủ.

Tên sản phẩm: '.$productName.'

DANH MỤC:
'.$catList.'
THƯƠNG HIỆU:
'.$brandList.'
Trả về DUY NHẤT JSON (không markdown):
{"name":"tên đầy đủ","brand":"hãng","brand_id":null,"category_id":0,"category_slug":"","short_desc":"60-80 từ tiếng Việt","description":"130-180 từ tiếng Việt","price":0,"sale_price":0,"stock":10,"sku":"BRAND-MODEL","warranty":24,"specs":{"Thông số":"Giá trị"}}

GIÁ VND (bắt buộc trong khoảng hợp lý, KHÔNG được vượt quá giới hạn):
- Chuột: 150.000–5.000.000 | Bàn phím: 200.000–8.000.000 | Tai nghe: 200.000–6.000.000
- RAM 8GB: 300.000–600.000 | 16GB: 600.000–1.200.000 | 32GB: 1.200.000–2.500.000
- SSD 256GB: 400.000–700.000 | 500GB: 600.000–1.200.000 | 1TB: 1.000.000–2.500.000
- CPU i3: 2tr–4tr | i5: 3.5tr–7tr | i7: 7tr–12tr | i9: 12tr–25tr | Ryzen 5: 3tr–6tr | Ryzen 7: 6tr–11tr
- GPU RTX 3060: 7tr–12tr | RTX 4060: 9tr–14tr | RTX 4070: 13tr–20tr | RTX 4090: 35tr–55tr
- Laptop phổ thông: 8tr–18tr | tầm trung: 18tr–30tr | cao cấp: 30tr–70tr
- Màn hình 24": 2.5tr–8tr | 27": 4tr–15tr | Mainboard: 2tr–10tr
- price là số nguyên VND. sale_price=0 nếu không KM. Giới hạn tối đa: 100.000.000
category_id brand_id từ danh sách trên. Specs 5-8 thông số tiếng Việt.';

        return $this->callGroq(array(array('role'=>'user','content'=>$prompt)), $apiKey);
    }

    // ── Gọi Groq AI ───────────────────────────────────────────
    private function callGroq($messages, $apiKey) {
        // Vision dùng llama-3.2-11b-vision-preview, text dùng llama-3.3-70b-versatile làm fallback
        $isVision = false;
        foreach ($messages as $msg) {
            if (is_array($msg['content'])) { $isVision = true; break; }
        }
        $models = $isVision
            ? array('meta-llama/llama-4-scout-17b-16e-instruct', 'meta-llama/llama-4-maverick-17b-128e-instruct', 'llama-3.2-11b-vision-preview')
            : array('llama-3.3-70b-versatile', 'meta-llama/llama-4-scout-17b-16e-instruct', 'mixtral-8x7b-32768');

        $lastErr = '';
        foreach ($models as $model) {
            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 90,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$apiKey,
                ),
                CURLOPT_POSTFIELDS => json_encode(array(
                    'model'       => $model,
                    'max_tokens'  => 4096,
                    'temperature' => 0,
                    'messages'    => $messages,
                ), JSON_UNESCAPED_UNICODE),
            ));
            $resp    = curl_exec($ch);
            $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr) return array('success'=>false,'message'=>'Lỗi kết nối: '.$curlErr);

            $data = json_decode($resp, true);
            if ($code === 200 && isset($data['choices'][0]['message']['content'])) {
                return $this->parseAiJson($data['choices'][0]['message']['content']);
            }

            $errMsg = $data['error']['message'] ?? ('HTTP '.$code.' raw:'.mb_substr($resp,0,200));
            $lastErr = '['.$model.'] '.$errMsg;
            // Lỗi fatal: sai key, hết quota
            if ($code === 401 || $code === 403 || strpos($errMsg, 'Invalid API') !== false) {
                return array('success'=>false,'message'=>'Groq lỗi: '.$errMsg);
            }
            // Rate limit hoặc model không dùng được → thử model tiếp
        }

        return array('success'=>false,'message'=>'Groq lỗi: '.($lastErr ?? 'Không có phản hồi'));
    }

    // ── Parse JSON từ response AI ─────────────────────────────
    private function parseAiJson($text) {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        $parsed = json_decode($text, true);
        if (!$parsed) {
            $s = strpos($text, '{'); $e = strrpos($text, '}');
            if ($s !== false && $e > $s) $parsed = json_decode(substr($text, $s, $e - $s + 1), true);
        }
        if (!$parsed) {
            $open = substr_count($text, '{'); $close = substr_count($text, '}');
            if ($open > $close) $parsed = json_decode($text.str_repeat('}', $open - $close), true);
        }
        if (!$parsed) return array('success'=>false,'message'=>'Không parse được JSON. Raw: '.mb_substr($text,0,300));

        return array('success'=>true,'data'=>$parsed);
    }

    // ── TELEGRAM BOT POLL ─────────────────────────────────────
    public function telegram($action = null) {
        // Cron endpoint: gọi từ cron-job.org mỗi phút (không cần login, bảo vệ bằng secret)
        if ($action === 'cron') {
            $secret = defined('TELEGRAM_CRON_SECRET') ? TELEGRAM_CRON_SECRET : getenv('TELEGRAM_CRON_SECRET');
            $token  = $_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? '';
            if (!$secret || $token !== $secret) {
                http_response_code(403);
                echo json_encode(array('ok'=>false,'message'=>'Forbidden'));
                return;
            }
            require_once __DIR__ . '/../Helpers/TelegramBot.php';
            $result = TelegramBot::poll();
            echo json_encode($result);
            return;
        }

        if (!isAdmin()) { echo json_encode(array('ok'=>false,'message'=>'Unauthorized')); return; }
        if ($action === 'poll') {
            require_once __DIR__ . '/../Helpers/TelegramBot.php';
            require_once __DIR__ . '/../../config/database.php';
            $result = TelegramBot::poll();
            echo json_encode($result);
            return;
        }
        echo json_encode(array('ok'=>false,'message'=>'Unknown action'));
    }

    // ── SKU duy nhất ──────────────────────────────────────────
    private function makeUniqueSku($db, $baseSku = '') {
        if (!$baseSku) $baseSku = 'AI-'.strtoupper(substr(uniqid(), 0, 6));
        $baseSku = substr($baseSku, 0, 50);
        $sku = $baseSku;
        $i   = 1;
        while (true) {
            if (!$db->fetch("SELECT id FROM products WHERE sku=?", array($sku))) break;
            $sku = $baseSku.'-'.$i++;
            if ($i > 999) { $sku = 'AI-'.strtoupper(uniqid()); break; }
        }
        return $sku;
    }
}
