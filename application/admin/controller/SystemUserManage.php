<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;

use app\common\functions\ComFunciton as ComFunc;

class SystemUserManage extends Controller
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
    public function index(){
        $username = session('crabstudio_session_username');
        return $this->fetch('index',[ 'username'  => $username]);
    }
    
    /**
     * 用户管理列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_user_list() {
        
        //$where后面where查询筛选条件初始化
        $where = " 1=1 ";
        if(!empty($_POST['username'])){
            $where = $where." and a.username like '%{$_POST['username']}%' ";
        };
        if(!empty($_POST['linkphone'])){
            $where = $where." and a.linkphone like '%{$_POST['linkphone']}%' ";
        };
        //sql语句
        $s1 = " SELECT 
                    a.objectid,
                    a.username,
                    ifnull(a.linkphone,'') as linkphone,
                    ifnull(a.email,'') as email,
                    a.lastlogintime,
                    a.createtime,
                    a.createmanid,
                    b.username as createman,
                    a.modifytime,
                    a.modifymanid,
                    c.username as modifyman 
                FROM 
                    global_user a
                INNER JOIN global_user b ON a.createmanid = b.objectid
                INNER JOIN global_user c ON a.modifymanid = c.objectid
                where $where ";
        $userList = Db::query($s1);
        //封装成gridManager需要的格式
        $result['data'] = $userList;
        $result['totals'] = count($userList);
        return json($result);
    }
    
    public function edit_user($id) {
        print_r($id);
    }
    
 
}