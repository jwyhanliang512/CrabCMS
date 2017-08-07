<?php

namespace app\admin\controller\devicemanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\UtilsFunction as UtilsFunc;
use app\common\functions\ComFunciton as ComFunc;

class Ms extends Index
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
    
    /**
     * 获取终端列表
     * @return type
     */
    public function get_ms_list() {
        //如果没有传入企业查询条件,获取自身企业及子企业id
        if(empty($_POST['mscompanyid'])){
            $commonFun = new ComFunc();
            $Companyids = $commonFun ->getSubsidiaryTreeByLevel("",1);
            $jointCompanyID = $Companyids[0]['jointcompanyid'];
        }else{
            $jointCompanyID = $_POST['mscompanyid'];
        }
        //$where后面where查询筛选条件初始化
        $where = "gms.companyid in ($jointCompanyID) ";
        //uid，别名，地址 查询条件
        if(!empty($_POST['msuid'])){
            $where = $where." and LPAD(hex(gms.uid), 8, 0) like '%{$_POST['msuid']}%'";
        };
        if(!empty($_POST['msalias'])){
            $where = $where." and gms.alias like '%{$_POST['msalias']}%' ";
        };
        if(!empty($_POST['msaddr'])){
            $where = $where." and gms.addr like '%{$_POST['msaddr']}%' ";
        };
        if(!empty($_POST['mstype'])){
            $where = $where." and gms.mstype in ({$_POST['mstype']})";
        };
        $s2 = " SELECT 
                    gms.uid as msuid,
                    gms.companyid,
                    gcom.companyname as companyname,
                    gtype.objectid  as mstypeid,
                    gtype.typename  as mstypename,
                    ifnull(gms.alias, '') as msalias,
                    ifnull(gms.addr, '') as msaddr,
                    gms.modifytime,
                    gms.modifymanid,
                    guser1.username as modifyman,
                    gms.createtime,
                    gms.createmanid,
                    guser2.username as createman
                FROM 
                    global_ms gms
                INNER JOIN global_company gcom ON gms.companyid = gcom.objectid
                INNER JOIN global_user guser1 ON gms.modifymanid = guser1.objectid
                INNER JOIN global_user guser2 ON gms.createmanid = guser2.objectid
                INNER JOIN global_typecode gtype ON gms.mstype = gtype.objectid
                where $where ";
        $msList = Db::query($s2);
        $listSize = count($msList);
        for($i=0;$i<$listSize;$i++){
            //uid十进制转十六进制
            $msList[$i]['hexmsuid'] = strtoupper(str_pad(dechex($msList[$i]['msuid']),8,'0',STR_PAD_LEFT));
        }
        //封装成gridManager需要的格式
        $result['data'] = $msList;
        $result['totals'] = $listSize;
        return json($result);
    }
    
    /**
     * 编辑或者增加用户
     */
    public function add_edit_ms() {
        if(!empty($_POST)){
            $commonUtils = new UtilsFunc();
            $userid = $commonUtils->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_ms = db('global_ms');
            $dexUid = hexdec($_POST["msuid"]);
            if(strlen($_POST['flag']) > 0){
                //为编辑模式
                try{
                    $global_ms  ->where('uid',$dexUid) 
                                ->update([  'mstype'        => $_POST['typecodeid'],
                                            'companyid'     => $_POST['companyid'], 
                                            'uid'           => $dexUid,
                                            'alias'         => $_POST['alias'],
                                            'addr'          => $_POST['addr'],
                                            'modifymanid'   => $userid,
                                            'modifytime'    => date("Y-m-d H:i:s")]);
                }catch(\Exception $e){
                        abort(500, '编辑终端异常');
                }
                $this->redirect(url('/admin/devicemanage.ms/index'));
            }else{
                //为新增模式
                $data = [   'mstype'        => $_POST['typecodeid'],
                            'companyid'     => $_POST['companyid'], 
                            'uid'           => $dexUid,
                            'alias'         => $_POST['alias'],
                            'addr'          => $_POST['addr'],
                            'modifymanid'   => $userid,
                            'modifytime'    => date("Y-m-d H:i:s"),
                            'createmanid'   => $userid,
                            'createtime'    => date("Y-m-d H:i:s")
                        ];
                try{
                    $global_ms -> insert($data);
                }catch(\Exception $e){
                        abort(500, '新增终端异常');
                }
                $this->redirect(url('/admin/devicemanage.ms/index'));
            }
        }else{
            $this->redirect(url('/admin/devicemanage.ms/index'));
        }
    }
    
    /**
     * 增加终端时校验是否重复
     * @return int
     */
    public function check_ms_duplicate() {
        if(!empty($_POST)){
            $global_ms = db('global_ms');
            $dexUid = hexdec($_POST["msuid"]);
            $useInfo = $global_ms ->where("uid = '$dexUid' ") ->find();
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
    public function delete_ms() {
        if(!empty($_POST)){
            $global_ms = db('global_ms');
            $res  = $global_ms->where('uid',$_POST['msuid']) ->delete();
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
