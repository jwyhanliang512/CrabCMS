<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

use app\common\functions\ComFunciton as comFunc;


class Index extends controller
{
//    /**
//     * 控制器初始化，进行是否处于已登录状态判断
//     * 使用控制器初始化方法  _initialize
//     */
//    public function _initialize()
//    {
//        $common = new comFunc();
//        $common -> checkUserSession();
//    }
    
    /**
     * 是否处于已登录状态判断
     * 这个函数只在Index.php使用，其他类里使用 _initialize()
     */
    public function check_session_username()
    {
        $sessionName = session('crabstudio_session_username');
        if(empty($sessionName)){
            $this->redirect(url('index/index/index/login'));
        }
    }
    
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
            $global_user = db('global_user');
            $useInfo = $global_user->where('username',$_POST['username'])->find();
            //校验用户名
            if($useInfo){
                //校验密码
                if($useInfo['password']===$_POST['password']){
                    session('crabstudio_session_username',$_POST['username']);
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
     */
    public function forget_password() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $useInfo = $global_user->where('username',$_POST['username'])->find();
            //校验用户名
            if($useInfo){
                //校验邮箱,注意统一为大写比较
                if(strtoupper($useInfo['email']) === strtoupper($_POST['email'])){
                    $common = new comFunc();
                    if($common -> sendEmail($_POST['email'], $_POST['username'], "密码取回 —— Crab Studio", '尊敬的客户，您好！您的【'.$_POST['username'].'】账号密码为【'.$useInfo['password'].'】,请您妥善保管注意泄露！ ')){
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
