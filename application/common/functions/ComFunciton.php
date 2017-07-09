<?php
namespace app\common\functions;

use think\Controller;
use phpmailer\phpmailer as PHPMailer;

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
    
    /*
     * 邮件发送
     * @param $toMail   接收人地址
     * @param $showname 收件人名称 Liang(yyyy@163.com) 中的 liang
     * @param $title    邮件标题
     * @param $content  邮件内容
     */
    public function sendEmail($toMail, $showname, $title, $content){
        $mail = new PHPMailer();  
        $mail->isSMTP();// 使用SMTP服务 

        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码  
        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址  
        $mail->SMTPAuth = true;// 是否使用身份验证  
        $mail->Username = "smartcityhqu@163.com";// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱</span><span style="color:#333333;">  
        $mail->Password = "owoevzkxbbaczuhs";// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！</span><span style="color:#333333;">  
        $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">  
        $mail->Port = 994;// 163邮箱的ssl协议方式端口号是465/994  
        
        $mail->setFrom("smartcityhqu@163.com","Crab Studio");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示  
        $mail->addAddress($toMail,$showname);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)  
        $mail->addReplyTo("smartcityhqu@163.com","Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址  
        $mail->addCC("CrabTeamStudio@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)  
        //$mail->addBCC("CrabTeamStudio@163.com");// 设置秘密抄送人(这个人也能收到邮件),貌似无效  This function works with the SMTP mailer on win32, not with the "mail" mailer.
        //$mail->addAttachment("bug0.jpg");// 添加附件  

        $mail->Subject = $title;// 邮件标题  
        $mail->Body = $content;// 邮件正文  
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用  
        return($mail->send());
    }
}