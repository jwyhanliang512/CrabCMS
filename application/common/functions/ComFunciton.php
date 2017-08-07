<?php
namespace app\common\functions;

use think\Controller;
use think\Db;
use app\common\functions\UtilsFunction as UtilsFunc;

class ComFunciton extends controller
{
    
    /**
     * 判断是否处于已登录状态
     */
    public function checkUserSession() {
        $sessionName = session('crabstudio_session_username');
        if(empty($sessionName)){
            $this->redirect(url('/index/index/login'));
        }
    }
    
    
    /**
     * 根据企业levelcode下的所有子企业ID及自身ID
     * @param type $levelcode 传入的公司层级编码
     * @param type $type  1表示根据登录用户的当期企业层级编码获取，0表示直接根据传入的公司层级编码获取
     * @return type 返回 1,2,3 拼接好格式的企业id组合
     */
    public function getSubsidiaryTreeByLevel($levelcode,$type){
        if($type == 1){
            $commonUtils = new UtilsFunc();
            $levelcode = $commonUtils->authcode(session('crabstudio_session_user_companylevelcode'), "DECODE", config('authcodeKey'), 0);
        }
        $s1 =  "SELECT
                    GROUP_CONCAT(objectid) as jointcompanyid
		FROM
                    global_company
		WHERE
                    levelcode LIKE CONCAT('$levelcode','%')";
        try{
            $SubsidiaryTree = Db::query($s1);
        }catch(\Exception $e){
            abort(500, '获取传入企业levelcode下的所有子企业ID及自身ID异常');
        }
        return $SubsidiaryTree;
    }
    
    /**
     * 根据企业levelcode获取所有直系上级和所有下级企业ID
     * @param type $levelcode 传入的公司层级编码
     * @param type $type 1表示根据登录用户的当期企业层级编码获取，0表示直接根据传入的公司层级编码获取
     * @return type 返回 1,2,3 拼接好格式的企业id组合
     */
    public function getLinkCompanyidByLevel($levelcode,$type) {
         if($type == 1){
            $commonUtils = new UtilsFunc();
            $levelcode = $commonUtils->authcode(session('crabstudio_session_user_companylevelcode'), "DECODE", config('authcodeKey'), 0);
        }
        $levelCodeParents = "";
        for ($i = 5; $i < strlen($levelcode)+1; $i+=5) {
                $levelCodeParents .= substr($levelcode, 0, $i);
                $levelCodeParents .= ",";
        }
        return substr($levelCodeParents,0,strlen($levelCodeParents)-1);
    }
    

}