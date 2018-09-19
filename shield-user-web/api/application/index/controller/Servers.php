<?php

namespace app\index\controller;

use app\index\model\ServerModel;
use app\index\model\UserInstanceModel;
use app\index\repository\DDoSRepository;
use app\index\repository\ServerRepository;
use app\index\service\MockData;
use app\index\traits\CheckLogin;
use app\index\validate\Servers as ServersValidator;
use think\Request;

class Servers extends BaseController
{
    use CheckLogin;

    private $server;

    protected $validator;

    protected $beforeActionList = [
        'checkLogin' => ['only' => 'get,add,setthr,serverPrice,serverConfig,serverAreas,serverLines']
    ];

    protected $repository = null;

    public function __construct(Request $request)
    {
        $this->repository = new ServerRepository();

        $this->validator = new ServersValidator();

        parent::__construct($request);
    }

    public function _initialize()
    {
        $this->server = new ServerModel;
    }

    /**
     *
     * @SWG\GET(
     *      path="/server",
     *      tags={"Server 用户实例"},
     *      summary="获取用户实例列表",
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          description="类型:1-独享型;2-共享型;3-非网站",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="line",
     *          in="query",
     *          description="线路,example: 11_1",
     *          type="integer"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="",
     *          ref="#/definitions/ServerList"
     *      )
     * )
     *
     * @return float|int|string
     * @throws \Exception
     */
    public function index()
    {

        $line = input('line', null);
        $type = input('type', null);

        $servers = $this->repository->getUserServer($line, $type);

        return Finalsuccess(['list' => $servers]);
    }

    /**
     * 获取用户的服务器列表
     * @param   string $page 页码
     * @param   string $row 每页行数
     * @return array     $server_list 返回结果数组
     */
    public function get()
    {
        if (!$this->validator->scene('get_servers')->check(input()))
            return Finalfail($this->validator->getError());

        $uid = session("user_auth.uid");
        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row = is_null(input('get.row')) ? 10 : input('get.row');

        $count = $this->server->count_user_servers($uid);
        $server_list = $this->server->get_user_servers($uid, $page, $row);
        $list = [
            "list" => $server_list,
            "page" => $count
        ];

        return Finalsuccess($list);
    }

    /**
     * 用户添加服务器
     * @param   string $server_ip 服务器ip
     * @return  object    json  返回结果
     */
    public function add()
    {
        if (!$this->validator->scene('add_servers')->check(input()))
            return Finalfail($this->validator->getError());

        $uid = session("user_auth.uid");
        $server_ip_arr = explode("|", input("post.server_ip"));
        $result = $this->server->exist_user_servers($uid, $server_ip_arr);
        if (count($result) > 0)
            return Finalfail("10022");

        $server_list = $this->server->get_servers(); // 获取server 的ip与id
        $id_arr = [];
        foreach ($server_list as $server) {
            if (in_array($server['ip'], $server_ip_arr)) {
                $id_arr[] = $server['id'];
                continue;
            }
            continue;
        }

        if (empty($id_arr))
            return Finalfail("10023");

        $result = $this->server->add_user_servers($uid, $id_arr);
        if ($result <= 0) {
            return Finalfail("20001");
        }

        return Finalsuccess();
    }

    /**
     * 设置阈值
     * @param   string $server_ip 服务器ip
     * @param   string $thr_in 输入流量
     * @param   string $thr_out 输出流量
     * @return  object    json  返回结果
     */
    public function setthr()
    {
        if (!$this->validator->scene('setthr')->check(input()))
            return Finalfail($this->validator->getError());

        $uid = session("user_auth.uid");
        $server_ip_arr = explode("|", input("post.server_ip"));
        $thr_in = is_null(input('post.thr_in')) ? 0 : input('post.thr_in');
        $thr_out = is_null(input('post.thr_out')) ? 0 : input('post.thr_out');

        $datas = $this->server->check_user_servers($uid, $server_ip_arr);

        if (count($datas) != count($server_ip_arr))
            return Finalfail("10024");

        foreach ($datas as &$server) {
            $server['thr_in'] = $thr_in;
            $server['thr_out'] = $thr_out;
        }
        $result = $this->server->update_servers($datas);
        if ($result < 0) {
            return Finalfail("20001");
        }

        return Finalsuccess();
    }

    public function _empty()
    {
        $this->redirect('/errorpage');
    }

