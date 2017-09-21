<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

use app\common\functions\AnalysisData as AnalysisData; 

class UpdataParse extends Controller
{    
    /**
     * 解析完整的数据域数据
     * @param type $wholeData   基站Mac[4]+基站时间[4]+上行数据结构
     */
    public function parseWholeData($wholeData) {
        $type = substr($wholeData, 6, 2);
        switch ($type) {
            case "01"://上行数据
                $this->parseCommonUpPackage($wholeData);
                break;
            case "02"://数据应答
                break;
            case "03"://获取网络时间
                break;
            case "04"://网关心跳包
                $this->gatewayHeartbeat($wholeData);
                break;
            case "05"://网关注册
                $this->gatewayReg($wholeData);
                break;
            default:
                break;
        }        
    }
    
    /**
     * 网关心跳包数据
     * @param type $wholeData
     */
    public function gatewayHeartbeat($wholeData) {
        $decApuid =  hexdec(substr($wholeData, 8, 8));
        $heartaptime =date('Y-m-d H:i:s',  hexdec(substr($wholeData, 16, 8)));
        $hearttime  = date("Y-m-d H:i:s",  time());   
        //插入网关心跳历史表
        $gateway_heartbeat = db('gateway_heartbeat');
        $sqlData=[ 
                    'apuid'   => "{$decApuid}", 
                    'aptime'  => $heartaptime,
                    'uptime'  => $hearttime
                ];
        try {
                $res = $gateway_heartbeat->insert($sqlData); 
            }catch(\Exception $e){
                abort(500, '网关心跳数据插入异常');
            }
        //更新最新信息表
        $updateSql ="INSERT INTO 
                            latest_ap_info (uid, heartaptime, hearttime) 
                    VALUE
                            ('{$decApuid}','$heartaptime','$hearttime')
                    ON DUPLICATE KEY UPDATE 
                            uid='{$decApuid}', heartaptime='{$heartaptime}', hearttime='$hearttime'";
        try {
                Db::execute($updateSql);
            }catch(\Exception $e){
                abort(500, '基站更新最新表心跳数据异常');
            } 
    }
    
    /**
     * 网关注册帧
     * @param type $wholeData
     */
    public function gatewayReg($wholeData) {
        $decApuid =  hexdec(substr($wholeData, 8, 8));
        $version  = hexdec(substr($wholeData, 16, 2));
        $regtime   = date("Y-m-d H:i:s",  time());   
        //插入网关心跳历史表
        $gateway_link = db('gateway_link');
        $sqlData=[ 
                    'apuid'     => "{$decApuid}", 
                    'version'   => "{$version}",
                    'uptime'    => $regtime
                ];
        try {
                $res = $gateway_link->insert($sqlData); 
            }catch(\Exception $e){
                abort(500, '网关注册数据插入异常');
            }
        //更新最新信息表
        $updateSql ="INSERT INTO 
                            latest_ap_info (uid, version, regtime) 
                    VALUE
                            ('{$decApuid}','$version','$regtime')
                    ON DUPLICATE KEY UPDATE 
                            uid='{$decApuid}', version='{$version}', regtime='$regtime'";
        try {
                Db::execute($updateSql);
            }catch(\Exception $e){
                abort(500, '基站更新最新表注册数据异常');
            } 
    }
    
