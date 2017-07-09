<?php

namespace app\admin\controller;

use think\Controller;

use app\common\functions\ComFunciton as comFunc;

class Index extends Controller
{
    
    /**
     * 控制器初始化，进行是否处于已登录状态判断
     * 使用控制器初始化方法  _initialize
     */
    public function _initialize()
    {
        $common = new comFunc();
        $common -> checkUserSession();
    }
    
    public function index()
    {
//        return 'this is admin Index index';
         return $this->fetch();
    }
}
