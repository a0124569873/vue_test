<?php

namespace app\index\service;

use \PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    /**
     * 邮件发送
     * @return null
     */
    public static function send($address, $token, $type)
    {
        $content = self::mailContent($type, $token);
        $title = self::mailTitle($type);
        $result = self::excute($address, $title, $content);
        return $result;
    }

    public static function mailTitle($type)
    {
        switch ($type) {
            case "forget":
                $mail_title = "密码找回";
                break;
            case "safemail":
                $mail_title = "安全邮箱验证";
                break;
            default:
                break;
        }
        return $mail_title;
    }

    public static function mailContent($type, $token="")
    {
        switch ($type) {
            case "forget":
                $url = request()->domain().'/password_reset'.'/'.$token;
                $mail_content = view('templet/forget_pwd_mail', ['reset_pwd_url'=>$url])->getContent();
                break;
            case "safemail":
                $mail_content = view('templet/safe_pwd_mail', ['safe_token'=>$token])->getContent();
                break;
            default:
                break;
        }
        return $mail_content;
    }

    public static function excute($to, $title, $content)
    {
        $mail = new PHPMailer; //实例化PHPMailer核心类
        $mail->SMTPDebug = 0; //是否启用smtp的debug进行调试
        $mail->isSMTP(); //使用smtp鉴权方式发送邮件
        $mail->SMTPAuth=true; //smtp需要鉴权 这个必须是true
        $mail->Host = 'smtp.exmail.qq.com';//链接qq域名邮箱的服务器地址
        $mail->SMTPOptions = array(
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                // 'verify_peer' => true,
                // 'verify_depth' => 3,
                // 'allow_self_signed' => true,
                // 'peer_name' => 'smtp.exmail.qq.com',
                // 'cafile' => '/web/ssl/cacert.pem',
            ],
        );
        $mail->SMTPSecure = 'ssl'; //设置使用ssl加密方式登录鉴权
        $mail->Port = 465; //设置ssl连接smtp服务器的远程服务器端口号
        // $mail->Helo = 'Hello smtp.qq.com Server'; //设置smtp的helo消息头 这个可有可无 内容任意
        $mail->Hostname = 'http://www.veda.com'; //设置发件人的主机域 默认为localhost 内容任意
        $mail->CharSet = 'UTF-8'; //设置发送的邮件的编码 可选GB2312
        $mail->FromName = '卫达安全'; //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->Username ='shaoyuansy@veda.com'; //smtp登录的账号
        $mail->Password = 'DSwPqNkCwbuSYwg6'; //smtp登录的密码 使用生成的授权码
        $mail->From = 'shaoyuansy@veda.com'; //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        $mail->isHTML(true); //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->addAddress($to, 'veda在线通知'); //设置收件人邮箱地址 第一个参数为收件人邮箱 第二参数为给该地址昵称 不同的邮箱会自动处理变动
        // $mail->addAddress('xxx@163.com','lsgo在线通知'); //添加多个收件人 则多次调用方法即可
        $mail->Subject = $title; //添加该邮件的主题
        $mail->Body = $content; //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符
        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        // $mail->addAttachment('./d.jpg','mm.jpg');
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
        
        $status = $mail->send();
        return $status ? true : false;
    }
}
