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
        $uid=$_SESSION['user_id'];
        $db=Database::getInstance();
        // Email update with uniqueness check
        $newEmail=trim($_POST['email']??'');
        if($newEmail){
            if(!filter_var($newEmail,FILTER_VALIDATE_EMAIL)){setFlash('error','Email không hợp lệ');header('Location:'.APP_URL.'/account');exit;}
            $cur=$db->fetch("SELECT email FROM users WHERE id=?",array($uid));
            if($newEmail!==($cur['email']??'')){
                if($db->fetch("SELECT id FROM users WHERE email=? AND id!=?",array($newEmail,$uid))){setFlash('error','Email này đã được sử dụng bởi tài khoản khác');header('Location:'.APP_URL.'/account');exit;}
                $db->query("UPDATE users SET email=? WHERE id=?",array($newEmail,$uid));
                $_SESSION['user_email']=$newEmail;
            }
        }
        (new UserModel())->update($uid,array('fullname'=>sanitize($_POST['fullname']??''),'phone'=>sanitize($_POST['phone']??''),'city'=>sanitize($_POST['city']??''),'district'=>sanitize($_POST['district']??''),'address'=>sanitize($_POST['address']??'')));
        $_SESSION['user_name']=sanitize($_POST['fullname']??'');
        setFlash('success','Cập nhật thành công!');header('Location:'.APP_URL.'/account');exit;
    }
    public function sendChangeOtp($p=null){
        header('Content-Type: application/json');
        if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(array('success'=>false,'message'=>'Method not allowed'));return;}
        $email=$_SESSION['user_email']??'';
        if(!$email){echo json_encode(array('success'=>false,'message'=>'Phiên đăng nhập không hợp lệ'));return;}
        require_once __DIR__.'/../Helpers/MailService.php';
        $otp=UserModel::generateOtp($email,'change');
        if($otp===false){echo json_encode(array('success'=>false,'message'=>'Gửi quá nhiều lần, vui lòng thử lại sau 15 phút'));return;}
        $user=(new UserModel())->findById($_SESSION['user_id']);
        $sent=MailService::sendOtp($email,$user['fullname']??'',$otp,'change');
        if(!$sent){echo json_encode(array('success'=>false,'message'=>'Không thể gửi email, vui lòng thử lại sau'));return;}
        echo json_encode(array('success'=>true));
    }

    public function verifyChangePassword($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/account');exit;}
        $email=$_SESSION['user_email']??'';
        $otp=trim($_POST['otp']??'');
        $new=$_POST['new_password']??'';
        $cf=$_POST['confirm_password']??'';
        if(!$otp||!$new){setFlash('error','Vui lòng điền đầy đủ');header('Location:'.APP_URL.'/account');exit;}
        if(strlen($new)<6){setFlash('error','Mật khẩu mới ít nhất 6 ký tự');header('Location:'.APP_URL.'/account');exit;}
        if($new!==$cf){setFlash('error','Mật khẩu nhập lại không khớp');header('Location:'.APP_URL.'/account');exit;}
        if(!UserModel::verifyOtp($email,$otp,'change')){setFlash('error','Mã OTP không đúng hoặc đã hết hạn');header('Location:'.APP_URL.'/account');exit;}
        require_once __DIR__.'/../Helpers/Logger.php';
        $db=Database::getInstance();
        $db->query("UPDATE users SET password=? WHERE id=?",array(password_hash($new,PASSWORD_BCRYPT),$_SESSION['user_id']));
        Logger::log('CHANGE_PASSWORD','users',$_SESSION['user_id'],null,array('method'=>'otp_change'));
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
