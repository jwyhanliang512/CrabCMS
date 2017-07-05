<?php

namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
//        return 'this is admin Index index';
         return $this->fetch();
    }
}
