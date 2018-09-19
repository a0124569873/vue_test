<?php
namespace app\index\controller\user;

use app\index\repository\UserRepository;
use app\index\service\Message as MessageService;
use app\index\service\Auth as AuthService;
use app\index\traits\CheckLogin;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;
use think\Cache;

class Safephone extends Controller
{
    use CheckLogin;
    protected $validate;
    protected $repository;

    public function _initialize()
    {
        $this->validate = Loader::validate('User');
        $this->repository = new UserRepository();
    }

    protected $beforeActionList = [
        'checkLogin',
    ];

    /**
     * @SWG\Post(
     *      path="/safephone/send",
     *      tags={"user 用户管理"},
     *      summary="【发送】短信验证码用于修改安全手机",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="【注】前端按钮限制一分钟获取一次验证码",
     *          @SWG\Schema(
     *              @SWG\Property(property="safephone", type="string", example="157********")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function send()
    {
        if (!$this->validate->scene('safephone_send')->check(input())) {
            Fail($this->validate->getError());
        }
        $safephone = input('post.safephone');
        $result = $this->repository->searchByFilter(['mobile.keyword'=>$safephone]);
        if(!empty($result) && $result[0]['mobile_verify_status'] === 1){
            Fail("10008", "该手机号已设置为安全手机");
        }
        $token = Randnum(6);
        MessageService::send($safephone, $token, "safephone");
        Cache::set($token, $safephone);
        return Success(['token' => $token]);
    }

    /**
     * @SWG\Put(
     *      path="/safephone",
     *      tags={"user 用户管理"},
     *      summary="【修改】安全邮箱",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="",
     *          @SWG\Schema(
     *              @SWG\Property(property="safephone", type="string", example="157********"),
     *              @SWG\Property(property="token", type="string", example="251014")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function update()
    {
        if (!$this->validate->scene('safephone_update')->check(input())) {
            Fail($this->validate->getError());
        }
        $safephone = input('put.safephone');
        $token = input('put.token');
        if (!Cache::get($token) || (Cache::get($token) !== $safephone)) {
            Fail("10006", "验证码错误");
        }
        Cache::rm($token);
        $u_id = AuthService::id();
        $result = $this->repository->updateUser(['mobile'=>$safephone, 'mobile_verify_status'=>1], $u_id);
        $result ? Success() : Fail('20001', "es error");
    }
}
