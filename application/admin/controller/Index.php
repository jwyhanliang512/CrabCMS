<?php

namespace app\admin\controller;

use think\Controller;
use app\common\functions\ComFunciton as ComFunc;
use app\common\functions\UtilsFunction as UtilsFunc;

class Index extends Controller
{
    
    /**
     * 控制器初始化，进行是否处于已登录状态判断
     * 使用控制器初始化方法  _initialize
     */
    public function _initialize()
    {
        $common = new ComFunc();
        $common -> checkUserSession();
    }
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index()
    {
        $commonUtils = new UtilsFunc();
        $username = $commonUtils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
}
