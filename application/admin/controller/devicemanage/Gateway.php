<?php

namespace app\admin\controller\devicemanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;
use app\common\functions\UtilsFunction as UtilsFunc;

class Gateway extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index() {
        $commonUtils = new UtilsFunc();
        $username = $commonUtils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
    
 
}
