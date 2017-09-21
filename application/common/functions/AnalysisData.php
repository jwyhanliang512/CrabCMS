<?php

namespace app\common\functions;

use think\Controller;

class AnalysisData extends Controller
{    
    
    /**
     * 解析终端数据
     * @param type $mstype 终端类型
     * @param type $data   原始数据
     * @return string
     */
    public function parseMsUpdata($mstype,$data) {
        $parsedData = $data;
        switch ($mstype) {
            case '1':
                break;
            case '2'://FFFFFFFF0A0F,模拟的温度数据,0Axx，xx代表十六进制的温度
                switch (strtoupper(substr($data, 0, 2))) {
                    case '0A':
                        $tmp = hexdec(substr($data,2));
                        $parsedData = "温度为：".$tmp."℃"; 
                        break;
                    default:
                        break;
                }
            case '3'://FFFFFFFF0A0F,模拟的pm2.5数据,0Axx，xx代表浓度
                switch (strtoupper(substr($data, 0, 2))) {
                    case '0A':
                        $tmp = hexdec(substr($data,2));
                        $parsedData = "浓度为：".$tmp."ug/m³"; 
                        break;
                    default:

                        break;
                }
            default:
                break;
        }
        return $parsedData;    
    }

}
