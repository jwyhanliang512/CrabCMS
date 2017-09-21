<?php

namespace app\elsonic\controller\datamanage;

use think\Db;

use app\elsonic\controller\Index;
use app\common\functions\Utils;

class ElsonicTypecode extends Index
{
    
    /**
     * 初始化主界面
     * @return type
     */
    public function index() {
        $Utils = new Utils();
        $username = $Utils->authcode(session('crabstudio_session_username'), "DECODE", config('authcodeKey'), 0);
        return $this->fetch('index',[ 'username'  => $username ]);
    }
    
    /**
     * 类型列表信息获取
     * @return 按照gridManager格式要求封装好的json数据
     */
    public function get_typecode_list() {
        $limitStart = $_POST['pSize'] *($_POST['cPage'] - 1);
        //$where后面where查询筛选条件初始化
        $where = "gtype.flag <> 3";
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
                where $where";
        $typeList = Db::query($s1);
        $listSize = count($typeList);
        $resData = array_slice($typeList,$limitStart,$_POST['pSize']);//此处由于需要分页，所以不是返回所有结果
        $i = 0;
        foreach ($resData as $key => $value) {
            switch ($resData[$i]['devicetypeid']) {
                case 1:
                    $resData[$i]['devicetypename'] = "终端";
                    break;
                case 2:
                    $resData[$i]['devicetypename'] = "中继";
                    break;
                case 3:
                    $resData[$i]['devicetypename'] = "基站";
                    break;
                default:
                    break;
            }
            $i++;
        }
        //封装成gridManager需要的格式
        $result['data'] = $resData;
        $result['totals'] = $listSize;
        return json($result);
    }
    
    /**
     * 编辑或者增加typecode
     */
    public function add_edit_typecode() {
        if(!empty($_POST)){
            $Utils = new Utils();
            $userid = $Utils->authcode(session('crabstudio_session_userid'), "DECODE", config('authcodeKey'), 0);
            $global_typecode = db('global_typecode');
            if(strlen($_POST['objectid']) > 0){
                //为编辑模式
                try{
                    $global_typecode->where('objectid',$_POST['objectid']) 
                                    ->update([   
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
            $info = $global_typecode ->where("typename = '{$_POST["typename"]}' and devicetype = '{$_POST["devicetype"]}' and flag <> 3 ") ->find();
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
    
    /**
     * 生成终端类型下拉框
     * @return type
     */
    public function set_typecode_tree() {
        //获取类型
        $s2 = " SELECT
                    objectid as id,
                    typename as name
		FROM
                    global_typecode
		WHERE 
                    flag <> 3 ";
        $typecodeList = Db::query($s2);
        return json($typecodeList);
    }
    
}
