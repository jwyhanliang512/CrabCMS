<?php

namespace app\elsonic\controller\devicemanage;

use think\Db;

use app\elsonic\controller\Index;
use app\common\functions\Universal;
use app\common\functions\Utils;

class ElsonicMs extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index() {
        $Utils = new Utils();
        $username = $Utils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
 
}
