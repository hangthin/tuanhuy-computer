<?php
require_once __DIR__.'/../Models/Models.php';
require_once __DIR__.'/../Helpers/Logger.php';

class AuthController {
    private $userModel;
    private $cartModel;
    public function __construct(){ $this->userModel=new UserModel();$this->cartModel=new CartModel(); }
    public function index($p=null){ $this->login(); }

    public function login($p=null){
        if(isLoggedIn()){header('Location:'.APP_URL.'/');exit;}
        $error='';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $email=trim(isset($_POST['email'])?$_POST['email']:'');
            $password=isset($_POST['password'])?$_POST['password']:'';
            if(!$email||!$password){$error='Vui lòng nhập đầy đủ.';}
            else{
                $user=$this->userModel->findByEmail($email);
                $passOk = $user && (
                    password_verify($password,$user['password']) ||
                    $password === $user['password']   // fallback cho tài khoản cũ chưa hash
                );
                if($passOk){
                    if(!$user['is_active']){$error='Tài khoản đã bị khóa.';}
                    else{
                        $_SESSION['user_id']=$user['id'];
                        $_SESSION['user_name']=$user['fullname'];
                        $_SESSION['user_email']=$user['email'];
                        $_SESSION['user_role']=$user['role'];
                        $this->userModel->updateLastLogin($user['id']);
                        $this->cartModel->mergeGuestCart($user['id']);
                        Logger::log('LOGIN','users',$user['id'],null,['email'=>$user['email'],'role'=>$user['role']]);
                        // Admin / Manager / Staff → admin panel
                        if(in_array((int)$user['role'],[1,2,3])){
                            header('Location:'.APP_URL.'/admin');exit;
                        }
                        $redirect=isset($_SESSION['redirect_after_login'])?$_SESSION['redirect_after_login']:APP_URL.'/';
                        unset($_SESSION['redirect_after_login']);
                        header('Location:'.$redirect);exit;
                    }
                } else $error='Email hoặc mật khẩu không đúng.';
            }
        }
        $pageTitle='Đăng nhập';include __DIR__.'/../Views/auth/login.php';
    }

    public function register($p=null){
        if(isLoggedIn()){header('Location:'.APP_URL.'/');exit;}
        $error='';$old=array();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $old=$_POST;
            $name=trim(isset($_POST['fullname'])?$_POST['fullname']:'');
            $email=trim(isset($_POST['email'])?$_POST['email']:'');
            $phone=trim(isset($_POST['phone'])?$_POST['phone']:'');
            $pass=isset($_POST['password'])?$_POST['password']:'';
            $confirm=isset($_POST['confirm_password'])?$_POST['confirm_password']:'';
            if(!$name||!$email||!$pass){$error='Vui lòng điền đầy đủ.';}
            elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){$error='Email không hợp lệ.';}
            elseif(strlen($pass)<6){$error='Mật khẩu ít nhất 6 ký tự.';}
            elseif($pass!==$confirm){$error='Mật khẩu không khớp.';}
            elseif($this->userModel->findByEmail($email)){$error='Email đã được đăng ký.';}
            else{
                $id=$this->userModel->create(array('fullname'=>$name,'email'=>$email,'phone'=>$phone,'password'=>$pass));
                $_SESSION['user_id']=$id;$_SESSION['user_name']=$name;$_SESSION['user_email']=$email;$_SESSION['user_role']=0;
                $this->cartModel->mergeGuestCart($id);
                Logger::log('CREATE','users',$id,null,['fullname'=>$name,'email'=>$email]);
                setFlash('success','Đăng ký thành công! Chào mừng '.$name);
                header('Location:'.APP_URL.'/');exit;
            }
        }
        $pageTitle='Đăng ký';include __DIR__.'/../Views/auth/register.php';
    }

    public function logout($p=null){
        if(isLoggedIn()){
            Logger::log('LOGOUT','users',$_SESSION['user_id'],['email'=>$_SESSION['user_email']??''],null);
        }
        session_destroy();
        header('Location:'.APP_URL.'/auth/login');exit;
    }
}