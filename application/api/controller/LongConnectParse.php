<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

use GatewayClient\Gateway;
use app\api\controller\UpdataParse;

class LongConnectParse extends Controller
{
    
    
    /**
     * 拆解数据
     */
    public function splitData() {
        if(!empty($_POST)){
            
            $Gateway = new Gateway();
            Gateway::$registerAddress = '127.0.0.1:1238';
            
            //转成数组,防止出现粘包
            $packageArr = array();
            $packageArr = $this->handlePackage($_POST['updata'],$packageArr);
            
            //上行数据反馈处理
            $i = 0;
            foreach ($packageArr as $key => $value) {
                if(substr($packageArr[$i], 6, 2) == "04"){
                    //网关心跳包
                    Gateway::sendToClient($_POST['clientid'], "AA55020200");
                }else if(substr($packageArr[$i], 6, 2) == "05"){
                    //网关注册
                    Gateway::sendToClient($_POST['clientid'], "AA55020200");
                    //client_id和apuid映射关系绑定
                    $this->bindClientAndApuid($_POST['clientid'], hexdec(substr($packageArr[$i], 8, 8)));
                }else if(substr($packageArr[$i], 6, 2) == "03"){
                    //获取网络时间
                    $networktime  = dechex(time());  //四字节网络时间，肯定满4字节 
                    Gateway::sendToClient($_POST['clientid'], "AA550504".$networktime);
                }else{
                    //普通数据帧
                    Gateway::sendToClient($_POST['clientid'], "AA55020200".substr($packageArr[$i], 8, 2));
                }
                $i++;
            }
            
            //上行数据的处理，和上面的反馈要分开，防止处理报错，反馈受到影响, 所以用两个foreach循环
            $parseData = new UpdataParse();
            $k=0;
            foreach ($packageArr as $key => $value) {
                $parseData -> parseWholeData($packageArr[$k]); 
                $k++;
            }
        }
    }
    
    /**
     * 对数据包进行处理
     * @param type $data  数据包
     * @param type $packageArr  把数据包拆分开，返回的数组
     * @return 返回完整的数据包数组
     */
    public function handlePackage($data,$packageArr) {
        //获取到的数据，一定是完整的数据包，只是可能多个粘在了一起
        $upperData = strtoupper($data);
        //获取帧头位置
        $headerPos = strpos($upperData, "AA55");
        //根据帧头位置，获取到数据len字节
        $len = hexdec(substr($upperData, $headerPos + 4, 2));
        //理论上的包长度 = 4(帧头) + 2(Len) + 2(类型符) + Len*2(数据)
        $packageLength = 6 + $len * 2;
        //根据帧头位置及数据包长度，获取数据包
        $package = substr($upperData, $headerPos,$packageLength);
        //填进数组
        array_push($packageArr, $package);
        //去除一个数据包，得到剩余数据, 注意，这里必须是 $headerPos + $packageLength ，因为前面可能有冗余数据
        $rest = substr($upperData, $headerPos + $packageLength);
        //判断有无数据剩下，如果有，那就是粘包了
        if($rest){
            return $this->handlePackage($rest, $packageArr);
        }else{
            return $packageArr;
        }
    }
    
    /**
     * workerman client_id与网关mac绑定
     * @param type $client_id
     * @param type $apuid
     */
    public function bindClientAndApuid($client_id,$apuid) {
        //workerman client_id与网关mac绑定
        $updatetime = date("Y-m-d H:i:s",  time());
        $mappingSql = "INSERT INTO 
                            client_ap_mapping (apuid, clienttid,updatetime) 
                    VALUE
                            ('{$apuid}','$client_id', '$updatetime')
                    ON DUPLICATE KEY UPDATE 
                            apuid='{$apuid}', clienttid='{$client_id}', updatetime= '$updatetime'";
        try {
            Db::execute($mappingSql);
        }catch(\Exception $e){
            abort(500, 'client_id与网关mac绑定异常');
        }
    }
    
    /**
     * 解绑client_id与网关mac
     */
    public function unbindClientAndApuid() {
        //workerman client_id与网关mac解绑
        $mappingSql =   "DELETE FROM  
                            client_ap_mapping 
                        WHERE
                            clienttid='{$_POST['clientid']}'";
        try {
            Db::execute($mappingSql);
        }catch(\Exception $e){
            abort(500, 'client_id与网关解绑异常');
        }
    }

}
