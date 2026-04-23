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
        // Capture ?redirect= param into session on GET
        if ($_SERVER['REQUEST_METHOD']!=='POST' && !empty($_GET['redirect'])) {
            $redir = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
            if (strpos($redir, APP_URL) === 0) {
                $_SESSION['redirect_after_login'] = $redir;
            }
        }
        $error='';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $email=trim(isset($_POST['email'])?$_POST['email']:'');
            $password=isset($_POST['password'])?$_POST['password']:'';
            if(!$email||!$password){$error='Vui lòng nhập đầy đủ.';}
            elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){$error='Email không hợp lệ.';}
            else{
                $user=$this->userModel->findByEmail($email);
                $storedPw = $user ? $user['password'] : '';
                $pwInfo = $user ? password_get_info($storedPw) : ['algo'=>0];
                $isPlaintext = $user && !$pwInfo['algo'] && $password === rtrim($storedPw, "\r\n ");
                $passOk = $user && (
                    password_verify($password,$storedPw) || $isPlaintext
                );
                if($passOk){
                    if(!$user['is_active']){$error='Tài khoản đã bị khóa.';}
                    else{
                        // Auto-upgrade plaintext passwords to bcrypt
                        if($isPlaintext){
                            $this->userModel->updatePassword($user['id'],password_hash($password,PASSWORD_BCRYPT));
                        }
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
        // Capture ?redirect= param into session on GET
        if ($_SERVER['REQUEST_METHOD']!=='POST' && !empty($_GET['redirect'])) {
            $redir = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
            if (strpos($redir, APP_URL) === 0) {
                $_SESSION['redirect_after_login'] = $redir;
            }
        }
        $error='';$old=array();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $old=$_POST;
            $name=trim(isset($_POST['fullname'])?$_POST['fullname']:'');
            $email=trim(isset($_POST['email'])?$_POST['email']:'');
            $phone=trim(isset($_POST['phone'])?$_POST['phone']:'');
            $pass=isset($_POST['password'])?$_POST['password']:'';
            $confirm=isset($_POST['confirm_password'])?$_POST['confirm_password']:'';
            if(!$name||!$email||!$pass){$error='Vui lòng điền đầy đủ.';}
            elseif(mb_strlen($name)<2||mb_strlen($name)>100){$error='Họ tên từ 2–100 ký tự.';}
            elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){$error='Email không hợp lệ.';}
            elseif($phone&&!preg_match('/^(0[3-9]\d{8}|02\d{9})$/',$phone)){$error='Số điện thoại không hợp lệ.';}
            elseif(strlen($pass)<6){$error='Mật khẩu ít nhất 6 ký tự.';}
            elseif(strlen($pass)>255){$error='Mật khẩu quá dài.';}
            elseif($pass!==$confirm){$error='Mật khẩu không khớp.';}
            elseif($this->userModel->findByEmail($email)){$error='Email đã được đăng ký.';}
            else{
                $id=$this->userModel->create(array('fullname'=>$name,'email'=>$email,'phone'=>$phone,'password'=>$pass));
                $_SESSION['user_id']=$id;$_SESSION['user_name']=$name;$_SESSION['user_email']=$email;$_SESSION['user_role']=0;
                $this->cartModel->mergeGuestCart($id);
                Logger::log('CREATE','users',$id,null,['fullname'=>$name,'email'=>$email]);
                setFlash('success','Đăng ký thành công! Chào mừng '.$name);
                $redirect=isset($_SESSION['redirect_after_login'])?$_SESSION['redirect_after_login']:APP_URL.'/';
                unset($_SESSION['redirect_after_login']);
                header('Location:'.$redirect);exit;
            }
        }
        $pageTitle='Đăng ký';include __DIR__.'/../Views/auth/register.php';
    }

    public function googleLogin($p=null){
        if(isLoggedIn()){header('Location:'.APP_URL.'/');exit;}
        if(!GOOGLE_CLIENT_ID){setFlash('error','Google OAuth chưa được cấu hình.');header('Location:'.APP_URL.'/auth/login');exit;}
        // Save redirect intent
        if(!empty($_GET['redirect'])){
            $redir=filter_var($_GET['redirect'],FILTER_SANITIZE_URL);
            if(strpos($redir,APP_URL)===0) $_SESSION['redirect_after_login']=$redir;
        }
        $state=bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state']=$state;
        $params=http_build_query(array(
            'client_id'     =>GOOGLE_CLIENT_ID,
            'redirect_uri'  =>APP_URL.'/auth/google-callback',
            'response_type' =>'code',
            'scope'         =>'openid email profile',
            'state'         =>$state,
            'access_type'   =>'online',
            'prompt'        =>'select_account',
        ));
        header('Location:https://accounts.google.com/o/oauth2/v2/auth?'.$params);exit;
    }

    public function googleCallback($p=null){
        if(isLoggedIn()){header('Location:'.APP_URL.'/');exit;}
        $code   =isset($_GET['code'])  ?$_GET['code']  :'';
        $state  =isset($_GET['state']) ?$_GET['state'] :'';
        $error  =isset($_GET['error']) ?$_GET['error'] :'';
        if($error||!$code){setFlash('error','Đăng nhập Google thất bại: '.htmlspecialchars($error?:'Không nhận được mã.'));header('Location:'.APP_URL.'/auth/login');exit;}
        if(!$state||!isset($_SESSION['google_oauth_state'])||$state!==$_SESSION['google_oauth_state']){
            setFlash('error','Yêu cầu không hợp lệ (state mismatch).');header('Location:'.APP_URL.'/auth/login');exit;
        }
        unset($_SESSION['google_oauth_state']);
        // Exchange code for token
        $tokenResp=$this->_googlePost('https://oauth2.googleapis.com/token',array(
            'code'          =>$code,
            'client_id'     =>GOOGLE_CLIENT_ID,
            'client_secret' =>GOOGLE_CLIENT_SECRET,
            'redirect_uri'  =>APP_URL.'/auth/google-callback',
            'grant_type'    =>'authorization_code',
        ));
        if(empty($tokenResp['access_token'])){setFlash('error','Không lấy được token từ Google.');header('Location:'.APP_URL.'/auth/login');exit;}
        // Get user info
        $info=$this->_googleGet('https://www.googleapis.com/oauth2/v3/userinfo',$tokenResp['access_token']);
        if(empty($info['sub'])||empty($info['email'])){setFlash('error','Không lấy được thông tin tài khoản Google.');header('Location:'.APP_URL.'/auth/login');exit;}
        $googleId =$info['sub'];
        $email    =strtolower(trim($info['email']));
        $name     =isset($info['name'])?trim($info['name']):$email;
        $avatar   =isset($info['picture'])?$info['picture']:'';
        // 1) Already linked via google_id
        $user=$this->userModel->findByGoogleId($googleId);
        if(!$user){
            // 2) Email exists → link the account
            $user=$this->userModel->findByEmail($email);
            if($user) $this->userModel->linkGoogleId($user['id'],$googleId);
            else{
                // 3) Create new account
                $id=$this->userModel->createViaGoogle(array('fullname'=>$name,'email'=>$email,'avatar'=>$avatar,'google_id'=>$googleId));
                $user=$this->userModel->findById($id);
                Logger::log('CREATE','users',$id,null,array('fullname'=>$name,'email'=>$email,'via'=>'google'));
            }
        }
        if(!$user['is_active']){setFlash('error','Tài khoản đã bị khóa.');header('Location:'.APP_URL.'/auth/login');exit;}
        $_SESSION['user_id']  =$user['id'];
        $_SESSION['user_name'] =$user['fullname'];
        $_SESSION['user_email']=$user['email'];
        $_SESSION['user_role'] =$user['role'];
        $this->userModel->updateLastLogin($user['id']);
        $this->cartModel->mergeGuestCart($user['id']);
        Logger::log('LOGIN','users',$user['id'],null,array('email'=>$user['email'],'via'=>'google'));
        if(in_array((int)$user['role'],[1,2,3])){header('Location:'.APP_URL.'/admin');exit;}
        $redirect=isset($_SESSION['redirect_after_login'])?$_SESSION['redirect_after_login']:APP_URL.'/';
        unset($_SESSION['redirect_after_login']);
        header('Location:'.$redirect);exit;
    }

    private function _googlePost($url,$fields){
        $ctx=stream_context_create(array('http'=>array(
            'method' =>'POST',
            'header' =>"Content-Type: application/x-www-form-urlencoded\r\n",
            'content'=>http_build_query($fields),
            'timeout'=>10,
        )));
        $resp=@file_get_contents($url,false,$ctx);
        return $resp?json_decode($resp,true):array();
    }

    private function _googleGet($url,$token){
        $ctx=stream_context_create(array('http'=>array(
            'method' =>'GET',
            'header' =>"Authorization: Bearer {$token}\r\n",
            'timeout'=>10,
        )));
        $resp=@file_get_contents($url,false,$ctx);
        return $resp?json_decode($resp,true):array();
    }

    public function forgotPassword($p=null){
        if(isLoggedIn()){header('Location:'.APP_URL.'/account');exit;}
        $pageTitle='Quên mật khẩu';include __DIR__.'/../Views/auth/forgot_password.php';
    }

    public function sendOtp($p=null){
        header('Content-Type: application/json');
        if($_SERVER['REQUEST_METHOD']!=='POST'){echo json_encode(array('success'=>false,'message'=>'Method not allowed'));return;}
        $b=json_decode(file_get_contents('php://input'),true)??array();
        $email=strtolower(trim($b['email']??''));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){echo json_encode(array('success'=>false,'message'=>'Email không hợp lệ'));return;}
        $user=$this->userModel->findByEmail($email);
        if(!$user||!$user['is_active']){
            // Don't reveal if email exists
            echo json_encode(array('success'=>true));return;
        }
        require_once __DIR__.'/../Helpers/MailService.php';
        $otp=UserModel::generateOtp($email,'forgot');
        if($otp===false){echo json_encode(array('success'=>false,'message'=>'Gửi quá nhiều lần, vui lòng thử lại sau 15 phút'));return;}
        $sent=MailService::sendOtp($email,$user['fullname'],$otp,'forgot');
        if(!$sent){echo json_encode(array('success'=>false,'message'=>'Không thể gửi email, vui lòng thử lại sau'));return;}
        echo json_encode(array('success'=>true));
    }

    public function resetPassword($p=null){
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        $email=strtolower(trim($_POST['email']??''));
        $otp=trim($_POST['otp']??'');
        $newPw=$_POST['new_password']??'';
        $cfPw=$_POST['confirm_password']??'';
        if(!$email||!$otp||!$newPw){setFlash('error','Vui lòng điền đầy đủ');header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        if(strlen($newPw)<6){setFlash('error','Mật khẩu mới ít nhất 6 ký tự');header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        if($newPw!==$cfPw){setFlash('error','Mật khẩu nhập lại không khớp');header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        if(!UserModel::verifyOtp($email,$otp,'forgot')){setFlash('error','Mã OTP không đúng hoặc đã hết hạn');header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        $user=$this->userModel->findByEmail($email);
        if(!$user){setFlash('error','Tài khoản không tồn tại');header('Location:'.APP_URL.'/auth/forgot-password');exit;}
        $this->userModel->updatePassword($user['id'],password_hash($newPw,PASSWORD_BCRYPT));
        Logger::log('CHANGE_PASSWORD','users',$user['id'],null,array('method'=>'otp_reset'));
        setFlash('success','Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
        header('Location:'.APP_URL.'/auth/login');exit;
    }

    public function logout($p=null){
        if(isLoggedIn()){
            Logger::log('LOGOUT','users',$_SESSION['user_id'],['email'=>$_SESSION['user_email']??''],null);
        }
        session_destroy();
        header('Location:'.APP_URL.'/auth/login');exit;
    }
}