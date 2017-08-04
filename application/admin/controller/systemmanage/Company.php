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
     * 企业列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_company_list() {
        //首先获取当前用户所属的企业层级
        $common = new ComFunc();
        $sesson_levelcode = $common->authcode(session('crabstudio_session_user_companylevelcode'), "DECODE", config('authcodeKey'), 0);
        //$where后面where查询筛选条件初始化
        $where = "gcom.flag <> 3 and gcom.levelcode like concat( '$sesson_levelcode','%')  ";
        if(!empty($_POST['companyname'])){
            $where = $where." and gcom.companyname like '%{$_POST['companyname']}%' ";
        };
        if(!empty($_POST['address'])){
            $where = $where." and gcom.address like '%{$_POST['address']}%' ";
        };
        //sql语句
        $s1 = " SELECT 
                    gcom.objectid,
                    gcom.companyname,
                    gcom.companycode,
                    gcom.parentid,
                    gcom2.companyname as parentcompany,
                    gcom.levelcode,
                    gcom.address,
                    gcom.linknumber,
                    gcom.linkman,
                    gcom.createtime,
                    gcom.createmanid,
                    guser1.username as createman,
                    gcom.modifytime,
                    gcom.modifymanid,
                    guser2.username as modifyman 
                FROM 
                    global_company gcom
                INNER JOIN global_company gcom2 ON gcom2.objectid = gcom.parentid
                INNER JOIN global_user guser1 ON gcom.createmanid = guser1.objectid
                INNER JOIN global_user guser2 ON gcom.modifymanid = guser2.objectid
                where $where
                order by gcom.levelcode";
        $userList = Db::query($s1);
        //封装成gridManager需要的格式
        $result['data'] = $userList;
        $result['totals'] = count($userList);
        return json($result);
    }
    
    /**
     * 编辑或者增加企业
     */
    public function add_edit_company() {
        if(!empty($_POST)){
            $common = new ComFunc();
            $userid = $common->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_company = db('global_company');
            if(strlen($_POST['objectid']) > 0){
                //为编辑模式
                try{
                    $global_company ->where('objectid',$_POST['objectid']) 
                                    ->update([  
                                                'address'       => $_POST['address'], 
                                                'linkman'       => $_POST['linkman'],
                                                'linknumber'    => $_POST['linknumber'],
                                                'flag'          => 2,
                                                'modifymanid'   => $userid,
                                                'modifytime'    => date("Y-m-d H:i:s")]);
                }catch(\Exception $e){
                        abort(500, '编辑企业异常');
                }
                $this->redirect(url('/admin/systemmanage.company/index'));
            }else{
                //为新增模式
                $highestlevel = $global_company -> where("levelcode like concat( '{$_POST['parentlevelcode']}','_____')")-> order('levelcode desc')->limit(1)->select(); //获取父企业下最近一级子企业的层级最高的企业层级值，这里包括已删除的企业
                if($highestlevel){
                    $rightlevel = substr($highestlevel[0]['levelcode'], -5);//获取最右侧5位编码
                    $rightlevel+=0;//去除前面的的0
                    $newLevelCount = $_POST['parentlevelcode'].str_pad($rightlevel+1,5,"0",STR_PAD_LEFT);
                }else{
                    $newLevelCount = $_POST['parentlevelcode']."00001";
                }
                $data = [   'companyname'   => $_POST['companyname'], 
                            'parentid'      => $_POST['parentid'], 
                            'levelcode'     => $newLevelCount, 
                            'address'       => $_POST['address'], 
                            'linkman'       => $_POST['linkman'],
                            'linknumber'    => $_POST['linknumber'],
                            'createmanid'   => $userid,
                            'createtime'    => date("Y-m-d H:i:s"),
                            'modifymanid'   => $userid,
                            'modifytime'    => date("Y-m-d H:i:s")
                        ];
                try{
                    $global_company-> insert($data);
                }catch(\Exception $e){
                        abort(500, '新增企业异常');
                }
                $this->redirect(url('/admin/systemmanage.company/index'));
            }
        }else{
            $this->redirect(url('/admin/systemmanage.company/index'));
        }
    }
    
    /**
     * 增加企业时校验企业名称是否重复
     * @return int
     */
    public function check_company_duplicate() {
        if(!empty($_POST)){
            $global_company = db('global_company');
            $Info = $global_company ->where("companyname = '{$_POST["companyname"]}' and flag <> 3") ->find();
            //校验用户名,需要封装好的bootstrapValidator remote校验需要的验证格式，必须是json
            if($Info){
                $remoteJson["valid"] = false;
            }else{
                $remoteJson["valid"] = true;
            }
            return json($remoteJson);
        }
    }
    
    
    /**
     * 删除企业
     * @return int
     */
    public function delete_company() {
        if(!empty($_POST)){
            $global_company = db('global_company');
            $res  = $global_company ->where('objectid',$_POST['objectid']) 
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
    
    /**
     * 生成企业树组织结构
     * @return type
     */
    public function set_company_tree() {
        $common = new ComFunc();
        $sesson_levelcode = $common->authcode(session('crabstudio_session_user_companylevelcode'), "DECODE", config('authcodeKey'), 0);
        //sql语句,需要排除objectid为-1的无效行
        $s1 = " SELECT 
                    objectid as id,
                    parentid as pId,
                    companyname as name,
                    levelcode as levelcode,
                    'true' as open
                FROM 
                    global_company
                WHERE 
                    objectid <> -1 and flag <> 3 and levelcode like concat( '$sesson_levelcode','%') ";
        $companyList = Db::query($s1);
        return json($companyList);
    }
    
 
}
