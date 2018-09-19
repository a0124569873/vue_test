<?php

namespace app\index\service;

class Message
{

    /**
     * 短信发送
     * @return null
     */
    public static function send($address, $token, $type)
    {
        switch($type){
            case "safephone":
                $msg_content = $token;
                break;
        }
        # Send msg...
        return $msg_content;
    }
}
