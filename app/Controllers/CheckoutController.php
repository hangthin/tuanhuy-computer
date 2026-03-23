<?php
require_once __DIR__.'/../Models/Models.php';
require_once __DIR__.'/../Models/ProductModel.php';

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $productModel;
    public function __construct(){ $this->cartModel=new CartModel();$this->orderModel=new OrderModel();$this->productModel=new ProductModel(); }

    private function calcSubtotal($items){ $t=0;foreach($items as $i)$t+=(float)$i['unit_price']*(int)$i['quantity'];return $t; }

    public function index($p=null){
        $items=$this->cartModel->getItems();
        if(empty($items)){header('Location:'.APP_URL.'/cart');exit;}
        $subtotal=$this->calcSubtotal($items);$shipping=$subtotal>=500000?0:30000;$total=$subtotal+$shipping;
        $user=isLoggedIn()?(new UserModel())->findById($_SESSION['user_id']):null;
        $categories=(new CategoryModel())->getAll();
        $pageTitle='Thanh toán';
        include __DIR__.'/../Views/cart/checkout.php';
    }

    public function place($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/checkout');exit;}
        $items=$this->cartModel->getItems();
        // Fallback: buynow không lưu DB cart, lấy từ session
        if(empty($items) && !empty($_SESSION['buynow_items'])){
            $items=$_SESSION['buynow_items'];
        }
        if(empty($items)){header('Location:'.APP_URL.'/cart');exit;}
        $subtotal=$this->calcSubtotal($items);$shipping=$subtotal>=500000?0:30000;$total=$subtotal+$shipping;
        $orderId=$this->orderModel->create(array(
            'user_id'        => isLoggedIn()?$_SESSION['user_id']:null,
            'fullname'       => sanitize($_POST['fullname']??''),
            'email'          => sanitize($_POST['email']??''),
            'phone'          => sanitize($_POST['phone']??''),
            'address'        => sanitize($_POST['address']??''),
            'city'           => sanitize($_POST['city']??''),
            'district'       => sanitize($_POST['district']??''),
            'subtotal'       => $subtotal,'shipping_fee'=>$shipping,'total'=>$total,
            'payment_method' => sanitize($_POST['payment_method']??'cod'),
            'notes'          => sanitize($_POST['notes']??''),
        ));
        foreach($items as $item){ $this->orderModel->addItem($orderId,array('product_id'=>$item['product_id'],'name'=>$item['name'],'price'=>$item['unit_price'],'quantity'=>$item['quantity'],'subtotal'=>$item['unit_price']*$item['quantity'])); }
        $this->cartModel->clear();
        unset($_SESSION['buynow_items']);
        $_SESSION['new_order_id'] = $orderId; // dùng để gửi email ở success page
        header('Location:'.APP_URL.'/checkout/success/'.$orderId);exit;
    }

    public function success($orderId){
        $order=$this->orderModel->getWithItems((int)$orderId);
        if(!$order){header('Location:'.APP_URL.'/');exit;}
        $categories=(new CategoryModel())->getAll();
        $pageTitle='Đặt hàng thành công';
        // Render trang trước, gửi email sau (không block user)
        ob_start();
        include __DIR__.'/../Views/cart/success.php';
        $html = ob_get_clean();
        header('Content-Length: '.strlen($html));
        header('Connection: close');
        echo $html;
        if(function_exists('fastcgi_finish_request')){fastcgi_finish_request();}else{ob_flush();flush();}
        // Gửi email sau khi đã trả response về trình duyệt
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
        $_SESSION['buynow_items']=$items; // lưu để place() dùng
        $subtotal=$product['final_price']*$qty;$shipping=$subtotal>=500000?0:30000;$total=$subtotal+$shipping;
        $user=isLoggedIn()?(new UserModel())->findById($_SESSION['user_id']):null;
        $categories=(new CategoryModel())->getAll();
        $pageTitle='Mua ngay';
        include __DIR__.'/../Views/cart/checkout.php';
    }
}