    /**
     * 普通上行数据包解析
     * @param type $wholeData
     */
    public function parseCommonUpPackage($wholeData) {
        
        $decApuid = hexdec(substr($wholeData, 10, 8));
        $aptime =date('Y-m-d H:i:s',hexdec(substr($wholeData, 18, 8)));
        
        $customData = substr($wholeData, 26);
        $decUid  = hexdec(substr($customData, 2, 8));
        $uptime  = date("Y-m-d H:i:s",  time());   
        
        switch (substr($customData, 0, 2)) {
            case "01"://终端上行数据
                $rawdata   = substr($customData, 10);
                $global_ms = db('global_ms');
                $msInfo    = $global_ms -> where('uid', $decUid)->find();
                //如果终端表里没有这个mac，则不作任何处理
                if(!empty($msInfo)){
                    $AnalysisData = new AnalysisData();
                    $parsedData = $AnalysisData->parseMsUpdata($msInfo['mstype'], $rawdata);
                    $ms_data_history = db('ms_data_history_'.$msInfo['companyid']);//ms历史数据表为【ms_data_history_】加【企业id】
                    $sqlData=[ 
                                'uid'       => "{$decUid}", 
                                'mstype'    => "{$msInfo['mstype']}", 
                                'rawdata'   => "{$rawdata}",
                                'parseddata'=> "{$parsedData}",
                                'aptime'    => $aptime,
                                'uptime'    => $uptime 
                            ];
                    try{
                        $res  = $ms_data_history->insert($sqlData); 
                    }catch(\Exception $e){
                        abort(500, '终端上行数据插入异常');
                    }
                    //更新最新信息表
                    $updateSql ="INSERT INTO 
                                        latest_ms_info (uid,rawdata,parseddata,uploadaptime,uploadtime) 
                                VALUE
                                        ('{$decUid}','{$rawdata}','{$parsedData}','{$aptime}','{$uptime}')
                                ON DUPLICATE KEY UPDATE 
                                        uid='{$decUid}', rawdata='{$rawdata}', parseddata='{$parsedData}', uploadaptime='{$aptime}', uploadtime='{$uptime}'";
                    try {
                            Db::execute($updateSql);
                        }catch(\Exception $e){
                            abort(500, '终端更新最新表上行数据异常');
                        }
                }
                break;
            case "02"://终端注册数据
                $decReguid = hexdec(substr($customData, 10, 8));
                $version   = hexdec(substr($customData, -2));
                $regnum    = hexdec(substr($customData, 18, 2));
                $regreason = hexdec(substr($customData, 20, 2));
                //插入注册历史表
                $ms_link = db('ms_link');
                $sqlData=[ 
                            'uid'       => "{$decUid}", 
                            'apuid'     => "{$decApuid}", 
                            'reguid'    => "{$decReguid}",
                            'version'   => "{$version}",
                            'regnum'    => "{$regnum}",
                            'regreason' => "{$regreason}",
                            'aptime'    => $aptime,
                            'uptime'    => $uptime
                        ];
                try {
                        $res = $ms_link->insert($sqlData); 
                    }catch(\Exception $e){
                        abort(500, '终端注册数据插入异常');
                    }
                //更新最新信息表
                $updateSql ="INSERT INTO 
                                    latest_ms_info (uid,apuid,reguid,version,regnum,regreason,regaptime,regtime,regstatus) 
                            VALUE
                                    ('{$decUid}','{$decApuid}','{$decReguid}','{$version}','{$regnum}','{$regreason}','$aptime','$uptime','1')
                            ON DUPLICATE KEY UPDATE 
                                    uid='{$decUid}', apuid='{$decApuid}', reguid='{$decReguid}', version='{$version}', regnum='{$regnum}', regreason='{$regreason}', regaptime='$aptime', regtime='$uptime', regstatus= '1'";
                try {
                        Db::execute($updateSql);
                    }catch(\Exception $e){
                        abort(500, '终端更新最新表注册数据异常');
                    }
                break;
            case "03"://终端心跳数据
                $decReguid  = hexdec(substr($customData, 10, 8));
                $ms_heartbeat = db('ms_heartbeat');
                $battery  = (hexdec(substr($customData, 10, 2)) + 150)/100;
                $uprssi   = hexdec(substr($customData, 12, 2));
                $downrssi = hexdec(substr($customData, 14, 2));
                //插入心跳历史表
                $sqlData=[ 
                            'uid'       => "{$decUid}", 
                            'apuid'     => "{$decApuid}", 
                            'battery'   => "{$battery}",
                            'uprssi'    => "{$uprssi}",
                            'downrssi'  => "{$downrssi}",
                            'aptime'    => $aptime,
                            'uptime'    => $uptime
                        ];
                try {
                        $res = $ms_heartbeat->insert($sqlData); 
                    }catch(\Exception $e){
                        abort(500, '终端心跳数据插入异常');
                    }
                //更新最新信息表
                $updateSql ="INSERT INTO 
                                    latest_ms_info (uid,apuid,battery,uprssi,downrssi,heartaptime,hearttime) 
                            VALUE
                                    ('{$decUid}','{$decApuid}','{$battery}','{$uprssi}','{$downrssi}','$aptime','$uptime')
                            ON DUPLICATE KEY UPDATE 
                                    uid='{$decUid}', apuid='{$decApuid}', battery='{$battery}', uprssi='{$uprssi}', downrssi='{$downrssi}', heartaptime='{$aptime}', hearttime='$uptime'";
                try {
                        Db::execute($updateSql);
                    }catch(\Exception $e){
                        abort(500, '终端更新最新表心跳数据异常');
                    } 
                break;
            default:
                break;
        }
    }

}
