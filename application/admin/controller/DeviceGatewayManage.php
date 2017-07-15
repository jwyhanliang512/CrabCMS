<?php

namespace app\admin\controller;

use think\Controller;
use app\common\functions\ComFunciton as ComFunc;

class DeviceGatewayManage extends Controller
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
    public function index() {
        $username = session('crabstudio_session_username');
        return $this->fetch('index',[ 'username'  => $username ]);
    }
    
 
}