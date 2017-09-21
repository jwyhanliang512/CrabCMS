<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

use app\common\functions\Utils;


class Index extends Controller
{
 
    
    /**
     * 初始化整个项目
     */
    public function index()
    {
        $Utils = new Utils();
        $sesson_top_companycode = $Utils->authcode(session('crabstudio_session_top_companycode'), "DECODE", config('authcodeKey'), 0);
        if(empty($sesson_top_companycode)){
            $this->redirect(url('/index.php/index/index/login'));
        }else{
            $this->redirect(url('/index.php/'.$sesson_top_companycode.'/index/index'));
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
                    gcom.companycode,
                    gcom.levelcode,
                    gcom.parentid as companyparentid
                FROM 
                    global_user guser
                LEFT JOIN global_company gcom ON guser.companyid = gcom.objectid
                where guser.username = '{$_POST["username"]}'";
            $res = Db::query($s1);
            //校验用户名
            if($res){
                $useInfo = $res[0];
                //校验密码
                if($useInfo['password']===$_POST['password']){
                    //用户相关信息入缓存
                    $Utils = new Utils();
                    $sesson_username = $Utils ->authcode($_POST['username'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_userid   = $Utils ->authcode($useInfo['objectid'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_user_companyid  = $Utils ->authcode($useInfo['companyid'], "ENCODE", config('authcodeKey'), 0);
                    $sesson_user_companylevelcode = $Utils ->authcode($useInfo['levelcode'], "ENCODE", config('authcodeKey'), 0);
                    session('crabstudio_session_username',$sesson_username);
                    session('crabstudio_session_userid',$sesson_userid);
                    session('crabstudio_session_user_companyid',$sesson_user_companyid);
                    session('crabstudio_session_user_companylevelcode',$sesson_user_companylevelcode);
                    //更新用户最新登录时间
                    $global_user = db('global_user');
                    $global_user ->where('username',$_POST['username']) ->update(['lastlogintime' => date("Y-m-d H:i:s")]);
                    //根据用户所属企业的最高父企业，分配url
                    if(strlen($useInfo['levelcode']) >= 10 ){
                        $topCompanyLevel = substr($useInfo['levelcode'], 0, 10);
                        $s1 = " SELECT 
                                    companycode
                                FROM 
                                    global_company where levelcode = '$topCompanyLevel'";
                        $res = Db::query($s1);
                        if($res){
                            $topCompanycode = $res[0]['companycode'];
                            //非最高权限用户，根据最高父企业编码作为模块，定向url
                            $sesson_top_companycode = $Utils ->authcode($topCompanycode, "ENCODE", config('authcodeKey'), 0);
                            session('crabstudio_session_top_companycode',$sesson_top_companycode);
                            $this->redirect(url('/'.$topCompanycode.'/index/index'));   
                        }else{
                            $flag = 6;
                        }
                    }else{
                        //最高权限admin登陆
                        $topCompanycode = 'admin';
                        $sesson_top_companycode = $Utils ->authcode($topCompanycode, "ENCODE", config('authcodeKey'), 0);
                        session('crabstudio_session_top_companycode',$sesson_top_companycode);
                        $this->redirect(url('/admin/index/index'));     
                    }
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
        session('crabstudio_session_userid',null);
        session('crabstudio_session_user_companyid',null);
        session('crabstudio_session_user_companylevelcode',null);
        session('crabstudio_session_top_companycode',null);
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
                    $Utils = new Utils();
                    if($Utils -> sendEmail($_POST['email'], $_POST['username'], "密码取回 [ Crab Studio ]", '尊敬的客户，您好！您的【'.$_POST['username'].'】账号密码为【'.$useInfo['password'].'】,请您妥善保管注意泄露！ ')){
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
