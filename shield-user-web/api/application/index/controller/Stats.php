<?php
namespace app\index\controller;
use app\index\model\ServerModel;
use think\Controller;
use think\Session;
use think\Loader;

class Stats extends Controller {

    private $server;    
    private $validate; 
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'real_time_flow,flow_rank'],
        'checkLogin'
    ];

    public function _initialize(){
        $this->server = new ServerModel;        
        $this->validate = loader::validate("Stats");
    }

    /**
     * 获取某个服务器ip的实时流量
     * @param   string    $server_ip  服务器IP
     * @return array     $stats 返回结果数组
     */
    public function real_time_flow(){
        if (!$this->validate->scene('real_time_flow')->check(input())) 
            return Finalfail($this->validate->getError());

        $uid = session("user_auth.uid");
        $server_ip = input('get.server_ip');

        $datas = $this->server->check_user_servers($uid,$server_ip);
        if(count($datas) == 0)
            return Finalfail("10024");

        //===============debug data ================ 
        $stats = [
            [
                'flow_in'=>'1024',
                'flow_out'=>'20156',
                'in_pkt'=>'500',
                'out_pkt'=>'100',
                'in_conn'=>'35',
                'out_conn'=>'40',
                'time'  =>  NowTime()
            ],
            [
                'flow_in'=>'5000',
                'flow_out'=>'1000',
                'in_pkt'=>'424',
                'out_pkt'=>'44',
                'in_conn'=>'100',
                'out_conn'=>'47',
                'time'  =>  NowTime()
            ],
            [
                'flow_in'=>'14778',
                'flow_out'=>'101',
                'in_pkt'=>'445',
                'out_pkt'=>'77',
                'in_conn'=>'104',
                'out_conn'=>'104',
                'time'  =>  NowTime()
            ],
            [
                'flow_in'=>'445',
                'flow_out'=>'101224',
                'in_pkt'=>'142',
                'out_pkt'=>'4534',
                'in_conn'=>'11',
                'out_conn'=>'453',
                'time'  =>  NowTime()
            ],
            [
                'flow_in'=>'424',
                'flow_out'=>'14',
                'in_pkt'=>'10',
                'out_pkt'=>'101',
                'in_conn'=>'14',
                'out_conn'=>'255',
                'time'  =>  NowTime()
            ]
        ];
        return Finalsuccess(["traffic" => $stats[rand(0,4)]]);
    }

    /**
     * 获取实时流量排名
     * @param   string    $orderby  排序规则
     * @param   string    $limit    每页显示条数
     * @return array     $rank     返回结果数组
     */
    public function flow_rank(){
        if (!$this->validate->scene('flow_rank')->check(input())) 
            return Finalfail($this->validate->getError());

        $uid = session("user_auth.uid");
        $orderby = is_null(input('get.orderby')) ? "flow_in" : input('get.orderby');
        $limit = is_null(input('get.limit')) ? 0 : input('get.limit');
        
        //===============debug data ================ 
        $rank = [
            [
                'server_ip'=>'192.168.2.56',                
                'flow_in'=>'1024',
                'flow_out'=>'20156',
                'in_pkt'=>'500',
                'out_pkt'=>'100',
                'in_conn'=>'35',
                'out_conn'=>'40'
            ],
            [   
                'server_ip'=>'192.168.2.57',    
                'flow_in'=>'10245',
                'flow_out'=>'300',
                'in_pkt'=>'12',
                'out_pkt'=>'1004',
                'in_conn'=>'50',
                'out_conn'=>'30'
            ]
            
        ];
        return Finalsuccess(["rank"=>$rank, "total"=>2]);
    }
   
    protected function checkLogin(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }
    protected function checkPost(){
        if(!request()->isPost())
            Error("21001","need post");
    }
    protected function checkGet(){
        if(!request()->isGet())
            Error("21002","need get");
    }
    public function _empty(){
        $this->redirect('/errorpage');
    }
}