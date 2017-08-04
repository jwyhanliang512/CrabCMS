<?php

namespace app\admin\controller\devicemanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;

class Ms extends Index
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
     * 获取终端列表
     * @return type
     */
    public function get_ms_list() {
        //$where后面where查询筛选条件初始化
        $where = " 1=1 ";
        if(!empty($_POST['msuid'])){
            $where = $where." and LPAD(hex(gms.uid), 8, 0) like '%{$_POST['msuid']}%'";
        };
        if(!empty($_POST['msalias'])){
            $where = $where." and gms.alias like '%{$_POST['msalias']}%' ";
        };
        if(!empty($_POST['msusername'])){
            $where = $where." and guser1.username like '%{$_POST['msusername']}%' ";
        };
        if(!empty($_POST['msaddr'])){
            $where = $where." and gms.addr like '%{$_POST['msaddr']}%' ";
        };
        //sql语句
        $s1 = " SELECT 
                    gms.uid as msuid,
                    gms.userid as msuerid,
                    guser1.username as msusername,
                    ifnull(gms.alias, '') as msalias,
                    ifnull(gms.addr, '') as msaddr,
                    gms.modifytime,
                    gms.modifymanid,
                    guser2.username as modifyman,
                    gtype.typename  as mstypename,
                    gtype.typeid    as mstypeid
                FROM 
                    global_ms gms
                INNER JOIN global_user guser1 ON gms.userid = guser1.objectid
                INNER JOIN global_user guser2 ON gms.modifymanid = guser2.objectid
                INNER JOIN global_typecode gtype ON gms.mstype = gtype.typeid and devicetype = 1
                where $where ";
        $msList = Db::query($s1);
        $listSize = count($msList);
        for($i=0;$i<$listSize;$i++){
            //uid十进制转十六进制
            $msList[$i]['msuid'] = strtoupper(str_pad(dechex($msList[$i]['msuid']),8,'0',STR_PAD_LEFT));
        }
        //封装成gridManager需要的格式
        $result['data'] = $msList;
        $result['totals'] = $listSize;
        return json($result);
    }
    
 
}
