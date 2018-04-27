<?php

namespace app\index\controller;
use app\index\model\RevertLink;
use app\index\model\UserInfo;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Relink extends Controller{

    protected $m_relink;
    protected $v_relink;

    public function _initialize(){
        $this->m_relink = new RevertLink;
        $this->v_relink = Loader::validate('Relink'); 
    }

    protected $beforeActionList = [
        'check_get'   => ['only' => 'get'],
        'check_post'  => ['only' => 'set,del'],
        'check_login' 
    ];

    //【接口】获取所有主动回连配置
    public function get(){
        if(!$this->v_relink->scene('get')->check(input()))
            return Finalfail($this->v_relink->getError());

        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row  = is_null(input('get.row')) ? 10 : input('get.row');

        $count = $this->m_relink->countReLink();
        $data = $count == 0 ?  [] : $this->m_relink->selectReLink($page, $row);
        $link = array_map(function($conf){
            return $conf['uid']."|".$conf['src_ip']."|".$conf['dst_ip']."|".$conf['src_port']."|".$conf['dst_port']."|".$conf['r_port']."|".$conf["p_ip"]."|".$conf["pro_type"];
        },$data);

        $result = ["link" => $link, "count" => $count];
        return Finalsuccess($result);
    }

    //【接口】设置主动回连配置
    public function set(){
        $conf_arr = RawJsonToArr();

        if(!array_key_exists('link',$conf_arr))
            Error("22001");
        
        $conf_arr = $conf_arr['link'];
        if(!$this->v_relink->scene('set')->check(['link'=>$conf_arr]))
            return Finalfail($this->v_relink->getError());

        $save_conf = [];
        foreach($conf_arr as $conf){
            $a = [];
            $tmp_arr = explode("|",$conf);
            if(!$this->checkPip($tmp_arr[5])){ //检查私有ip是否在范围
                return Finalfail("15012");
            }

            $a = [
                // "uid"=>array_pop($unuse_uid),
                "src_ip"=>$tmp_arr[0],
                "dst_ip"=>$tmp_arr[1],
                "p_ip"=>$tmp_arr[5],
                "pro_type"=>$tmp_arr[6]
            ];
            $tmp_arr[2] = empty($tmp_arr[2])?NULL:$tmp_arr[2];
            $tmp_arr[3] = empty($tmp_arr[3])?NULL:$tmp_arr[3];
            $tmp_arr[4] = empty($tmp_arr[4])?NULL:$tmp_arr[4];

            if($tmp_arr[6] == "6" || $tmp_arr[6] == "17"){
                $a['src_port'] = $tmp_arr[2];
                $a['dst_port'] = $tmp_arr[3];
                $a['r_port'] = $tmp_arr[4];

            }else{
                Error("11023");
                $a['src_port'] = NULL;
                $a['dst_port'] = NULL;
                $a['r_port'] = NULL;
            }

            $save_conf[] = $a;
        }

        $result = $this->m_relink->insertAllRelink($save_conf);
        if(count($result) <= 0)
            return Finalfail('20001');

        $this->reloadRelink();

        #update Gserver
        $ress = rewriteGConf();
        if ($ress != "success") {


            $this->m_relink->deleteRelinkByUid($result);

            $this->reloadRelink();
            return Finalfail("11024",$ress);
        }

        return Finalsuccess();
    }

    //获取count个未被使用的uid

    //【接口】删除主动回连配置
    public function del(){
        $clear = input('get.clear');
        if($clear === "true"){
            ExcuteExec("uroot iptables -F");//清空
            ExcuteExec("uroot fpcmd cs -f");//清空
            ExcuteExec("iptables -A FORWARD -m uid --uid 1");
            
            $res_tmp = $this->m_relink->selectAllRelink();
            $this->m_relink->deleteRelinkAll();

            #update Gserver
            $ress = rewriteGConf();
            if ($ress != "success") {

                foreach ($res_tmp as $key => $value) {
                    unset($res_tmp[$key]["uid"]);
                }
                $this->m_relink->insertAllRelink($res_tmp);
                $this->reloadRelink();
                return Finalfail("11024",$ress);
            }
            return Finalsuccess();
        }

        if(!$this->v_relink->scene('del')->check(input()))
            return Finalfail($this->v_relink->getError());

        $id_arr = explode(",", input('post.uids'));

        $res_tmp = $this->m_relink->getRelinkByUids($id_arr);

        $this->m_relink->deleteRelinkByUid($id_arr);

        $this->reloadRelink();

        #update Gserver
        $ress = rewriteGConf();
        if ($ress != "success") {

            foreach ($res_tmp as $key => $value) {
                unset($res_tmp[$key]["uid"]);
            }

            $this->m_relink->insertAllRelink($res_tmp);

            $this->reloadRelink();
            return Finalfail("11024",$ress);
        }
        
        return Finalsuccess();
    }

    //【方法】清空所有，并读取现有配置
    private function reloadRelink(){
        ExcuteExec("uroot iptables -F");//清空
        ExcuteExec("uroot fpcmd cs -f");//清空
        ExcuteExec("iptables -A FORWARD -m uid --uid 1");
        $confs = $this->m_relink->selectAllRelink();
        foreach($confs as $conf){
            if ($conf["pro_type"] == "6" || $conf["pro_type"] == "17"){

                $conf_param = "uroot iptables -A FORWARD";
                if(!empty($conf["src_ip"])){
                    $conf_param.=(" -s ".$conf["src_ip"]);
                }
                if(!empty($conf["dst_ip"])){
                    $conf_param.=(" -d ".$conf["dst_ip"]);
                }

                $conf_param.=(" -p ".$conf["pro_type"]);

                if(!empty($conf["src_port"])){
                    $conf_param.=(" --sport ".$conf["src_port"]);
                }
                if(!empty($conf["dst_port"])){
                    $conf_param.=(" --dport ".$conf["dst_port"]);
                }

                $conf_param.=(" -m uid --uid ".$conf["uid"]);

                ExcuteExec($conf_param);
                if (!empty($conf["r_port"])) {
                    ExcuteExec("uroot fpcmd cs -u ". $conf["uid"] ." -r ". $conf["p_ip"] . " -d ". $conf["r_port"]);
                }

                continue;
            }
            $conf_param = "uroot iptables -A FORWARD -d ". $conf["dst_ip"];
            
            if (!empty($conf["src_ip"])){
                $conf_param.=(" -s ".$conf["src_ip"]);
            }
            $conf_param.= " -p ".$conf["pro_type"]." -m uid --uid ". $conf["uid"];
            ExcuteExec($conf_param);
            if (!empty($conf["r_port"])) {
                ExcuteExec("uroot fpcmd cs -u ". $conf["uid"] ." -r ". $conf["p_ip"] . " -d ". $conf["r_port"]);
            }

        }

        

    }

    //【方法】检查配置的私有ip是否有效
    private function checkPip($ip){
        $userinfo = new UserInfo;
        $data = $userinfo->selectPipByIp($ip);
        return empty($data) ? false : true;
    }

    protected function check_login(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }

    protected function check_post(){
        if(!request()->isPost())
            Error("21001","need post method");
    }

    protected function check_get(){
        if(!request()->isGet())
            Error("21002","need get method");
    }

    public function _empty(){
        $this->redirect('/errorpage');
    }
}
