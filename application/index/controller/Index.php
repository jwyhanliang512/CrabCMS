<?php
namespace app\index\controller;
use think\Controller;

class Index extends controller
{
    public function index()
    {

        //可以跨模块调用
        //$this->redirect(url('/index.php/admin/index/index'));

        $this->redirect(url('/index.php/index/index/login'));
    }
    
    public function login() {
        if(!empty($_POST)){
            echo '11111111';
        }
        return $this->fetch();
    }
}
