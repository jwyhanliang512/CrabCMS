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
                    a.password,
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
    
    /**
     * 编辑用户信息
     */
    public function edit_user() {
        if(!empty($_POST)){
            $userid = session('crabstudio_session_userid');
            $global_user = db('global_user');
            $res  = $global_user->where('username',$_POST['username']) 
                                ->update([  'linkphone'     => $_POST['linkphone'],
                                            'email'         => $_POST['email'],
                                            'password'      => $_POST['password'],
                                            'modifymanid'   => $userid,
                                            'modifytime'    => date("Y-m-d H:i:s")]);
            if($res){
                $this->redirect(url('/admin/SystemUserManage/index'));
            }
        }else{
            $this->redirect(url('/admin/SystemUserManage/index'));
        }
    }
    
    /**
     * 增加用户
     */
    public function add_user() {
        if(!empty($_POST)){
            $userid = session('crabstudio_session_userid');
            $global_user = db('global_user');
            $data = [   'username'      => $_POST['username'], 
                        'password'      => $_POST['password'], 
                        'linkphone'     => $_POST['linkphone'], 
                        'email'         => $_POST['email'], 
                        'createmanid'   => $userid,
                        'createtime'    => date("Y-m-d H:i:s"),
                        'modifymanid'   => $userid,
                        'modifytime'    => date("Y-m-d H:i:s"),
                    ];
            $res  = $global_user-> insert($data);
            if($res){
                $this->redirect(url('/admin/SystemUserManage/index'));
            }
        }else{
            $this->redirect(url('/admin/SystemUserManage/index'));
        }
    }
    
    /**
     * 增加用户时校验用户名是否重复
     * @return int
     */
    public function check_user_duplicate() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $useInfo = $global_user->where('username',$_POST['username'])->find();
            //校验用户名,重复返回1，不重复返回0
            if($useInfo){
                return 1;
            }else{
                return 0;
            }
        }
    }
    
    
    /**
     * 删除用户
     * @return int
     */
    public function delete_user() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $res  = $global_user->where('objectid',$_POST['userid']) -> delete();
            if($res){
                return 1;
            }else{
                return 0;
            }
        }else{
            $this->redirect(url('/admin/SystemUserManage/index'));
        }
    }
    
   
    
 
}
