<?php

namespace app\admin\controller\datamanage;

use think\Db;
use app\admin\controller\Index;
use app\common\functions\ComFunciton as ComFunc;
use app\common\functions\UtilsFunction as UtilsFunc;

class Typecode extends Index
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
     * 类型列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_typecode_list() {
        //如果没有传入企业查询条件,获取自身企业及子企业id
        if(empty($_POST['companyids'])){
            $commonFun = new ComFunc();
            $Companyids = $commonFun ->getSubsidiaryTreeByLevel("",1);
            $jointCompanyID = $Companyids[0]['jointcompanyid'];
        }else{
            $jointCompanyID = $_POST['companyids'];
        }
        //$where后面where查询筛选条件初始化
        $where = "gtype.companyid in ($jointCompanyID) and gtype.flag <> 3";
        if(!empty($_POST['typename'])){
            $where = $where." and gtype.typename like '%{$_POST['typename']}%' ";
        };
        if(!empty($_POST['devicetype'])){
            $where = $where." and gtype.devicetype = {$_POST['devicetype']} ";
        };
        //sql语句
        $s1 = " SELECT 
                    gtype.objectid,
                    gtype.typename,
                    gtype.devicetype as devicetypeid,
                    gtype.companyid,
                    gcom.companyname,
                    gtype.createtime,
                    gtype.createmanid,
                    guser1.username as createman,
                    gtype.modifytime,
                    gtype.modifymanid,
                    guser2.username as modifyman 
                FROM 
                    global_typecode gtype
                INNER JOIN global_company gcom ON gtype.companyid = gcom.objectid
                INNER JOIN global_user guser1 ON gtype.createmanid = guser1.objectid
                INNER JOIN global_user guser2 ON gtype.modifymanid = guser2.objectid
                where $where
                order by gcom.levelcode";
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
     * 编辑或者增加typecode
     */
    public function add_edit_typecode() {
        if(!empty($_POST)){
            $commonUtils = new UtilsFunc();
            $userid = $commonUtils->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_typecode = db('global_typecode');
            if(strlen($_POST['objectid']) > 0){
                //为编辑模式
                try{
                    $global_typecode->where('objectid',$_POST['objectid']) 
                                    ->update([  
                                                'companyid'     => $_POST['companyid'], 
                                                'typename'      => $_POST['typename'],
                                                'devicetype'    => $_POST['devicetype'],
                                                'flag'          => 2,
                                                'modifymanid'   => $userid,
                                                'modifytime'    => date("Y-m-d H:i:s")]);
                }catch(\Exception $e){
                        abort(500, '编辑typecode异常');
                }
                $this->redirect(url('/admin/datamanage.typecode/index'));
            }else{
                //为新增模式
                $data = [   
                            'companyid'     => $_POST['companyid'], 
                            'typename'      => $_POST['typename'],
                            'devicetype'    => $_POST['devicetype'],
                            'createmanid'   => $userid,
                            'createtime'    => date("Y-m-d H:i:s"),
                            'modifymanid'   => $userid,
                            'modifytime'    => date("Y-m-d H:i:s")
                        ];
                try{
                    $global_typecode-> insert($data);
                }catch(\Exception $e){
                        abort(500, '新增typecode异常');
                }
                $this->redirect(url('/admin/datamanage.typecode/index'));
            }
        }else{
            $this->redirect(url('/admin/systemmanage.user/index'));
        }
    }

    
    /**
     * 增加类型时校验typecode是否重复
     * @return int
     */
    public function check_typecode_duplicate() {
        if(!empty($_POST)){
            $global_typecode = db('global_typecode');
            $info = $global_typecode ->where("typename = '{$_POST["typename"]}' and devicetype = '{$_POST["devicetype"]}' and companyid = '{$_POST["companyid"]}' and flag <> 3 ") ->find();
            //校验用户名,需要封装好的bootstrapValidator remote校验需要的验证格式，必须是json
            if($info){
                $remoteJson["valid"] = false;
            }else{
                $remoteJson["valid"] = true;
            }
            return json($remoteJson);
        }
    }
    
    
    /**
     * 删除类型
     * @return int
     */
    public function delete_typecode() {
        if(!empty($_POST)){
            $global_typecode = db('global_typecode');
            $res  = $global_typecode->where('objectid',$_POST['objectid']) 
                                ->update([ 'flag' => 3]);
            if($res){
                return 1;
            }else{
                return 0;
            }
        }else{
            $this->redirect(url('/admin/datamanage.typecode/index'));
        }
    }    
    
    
    public function set_typecode_tree() {
        $commonUtils = new UtilsFunc();
        $sesson_levelcode = $commonUtils->authcode(session('crabstudio_session_user_companylevelcode'), "DECODE", config('authcodeKey'), 0);
        $commonFun = new ComFunc();
        $levelCodeParents = $commonFun ->getLinkCompanyidByLevel($sesson_levelcode,0);
        //sql语句，直系上级和所有下级企业ID
        $s1 = " SELECT
			GROUP_CONCAT(objectid) as companyids
		FROM
			global_company a
		WHERE find_in_set(a.levelcode,'{$levelCodeParents}')
			 or a.levelcode like CONCAT( '$sesson_levelcode','%')";
        $companyidList = Db::query($s1);
        //获取类型
        $s2 = " SELECT
                    objectid as id,
                    typename as name
		FROM
                    global_typecode
		WHERE 
                    companyid in ({$companyidList[0]['companyids']}) ";
        $typecodeList = Db::query($s2);
        return json($typecodeList);
    }
    
}
