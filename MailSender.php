<?php
require_once 'vendor/autoload.php';

// 引入composer依赖
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailSender
{
    /*
     * SMTP配置
     * */
    private $smtp_host = 'smtp.exmail.qq.com';//SMTP服务器地址
    private $smtp_from = '极客开发者-管理员';//发送者
    private $smtp_username = 'admin@jkdev.cn';//邮箱账号
    private $smtp_password = '';//邮箱密码
    private $smtp_port = '465';//端口号

    /**
     * 发送邮件对象
     * @param $addresses
     * @param $subject
     * @param $body
     * @return PHPMailer
     * @throws Exception
     */
    public function obtainEmailSender(array $addresses, $subject, $body)
    {
        $mailSender = new PHPMailer(true);
        $mailSender->CharSet = 'UTF-8';

        //Server settings
        $mailSender->SMTPDebug = SMTP::DEBUG_SERVER;                       // Enable verbose debug output
        $mailSender->isSMTP();                                             // Send using SMTP
        $mailSender->Host = $this->smtp_host;                              // Set the SMTP server to send through
        $mailSender->SMTPAuth = true;                                      // Enable SMTP authentication
        $mailSender->Username = $this->smtp_username;                      // SMTP username
        $mailSender->Password = $this->smtp_password;                      // SMTP password
        $mailSender->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;             // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mailSender->Port = $this->smtp_port;                              // TCP port to connect to

        //Recipients
        $mailSender->setFrom($this->smtp_username, $this->smtp_from);
        foreach ($addresses as $index => $address) {
            $mailSender->addAddress($address);                             // Name is optional
        }

        // Content
        $mailSender->isHTML(true);                                  // Set email format to HTML
        $mailSender->Subject = $subject;
        $mailSender->Body = $body;

        //返回邮件对象
        return $mailSender;
    }
}