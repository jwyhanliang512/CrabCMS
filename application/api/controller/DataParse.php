<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

use app\common\functions\ComFunciton as ComFunc;

class DataParse extends Controller
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
     * 拆解数据
     */
    public function splitData() {
        if(!empty($_POST)){
            $uid  = substr($_POST, 0, 8);
            $data = substr($_POST, 9);
            $this->parseMsData($uid,$data,1);
        }
    }
    
    /**
     * 解析终端数据
     * @param type $uid 十六进制uid
     * @param type $data 原始数据
     * @param type $type 解析要求的类型：0表示不需要插入数据库，只解析返回解析数据； 1表示需要插入数据库，不需要返回解析数据
     */
    public function parseMsData($uid,$data,$type) {
        $global_ms = db('global_ms');
        $decUid = hexdec($uid);//十六进制uid转十进制
        $msInfo = $global_ms -> where('uid', $decUid)->find();
        if(!empty($msInfo)){
            //如果在glabal_ms表里查到终端
            $mstype = $msInfo['mstype'];//终端类型
            switch ($mstype) {
                case '0':
                    $parsedData = $data;
                    break;
                case  '1'://模拟的门禁数据,0Axxxx,xxxx为二字节的开门时长，例如0010，代表开门16秒
                    switch (substr($data, 0, 2)) {
                        case '0A':
                            $dectime = hexdec(substr($data,2));
                            $parsedData = "开门时长为".$dectime."秒"; 
                            break;
                        case '0B':
                            $parsedData = "门被关上"; 
                            break;
                        default:
                            break;
                    }
                default:
                    $parsedData = $data;
                    break;
            }
            //根据解析要求的类型,进行下一步处理
            if($type === 0){
                return $parsedData;
            }else{
                //插入数据库
                $ms_data_history = db('ms_data_history_'.$msInfo['userid']);//ms历史数据表为【ms_data_history_】加【用户objectid】
                $sqlData=[  'uid'       => $decUid, 
                            'mstype'    => $mstype, 
                            'rawdata'   => $data,
                            'parseddata'=> $parsedData,
                            'uptime'    => date("Y-m-d H:i:s")
                        ];
                try{
                    $res  = $ms_data_history->insert($sqlData); 
                }catch(\Exception $e){
                    abort(500, '终端历史数据插入异常');
                }
            }
        }else{
            //如果没有在glabal_ms表里查到终端
            //根据解析要求的类型,进行下一步处理
            if($type === 0){
                return $data;
            }else{
                //不作任何处理
                return;
            }
        }
        
    }

}