    /**
     *
     * @SWG\Post(
     *      path="/server/price",
     *      tags={"Server 用户实例"},
     *      summary="计算当前选购实例的价格",
     *     @SWG\Parameter(
     *         name="type=1|2",
     *         in="body",
     *         description="网站类价格计算参数",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"type": 1,"line": "1_1","base_bandwidth": 10,"bandwidth": 100,"count": 1,"period": "2:year","site_count": 50},
     *              description="站点"
     *          )
     *     ),
     *     @SWG\Parameter(
     *         name="type=3",
     *         in="body",
     *         description="非网站类价格计算参数",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"type": 3,"line": "1_1","base_bandwidth": 10,"bandwidth": 100,"count": 1,"period": "2:month","port_count": 50},
     *              description="站点"
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok", "amount": 100}
     *          )
     *      )
     * )
     *
     * @return float|int|string
     */
    public function serverPrice()
    {
        $data = input();
        $invalid = $this->validate($data, ['type' => 'require|in:1,2,3']);
        if ($invalid !== true) {
            return Finalfail(11000, $invalid);
        }

        if ($data['type'] != ServerModel::SERVER_TYPE_WEB_PORT) { // 网站类
            if ($this->validator->scene('site_price')->check($data)) {
                // 计算网站类实例价格
                $amount = $this->repository->calcSiteServerPrice($data);
            } else {
                return Finalfail(11000, $this->validator->getError());
            }

        } else {  // 非网站类
            if ($this->validator->scene('port_price')->check($data)) {
                // 计算非网站类实例价格
                $amount = $this->repository->calcNonSiteServerPrice($data);
            } else {
                return Finalfail(11000, $this->validator->getError());
            }
        }

        return Success(compact('amount'));
    }

    /**
     *
     * @SWG\Get(
     *      path="/server/config",
     *      tags={"Server 用户实例"},
     *      summary="获取实例基础数据",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{
     *     "line":{{"text":"海外","value":"0","ip_count":{"text":"1个","value":"1","unit":"个"},
     *     "base_bandwidth":{{"text":"10Gb","value":"10","unit":"Gb"},{"text":"20Gb","value":"20","unit":"Gb"},{"text":"30Gb","value":"30","unit":"Gb"}},
     *     "normal_bandwidth":{{"text":"10M","value":"10","unit":"M"},{"text":"20M","value":"20","unit":"M"},{"text":"30M","value":"30","unit":"M"}},
     *     "bandwidth":{{"text":"10Gb","value":"10","unit":"Gb"},{"text":"20Gb","value":"20","unit":"Gb"},{"text":"30Gb","value":"30","unit":"Gb"}},
     *     "site_count": {"min": "50", "max": 200, "step": "1"},"port_count": {"min": "50", "max": 200, "step": "1"}},
     *     "ord_time":{{"text":"1个月","value":"1:month","unit":"month"},{"text":"2个月","value":"2:month","unit":"month"},
     *     {"text":"3个月","value":"3:month","unit":"month"},{"text":"4个月","value":"4:month","unit":"month"},{"text":"5个月","value":"5:month","unit":"month"}}}}}
     *          )
     *      )
     * )
     *
     * 获取实例基础数据
     *
     * @return string
     * @throws \app\common\exception\MockDataModuleNotFound
     */
    public function serverConfig()
    {
        // 获取所有的实例基础数据
        $data = MockData::value('ServerConfig');

        return Finalsuccess(['data' => $data]);
    }

    /**
     *
     * @SWG\Get(
     *      path="/server/{id}/areas",
     *      tags={"Server 用户实例"},
     *      summary="获取实例可接入地域",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="用户实例Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","list":{{"text":"北京","value":11},{"text":"天津","value":12}}}
     *          )
     *      )
     * )
     *
     * 获取用户实例可接入的地域列表
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function serverAreas($id)
    {
        $data = input();
        if (!$this->validator->scene('server_area')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if (!$instance = (new DDoSRepository())->getDDoSById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该实例信息！');
        }
        $areas = $this->repository->getServerAvailableAreas($instance['instance_line'], $instance['type']);

        return Finalsuccess(['list' => $areas]);
    }

    /**
     *
     * @SWG\Get(
     *      path="/server/lines",
     *      tags={"Server 用户实例"},
     *      summary="获取实例可接入线路",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","list":{{"value":0,"text":"海外"},{"value":1,"text":"电信"},
     *     {"value":2,"text":"联通"},{"value":3,"text":"电信、联通"},{"value":4,"text":"移动"},{"value":5,"text":"电信、移动"},
     *     {"value":6,"text":"移动、联通"},{"value":7,"text":"移动、联通和电信"},{"value":8,"text":"BGP"}}}
     *          )
     *      )
     * )
     *
     * 获取所有实例可接入线路
     *
     * @return string
     */
    public function serverLines()
    {
        $lines = $this->repository->getServerLines();

        return Finalsuccess(['list' => $lines]);
    }

}