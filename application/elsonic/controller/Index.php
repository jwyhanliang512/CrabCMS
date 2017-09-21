<?php
namespace app\elsonic\controller;
use think\Controller;
use think\Request;

use app\common\functions\Universal;
use app\common\functions\Utils;

class Index extends Controller
{
    
    /**
     * 控制器初始化，进行是否处于已登录状态判断
     * 使用控制器初始化方法  _initialize
     */
    public function _initialize()
    {
        $Universal = new Universal();
        $Universal -> checkUserSession();
        $this->checkUserModule();
    }
    
    /**
     * 获取当前模块，这个是最高企业的编码，判断登录名所属企业的编码是否属于模块名（最高企业）
     */
    public function checkUserModule() {
        $Utils = new Utils();
        $sesson_top_companycode = $Utils->authcode(session('crabstudio_session_top_companycode'), "DECODE", config('authcodeKey'), 0);
        $module = Request::instance()->module();
        if($sesson_top_companycode != $module){
            $this->redirect(url('/index/index/logout'));
        }
    }
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index()
    {
        $Utils = new Utils();
        $username = $Utils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
}
