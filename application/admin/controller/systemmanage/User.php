<?php

namespace app\admin\controller\systemmanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\Universal;
use app\common\functions\Utils;

class User extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index(){
        $Utils = new Utils();
        $username = $Utils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username]);
    }
    
    /**
     * 用户管理列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_user_list() {
        $limitStart = $_POST['pSize'] *($_POST['cPage'] - 1);
        //如果没有传入企业查询条件,获取自身企业及子企业id
        if(empty($_POST['companyids'])){
            $Universal = new Universal();
            $Companyids = $Universal ->getSubsidiaryTreeByLevel("",1);
            $jointCompanyID = $Companyids[0]['jointcompanyid'];
        }else{
            $jointCompanyID = $_POST['companyids'];
        }
        //$where后面where查询筛选条件初始化
        $where = "a.companyid in ($jointCompanyID) and a.flag <> 3";
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
                    a.companyid,
                    gcom.companyname,
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
                INNER JOIN global_company gcom ON a.companyid = gcom.objectid
                INNER JOIN global_user b ON a.createmanid = b.objectid
                INNER JOIN global_user c ON a.modifymanid = c.objectid
                where $where ";
        $userList = Db::query($s1);
        $listSize = count($userList);
        $resData = array_slice($userList,$limitStart,$_POST['pSize']);//此处由于需要分页，所以不是返回所有结果
        //封装成gridManager需要的格式
        $result['data'] = $resData;
        $result['totals'] = $listSize;
        return json($result);
    }
    
    /**
     * 编辑或者增加用户
     */
    public function add_edit_user() {
        if(!empty($_POST)){
            $Utils = new Utils();
            $userid = $Utils->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_user = db('global_user');
            if($_POST['confirmPassword'] == ""){
                //为编辑模式
                try{
                    $global_user->where('objectid',$_POST['objectid']) 
                                ->update([  'linkphone'     => $_POST['linkphone'],
                                            'companyid'     => $_POST['companyid'], 
                                            'email'         => $_POST['email'],
                                            'password'      => $_POST['password'],
                                            'flag'          => 2,
                                            'modifymanid'   => $userid,
                                            'modifytime'    => date("Y-m-d H:i:s")]);
                }catch(\Exception $e){
                        abort(500, '编辑用户异常');
                }
                $this->redirect(url('/admin/systemmanage.user/index'));
            }else{
                //为新增模式
                $data = [   'username'      => $_POST['username'], 
                            'companyid'     => $_POST['companyid'], 
                            'password'      => $_POST['password'], 
                            'linkphone'     => $_POST['linkphone'], 
                            'email'         => $_POST['email'], 
                            'createmanid'   => $userid,
                            'createtime'    => date("Y-m-d H:i:s"),
                            'modifymanid'   => $userid,
                            'modifytime'    => date("Y-m-d H:i:s")
                        ];
                try{
                    $global_user-> insert($data);
                }catch(\Exception $e){
                        abort(500, '新增用户异常');
                }
                $this->redirect(url('/admin/systemmanage.user/index'));
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
        }
    }
        //            if($resObjectid){
    //                //根据返回的用户主键值，建立对应的数据表
    //                try{
    //                    $createDB1 = "SET FOREIGN_KEY_CHECKS=0;";
    //                    Db::execute($createDB1);   
    //                    $createDB2 = "DROP TABLE IF EXISTS `ms_data_history_"."$resObjectid`";
    //                    Db::execute($createDB2);   
    //                    $createDB3 = "CREATE TABLE `ms_data_history_".$resObjectid."` (
    //                                   `objectid` bigint(20) NOT NULL AUTO_INCREMENT,
    //                                    `uid` int(10) NOT NULL COMMENT '终端id标识',
    //                                    `mstype` int(5) NOT NULL COMMENT '终端类型',
    //                                    `rawdata` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '原始数据',
    //                                    `parseddata` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '解析后的数据',
    //                                    `uptime` datetime NOT NULL COMMENT '上报时间',
    //                                    PRIMARY KEY (`objectid`)
    //                                  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    //                    Db::execute($createDB3);
    //                }catch(\Exception $e){
    //                    abort(500, '新增用户时新建历史数据表异常');
    //                }
    //                $this->redirect(url('/admin/systemmanage.user/index'));
    //            }

    
    /**
     * 增加用户时校验用户名是否重复
     * @return int
     */
    public function check_user_duplicate() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $useInfo = $global_user ->where("username = '{$_POST["username"]}' and flag <> 3") ->find();
            //校验用户名,需要封装好的bootstrapValidator remote校验需要的验证格式，必须是json
            if($useInfo){
                $remoteJson["valid"] = false;
            }else{
                $remoteJson["valid"] = true;
            }
            return json($remoteJson);
        }
    }
    
    
    /**
     * 删除用户
     * @return int
     */
    public function delete_user() {
        if(!empty($_POST)){
            $global_user = db('global_user');
            $res  = $global_user->where('objectid',$_POST['objectid']) 
                                ->update([ 'flag' => 3]);
            if($res){
                return 1;
            }else{
                return 0;
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
        }
    }    
 
}
