<?php
namespace app\common\functions;

use think\Controller;
use phpmailer\phpmailer as PHPMailer;

class UtilsFunction extends controller
{
    
    /*
     * 邮件发送
     * @param $toMail   接收人地址
     * @param $showname 收件人名称 Liang(yyyy@163.com) 中的 Liang
     * @param $title    邮件标题
     * @param $content  邮件内容
     */
    public function sendEmail($toMail, $showname, $title, $content){
        $mail = new PHPMailer();  
        $mail->isSMTP();// 使用SMTP服务 

        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码  
        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址  
        $mail->SMTPAuth = true;// 是否使用身份验证  
        $mail->Username = "CrabTeamStudio@163.com";// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱</span><span style="color:#333333;">  
        $mail->Password = "hqu123456";// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！</span><span style="color:#333333;">  
        $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">  
        $mail->Port = 994;// 163邮箱的ssl协议方式端口号是465/994  
        
        $mail->setFrom("CrabTeamStudio@163.com","Crab Studio");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示  
        $mail->addAddress($toMail,$showname);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)  
        $mail->addReplyTo("CrabTeamStudio@163.com","Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址  
        $mail->addCC("1463659386@qq.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)  
        //$mail->addBCC("CrabTeamStudio@163.com");// 设置秘密抄送人(这个人也能收到邮件),貌似无效  This function works with the SMTP mailer on win32, not with the "mail" mailer.
        //$mail->addAttachment("bug0.jpg");// 添加附件  

        $mail->Subject = $title;// 邮件标题  
        $mail->Body = $content;// 邮件正文  
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用  
        return($mail->send());
    }
    
    /**
     * 加解密方法
     * @param type $string 字符串，明文或密文
     * @param type $operation ：DECODE表示解密，其它表示加密
     * @param type $key 密匙
     * @param type $expiry 密文有效期
     * @return string
     */
    public function authcode($string, $operation, $key, $expiry) {   
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙   
        $ckey_length = 4;   
        // 密匙   
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);   
        // 密匙a会参与加解密   
        $keya = md5(substr($key, 0, 16));   
        // 密匙b会用来做数据完整性验证   
        $keyb = md5(substr($key, 16, 16));   
        // 密匙c用于变化生成的密文   
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';   
        // 参与运算的密匙   
        $cryptkey = $keya.md5($keya.$keyc);   
        $key_length = strlen($cryptkey);   
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)， 
        //解密时会通过这个密匙验证数据完整性   
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确   
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
        $string_length = strlen($string);   
        $result = '';   
        $box = range(0, 255);   
        $rndkey = array();   
        // 产生密匙簿   
        for($i = 0; $i <= 255; $i++) {   
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);   
        }   
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度   
        for($j = $i = 0; $i < 256; $i++) {   
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;   
            $tmp = $box[$i];   
            $box[$i] = $box[$j];   
            $box[$j] = $tmp;   
        }   
        // 核心加解密部分   
        for($a = $j = $i = 0; $i < $string_length; $i++) {   
            $a = ($a + 1) % 256;   
            $j = ($j + $box[$a]) % 256;   
            $tmp = $box[$a];   
            $box[$a] = $box[$j];   
            $box[$j] = $tmp;   
            // 从密匙簿得出密匙进行异或，再转成字符   
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
        }   
        if($operation == 'DECODE') {  
            // 验证数据有效性，请看未加密明文的格式   
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
                return substr($result, 26);   
            } else {   
                return '';   
            }   
        } else {   
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因   
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码   
            return $keyc.str_replace('=', '', base64_encode($result));   
        }   
    }

}