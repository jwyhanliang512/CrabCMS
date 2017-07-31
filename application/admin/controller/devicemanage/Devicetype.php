<?php

namespace app\admin\controller\devicemanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;

class Devicetype extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index() {
        $common = new ComFunc();
        $username = $common->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
    
 
}
