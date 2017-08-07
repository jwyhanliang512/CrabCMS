<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

use app\common\functions\ComFunciton as ComFunc;
use app\common\functions\UtilsFunction as UtilsFunc;


class Index extends controller
{
//    /**
//     * 控制器初始化，进行是否处于已登录状态判断
//     * 使用控制器初始化方法  _initialize
//     */
//    public function _initialize()
//    {
//        $common = new ComFunc();
//        $common -> checkUserSession();
//    }
    
    
    /**
     * 初始化整个项目
     */
    public function index()
    {
        $sessionName = session('crabstudio_session_username');
        if(empty($sessionName)){
            $this->redirect(url('/index.php/index/index/login'));
        }else{
            $this->redirect(url('/index.php/admin/index/index'));
        }
        
    }
    
    /*
     * 初始登录
     */
    public function login() {
        $flag = ""; //0登录时填写的用户名不存在，1登录填写的密码错误,2获取密码邮件发送失败，3获取密码邮件发送成功，4获取密码用户名和邮箱不匹配，5获取密码填写的用户名不存在
        if(!empty($_POST)){
            $s1 = " 
                SELECT 
                    guser.objectid,
                    guser.username,
                    guser.password,
                    guser.companyid,
                    gcom.companyname,
                    gcom.levelcode,
                    gcom.parentid as companyparentid
                FROM 
                    global_user guser
                LEFT JOIN global_company gcom ON guser.companyid = gcom.objectid
                where username = '{$_POST["username"]}'";
            $res = Db::query($s1);
            //校验用户名
            if($res){
                $useInfo = $res[0];
                //校验密码
                if($useInfo['password']===$_POST['password']){
                    $commonUtils = new UtilsFunc();
                    $sesson_username = $commonUtils ->authcode($_POST['username'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_userid   = $commonUtils ->authcode($useInfo['objectid'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_user_companyid  = $commonUtils ->authcode($useInfo['companyid'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_user_companylevelcode = $commonUtils ->authcode($useInfo['levelcode'], "ENCODE", config('authcodeKey'), 0);
                    session('crabstudio_session_username',$sesson_username);
                    session('crabstudio_session_userid',$sesson_userid);
                    session('crabstudio_session_user_companyid',$sesson_user_companyid);
                    session('crabstudio_session_user_companylevelcode',$sesson_user_companylevelcode);
                    $global_user = db('global_user');
                    $global_user ->where('username',$_POST['username']) ->update(['lastlogintime' => date("Y-m-d H:i:s")]);
                    $this->redirect(url('/admin/index/index'));             
                }else{
                    $flag = 1;
                }
            }else{   
                $flag = 0;
            }
        }

        return $this->fetch('login',[ 'flag'  => $flag]);
    }
    
    /**
     * 退出登录
     */
    public function logout(){
        session('crabstudio_session_username',null);
        $this->redirect(url('/index/index/login'));
    }
    
    
    /**
     * 忘记密码
     * @return type
     */
    public function forget_password() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $useInfo = $global_user->where('username',$_POST['username'])->find();
            //校验用户名
            if($useInfo){
                //校验邮箱,注意统一为大写比较
                if(strtoupper($useInfo['email']) === strtoupper($_POST['email'])){
                    $commonUtils = new UtilsFunc();
                    if($commonUtils -> sendEmail($_POST['email'], $_POST['username'], "密码取回 [ Crab Studio ]", '尊敬的客户，您好！您的【'.$_POST['username'].'】账号密码为【'.$useInfo['password'].'】,请您妥善保管注意泄露！ ')){
                        return $this->fetch('login',[ 'flag'  => 3 ]);
                    }else{
                        return $this->fetch('login',[ 'flag'  => 2 ]);
                    }
                }else{
                    return $this->fetch('login',[ 'flag'  => 4 ]);
                }
            }else{   
                return $this->fetch('login',[ 'flag'  => 5 ]);
            }
           
        }
    }
}
