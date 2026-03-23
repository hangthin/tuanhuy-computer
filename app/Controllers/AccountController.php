<?php
require_once __DIR__.'/../Models/Models.php';
class AccountController {
    public function __construct(){ requireLogin(); }
    public function index($p=null){
        $user=(new UserModel())->findById($_SESSION['user_id']);
        $categories=(new CategoryModel())->getAll();$pageTitle='Tài khoản';
        include __DIR__.'/../Views/account/profile.php';
    }
    public function orders($p=null){
        $orders=(new OrderModel())->getUserOrders($_SESSION['user_id']);
        $categories=(new CategoryModel())->getAll();$pageTitle='Đơn hàng';
        include __DIR__.'/../Views/account/orders.php';
    }
    public function update($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/account');exit;}
        (new UserModel())->update($_SESSION['user_id'],array('fullname'=>sanitize($_POST['fullname']??''),'phone'=>sanitize($_POST['phone']??''),'city'=>sanitize($_POST['city']??''),'district'=>sanitize($_POST['district']??''),'address'=>sanitize($_POST['address']??'')));
        $_SESSION['user_name']=sanitize($_POST['fullname']??'');
        setFlash('success','Cập nhật thành công!');header('Location:'.APP_URL.'/account');exit;
    }
    public function changePassword($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/account');exit;}
        $cur=trim($_POST['current_password']??'');
        $new=trim($_POST['new_password']??'');
        $cf =trim($_POST['confirm_password']??'');
        if(!$cur||!$new){setFlash('error','Vui lòng điền đầy đủ');header('Location:'.APP_URL.'/account');exit;}
        if(strlen($new)<6){setFlash('error','Mật khẩu mới ít nhất 6 ký tự');header('Location:'.APP_URL.'/account');exit;}
        if($new!==$cf){setFlash('error','Mật khẩu nhập lại không khớp');header('Location:'.APP_URL.'/account');exit;}
        $db=Database::getInstance();
        $user=$db->fetch("SELECT password FROM users WHERE id=?",array($_SESSION['user_id']));
        if(!$user||!password_verify($cur,$user['password'])){
            // fallback: plain text password (legacy)
            if(!$user||$cur!==$user['password']){setFlash('error','Mật khẩu hiện tại không đúng');header('Location:'.APP_URL.'/account');exit;}
        }
        $db->query("UPDATE users SET password=? WHERE id=?",array(password_hash($new,PASSWORD_BCRYPT),$_SESSION['user_id']));
        setFlash('success','Đổi mật khẩu thành công!');header('Location:'.APP_URL.'/account');exit;
    }
    public function cancelOrder($p=null){
        header('Content-Type: application/json');
        if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(array('success'=>false,'message'=>'Invalid'));return;}
        $b=json_decode(file_get_contents('php://input'),true)??array();
        $oid=(int)($b['order_id']??0);
        if(!$oid){echo json_encode(array('success'=>false,'message'=>'Thiếu dữ liệu'));return;}
        $db=Database::getInstance();
        $o=$db->fetch("SELECT id,status,user_id FROM orders WHERE id=?",array($oid));
        if(!$o||$o['user_id']!=$_SESSION['user_id']){echo json_encode(array('success'=>false,'message'=>'Không tìm thấy đơn hàng'));return;}
        if($o['status']!=='pending'){echo json_encode(array('success'=>false,'message'=>'Chỉ có thể hủy đơn chờ xác nhận'));return;}
        $db->query("UPDATE orders SET status='cancelled',updated_at=NOW() WHERE id=?",array($oid));
        echo json_encode(array('success'=>true));
    }
}
