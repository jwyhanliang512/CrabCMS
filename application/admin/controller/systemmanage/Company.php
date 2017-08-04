<?php

namespace app\admin\controller\systemmanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;

class Company extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index(){
        $common = new ComFunc();
        $username = $common->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
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
            $common = new ComFunc();
            $userid = $common->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_user = db('global_user');
            $res  = $global_user->where('username',$_POST['username']) 
                                ->update([  'linkphone'     => $_POST['linkphone'],
                                            'email'         => $_POST['email'],
                                            'password'      => $_POST['password'],
                                            'modifymanid'   => $userid,
                                            'modifytime'    => date("Y-m-d H:i:s")]);
            if($res){
                $this->redirect(url('/admin/systemmanage.user/index'));
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
        }
    }
    
    /**
     * 增加用户
     */
    public function add_user() {
        if(!empty($_POST)){
            $common = new ComFunc();
            $userid = $common->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_user = db('global_user');
            $data = [   'username'      => $_POST['username'], 
                        'password'      => $_POST['password'], 
                        'linkphone'     => $_POST['linkphone'], 
                        'email'         => $_POST['email'], 
                        'createmanid'   => $userid,
                        'createtime'    => date("Y-m-d H:i:s"),
                        'modifymanid'   => $userid,
                        'modifytime'    => date("Y-m-d H:i:s")
                    ];
            try{
                $resObjectid  = $global_user-> insertGetId($data);
            }catch(\Exception $e){
                    abort(500, '新增用户异常');
            }
            if($resObjectid){
                //根据返回的用户主键值，建立对应的数据表
                try{
                    $createDB1 = "SET FOREIGN_KEY_CHECKS=0;";
                    Db::execute($createDB1);   
                    $createDB2 = "DROP TABLE IF EXISTS `ms_data_history_"."$resObjectid`";
                    Db::execute($createDB2);   
                    $createDB3 = "CREATE TABLE `ms_data_history_".$resObjectid."` (
                                   `objectid` bigint(20) NOT NULL AUTO_INCREMENT,
                                    `uid` int(10) NOT NULL COMMENT '终端id标识',
                                    `mstype` int(5) NOT NULL COMMENT '终端类型',
                                    `rawdata` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '原始数据',
                                    `parseddata` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '解析后的数据',
                                    `uptime` datetime NOT NULL COMMENT '上报时间',
                                    PRIMARY KEY (`objectid`)
                                  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                    Db::execute($createDB3);
                }catch(\Exception $e){
                    abort(500, '新增用户时新建历史数据表异常');
                }
                $this->redirect(url('/admin/systemmanage.user/index'));
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
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
//                try{
//                    $createDB1 = "SET FOREIGN_KEY_CHECKS=0;";
//                    Db::execute($createDB1);   
//                    $createDB2 = "DROP TABLE IF EXISTS `ms_data_history_"."{$_POST['userid']}`";
//                    Db::execute($createDB2);   
//                }catch(\Exception $e){
//                    abort(500, '删除用户时删除对应历史数据表异常');
//                }
                return 1;
            }else{
                return 0;
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
        }
    }
    
    public function set_company_tree() {
        //sql语句,需要排除objectid为-1的无效行
        $s1 = " SELECT 
                    objectid as id,
                    parentid as pId,
                    companyname as name,
                    'true' as open
                FROM 
                    global_company
                WHERE 
                    objectid <> -1";
        $companyList = Db::query($s1);
        return json($companyList);
    }
    
 
}
