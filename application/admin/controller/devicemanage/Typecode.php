<?php

namespace app\admin\controller\devicemanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;

class Typecode extends Index
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
    
    /**
     * 类型列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_typecode_list() {
        //$where后面where查询筛选条件初始化
        $where = " 1=1 ";
        if(!empty($_POST['typename'])){
            $where = $where." and gtype.typename like '%{$_POST['typename']}%' ";
        };
        //sql语句
        $s1 = " SELECT 
                    gtype.objectid,
                    gtype.typeid,
                    gtype.typename,
                    gtype.devicetype as devicetypeid,
                    gtype.userid,
                    ifnull(guser3.username,'') as username,
                    gtype.createtime,
                    gtype.createmanid,
                    guser1.username as createman,
                    gtype.modifytime,
                    gtype.modifymanid,
                    guser2.username as modifyman 
                FROM 
                    global_typecode gtype
                INNER JOIN global_user guser1 ON gtype.createmanid = guser1.objectid
                INNER JOIN global_user guser2 ON gtype.modifymanid = guser2.objectid
                INNER JOIN global_user guser3 ON gtype.userid = guser3.objectid
                where $where ";
        $typeList = Db::query($s1);
        $size = count($typeList);
        for($i=0; $i<$size; $i++){
            switch ($typeList[$i]['devicetypeid']) {
                case 1:
                    $typeList[$i]['devicetypename'] = "终端";
                    break;
                case 2:
                    $typeList[$i]['devicetypename'] = "中继";
                    break;
                case 3:
                    $typeList[$i]['devicetypename'] = "基站";
                    break;
                default:
                    break;
            }
        }
        //封装成gridManager需要的格式
        $result['data'] = $typeList;
        $result['totals'] = $size;
        return json($result);
    }
    
     /**
     * 编辑类型信息
     */
    public function edit_typecode() {
        if(!empty($_POST)){
            $common = new ComFunc();
            $userid = $common->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_typecode = db('global_typecode');
            $res = $global_typecode ->where('objectid',$_POST['objectid']) 
                                    ->update([  'typename'     => $_POST['typename'],
                                                'devicetype'   => $_POST['devicetype'],
                                                'modifymanid'   => $userid,
                                                'modifytime'    => date("Y-m-d H:i:s")]);
            if($res){
                $this->redirect(url('/admin/devicemanage.typecode/index'));
            }
        }else{
            $this->redirect(url('/admin/devicemanage.typecode/index'));
        }
    }
 
    /**
     * 增加类型
     */
    public function add_typecode() {
        if(!empty($_POST)){
//            $common = new ComFunc();
//            $userid = $common->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
//            $global_typecode = db('global_typecode');
//            $data = [   'username'      => $_POST['username'], 
//                        'password'      => $_POST['password'], 
//                        'linkphone'     => $_POST['linkphone'], 
//                        'email'         => $_POST['email'], 
//                        'createmanid'   => $userid,
//                        'createtime'    => date("Y-m-d H:i:s"),
//                        'modifymanid'   => $userid,
//                        'modifytime'    => date("Y-m-d H:i:s")
//                    ];
//            $res  = $global_user-> insert($data);
            if($res){
                $this->redirect(url('/admin/devicemanage.typecode/index'));
            }
        }else{
            $this->redirect(url('/admin/devicemanage.typecode/index'));
        }
    }
}
