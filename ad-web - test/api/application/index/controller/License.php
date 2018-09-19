<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;
use think\Request;


/**
 * 设备证书 分层控制器
 */
class License extends Controller {

    protected $beforeActionList = [
        'checkGet'  => ['only' => 'read'],
        'checkPost' => ['only' => 'upload'],
        'checkLogin' => ['only' => 'upload']
    ];
    
    public function _initialize(){

    }

    //【接口】证书状态查询接口
    public function read(){
        return Finalsuccess(['cert' => $this->get()]);
    }

    //【接口】证书上传接口
    public function upload(){
        exec("uroot mkdir -p /hard_disk/grub");
        $public_path = ROOT_PATH . 'public' . DS . 'uploads';

        $file = request()->file('file');
        if(is_null($file)){
            Error('10026');
        }

        $info = $file->validate(['size'=>15678,'ext'=>'lic'])->move($public_path);
        if(!$info){
            Error('11022');
        }

        $filename = $info->getFilename(); 
        $save_path = $public_path .'/'.$info->getSaveName(); 

        $fp = fopen($save_path, "rb");
        $bin = fread($fp, 4); //只读4字节
        fclose($fp);
        if($bin !== "VEDA"){
            unlink($save_path);
            Error("11022");
        }

        //命令验证证书是否有效
        exec("uroot fpcmd load_licence --test ".$save_path,$result);
        if($result[0] !== "valid") {
            Error("11022");
        }

        //移动证书到licence
        exec("uroot mv ".$save_path." /hard_disk/grub/licence");
        exec("uroot rm ".$save_path);
        exec("uroot fpcmd load_licence");
        
        return Finalsuccess(['cert'=>$this->get()]);
    }

    //【方法】首页证书状态查询接口（提供给同级控制器）
    public function get(){
        exec("uroot fpcmd licence_status",$cert_status);
        if(empty($cert_status)){
            Error("10028");
        }
        $cert_status = json_decode($cert_status[0],true);
        if($cert_status["status"] === "valid" || $cert_status["status"] === "expired") {
            $cert_status["user"] = base64_decode($cert_status["user"]);
            $cert_status["desc"] = base64_decode($cert_status["desc"]);
            $cert_status["copy_right"] = base64_decode($cert_status["copy_right"]);
            $cert_status["licence_owner"] = base64_decode($cert_status["licence_owner"]);
            $cert_status["model"] = base64_decode($cert_status["model"]);
        }else{
            exec("uroot fpcmd serial_no",$serial_no);
            $cert_status["device_id"] = $serial_no[0];
        }
        $C_system = controller('System', 'event');
        $cert_status["version"] = $C_system->SysVersion();
        return $cert_status;
    }

    //【前置方法】验证登陆
    protected function checkLogin(){
        if(!CheckLoginToken())
            Error('12001','need login or token error');
    }
    //【前置方法】验证设备授权
    protected function checkValid(){
        $status = CheckValid();
        if($status != '0')
            Error($status,'need valid');
    }
    //【前置方法】验证post请求
    protected function checkPost(){
        if(!request()->isPost())
            Error("21001","need post method");
    }
    //【前置方法】验证get请求
    protected function checkGet(){
        if(!request()->isGet())
            Error("21002","need get method");
    }
    //【空方法】
    public function _empty(){
        $this->redirect('/errorpage');
    }

}