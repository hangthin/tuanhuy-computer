<?php
require_once __DIR__.'/../Models/Models.php';
require_once __DIR__.'/../Models/ProductModel.php';

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $productModel;
    public function __construct(){ $this->cartModel=new CartModel();$this->orderModel=new OrderModel();$this->productModel=new ProductModel(); }

    private function calcSubtotal($items){ $t=0;foreach($items as $i)$t+=(float)$i['unit_price']*(int)$i['quantity'];return $t; }

    /** Validate coupon from session against current subtotal. Returns [code, discount] or ['', 0]. */
    private function resolveSessionCoupon($subtotal) {
        $sc = $_SESSION['applied_coupon'] ?? null;
        if (empty($sc['code'])) return array('', 0);
        $db   = Database::getInstance();
        $code = strtoupper($sc['code']);
        $c    = $db->fetch("SELECT * FROM coupons WHERE code=? AND is_active=1", array($code));
        if (!$c)                                                       { unset($_SESSION['applied_coupon']); return array('', 0); }
        if ($c['expires_at'] && strtotime($c['expires_at']) < time()) { unset($_SESSION['applied_coupon']); return array('', 0); }
        if ((int)$c['used_count'] >= (int)$c['usage_limit'])          { unset($_SESSION['applied_coupon']); return array('', 0); }
        if ($subtotal < (float)$c['min_order'])                        { return array('', 0); } // keep session but don't apply
        $disc = $c['type'] === 'percent' ? $subtotal * (float)$c['value'] / 100 : (float)$c['value'];
        if ($c['max_discount']) $disc = min($disc, (float)$c['max_discount']);
        $disc = min($disc, $subtotal);
        // Update stored discount in case subtotal changed
        $_SESSION['applied_coupon']['discount'] = $disc;
        $_SESSION['applied_coupon']['message']  = 'Giảm '.formatPrice($disc);
        return array($code, $disc);
    }

    public function index($p=null){
        $items=$this->cartModel->getItems();
        if(empty($items)){header('Location:'.APP_URL.'/cart');exit;}
        $subtotal=$this->calcSubtotal($items);
        $shipping=$subtotal>=500000?0:30000;
        list($couponCode,$couponDiscount)=$this->resolveSessionCoupon($subtotal);
        $total=max(0,$subtotal+$shipping-$couponDiscount);
        $appliedCoupon=$_SESSION['applied_coupon']??null;
        $user=isLoggedIn()?(new UserModel())->findById($_SESSION['user_id']):null;
        $categories=(new CategoryModel())->getAll();
        $checkoutMode='cart';
        $pageTitle='Thanh toán';
        include __DIR__.'/../Views/cart/checkout.php';
    }

    public function place($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/checkout');exit;}
        $mode = ($_POST['checkout_mode'] ?? 'cart') === 'buynow' ? 'buynow' : 'cart';
        if($mode === 'buynow'){
            $items = !empty($_SESSION['buynow_items']) ? $_SESSION['buynow_items'] : array();
        } else {
            $items = $this->cartModel->getItems();
        }
        if(empty($items)){header('Location:'.APP_URL.'/cart');exit;}

        // ── Server-side validation ─────────────────────────────
        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $address  = trim($_POST['address']  ?? '');
        $city     = trim($_POST['city']     ?? '');
        $payment  = trim($_POST['payment_method'] ?? 'cod');
        $validPayments = array('cod','bank','momo','vnpay');
        $errs = array();
        if(!$fullname)                                                   $errs[]='Vui lòng nhập họ tên.';
        if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))        $errs[]='Email không hợp lệ.';
        if(!preg_match('/^(0[3-9]\d{8}|02\d{9})$/', $phone))            $errs[]='Số điện thoại không hợp lệ (VD: 0901234567).';
        if(!$address)                                                     $errs[]='Vui lòng nhập địa chỉ.';
        if(!$city)                                                        $errs[]='Vui lòng chọn tỉnh/thành phố.';
        if(!in_array($payment, $validPayments))                           $payment='cod';
        if($errs){ setFlash('error', implode(' ', $errs)); header('Location:'.APP_URL.'/checkout'); exit; }

        $subtotal=$this->calcSubtotal($items);
        $shipping=$subtotal>=500000?0:30000;

        // ── Coupon: server-side re-validate (không tin client) ──
        // Chỉ áp dụng coupon cho cart checkout, không áp cho buynow
        $couponCode     = '';
        $couponDiscount = 0;
        if ($mode === 'cart') {
            list($couponCode, $couponDiscount) = $this->resolveSessionCoupon($subtotal);
        }

        $total=max(0,$subtotal+$shipping-$couponDiscount);

        $orderId=$this->orderModel->create(array(
            'user_id'        => isLoggedIn()?$_SESSION['user_id']:null,
            'fullname'       => sanitize($fullname),
            'email'          => sanitize($email),
            'phone'          => sanitize($phone),
            'address'        => sanitize($address),
            'city'           => sanitize($city),
            'district'       => sanitize($_POST['district'] ?? ''),
            'subtotal'       => $subtotal,
            'shipping_fee'   => $shipping,
            'discount'       => $couponDiscount,
            'coupon_code'    => $couponCode ?: null,
            'total'          => $total,
            'payment_method' => $payment,
            'notes'          => sanitize($_POST['notes'] ?? ''),
        ));
        foreach($items as $item){ $this->orderModel->addItem($orderId,array('product_id'=>$item['product_id'],'name'=>$item['name'],'price'=>$item['unit_price'],'quantity'=>$item['quantity'],'subtotal'=>$item['unit_price']*$item['quantity'])); }
        if($payment !== 'cod'){
            Database::getInstance()->query("UPDATE orders SET status='pending_payment' WHERE id=?",array($orderId));
        }

        // Increment coupon usage
        if($couponCode){
            Database::getInstance()->query("UPDATE coupons SET used_count=used_count+1 WHERE code=?",array($couponCode));
        }

        if($mode === 'buynow'){
            unset($_SESSION['buynow_items']);
        } else {
            $this->cartModel->clear();
            unset($_SESSION['applied_coupon']); // clear coupon after cart order
        }
        $_SESSION['new_order_id'] = $orderId;
        header('Location:'.APP_URL.'/checkout/success/'.$orderId);exit;
    }

    public function success($orderId){
        $order=$this->orderModel->getWithItems((int)$orderId);
        if(!$order){header('Location:'.APP_URL.'/');exit;}
        $categories=(new CategoryModel())->getAll();
        $pageTitle='Đặt hàng thành công';
        ob_start();
        include __DIR__.'/../Views/cart/success.php';
        $html = ob_get_clean();
        header('Content-Length: '.strlen($html));
        header('Connection: close');
        echo $html;
        if(function_exists('fastcgi_finish_request')){fastcgi_finish_request();}else{ob_flush();flush();}
        if(isset($_SESSION['new_order_id']) && (int)$_SESSION['new_order_id']===(int)$orderId){
            unset($_SESSION['new_order_id']);
            session_write_close();
            ignore_user_abort(true);
            try{
                $f=__DIR__.'/../Helpers/MailService.php';
                if(file_exists($f)){require_once $f;}
                if(class_exists('MailService')){@MailService::sendOrderConfirmation($order);}
            }catch(Exception $e){}
        }
    }

    public function buynow($productId){
        $product=$this->productModel->getById((int)$productId);
        if(!$product){header('Location:'.APP_URL.'/products');exit;}
        $qty=max(1,(int)(isset($_GET['qty'])?$_GET['qty']:1));
        $items=array(array('product_id'=>$product['id'],'name'=>$product['name'],'unit_price'=>$product['final_price'],'quantity'=>$qty,'slug'=>$product['slug'],'image'=>$product['image'],'stock'=>$product['stock']));
        $_SESSION['buynow_items']=$items;
        $subtotal=$product['final_price']*$qty;$shipping=$subtotal>=500000?0:30000;
        $couponCode='';$couponDiscount=0;$appliedCoupon=null;
        $total=$subtotal+$shipping;
        $user=isLoggedIn()?(new UserModel())->findById($_SESSION['user_id']):null;
        $categories=(new CategoryModel())->getAll();
        $checkoutMode='buynow';
        $pageTitle='Mua ngay';
        include __DIR__.'/../Views/cart/checkout.php';
    }
}
