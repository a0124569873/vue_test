<?php

namespace app\index\controller;

use app\index\repository\UserRepository;
use app\index\service\Auth as AuthService;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

class Sessions extends Controller
{
    protected $validate;
    protected $repository;
    public function _initialize()
    {
        $this->validate = Loader::validate('User');
        $this->repository = new UserRepository();
    }

    /**
     * @SWG\Get(
     *      path="/loginfo",
     *      tags={"session 登录管理"},
     *      summary="【获取】登录状态",
     *      @SWG\Response(response="200", ref="#/definitions/Loginfo")
     * )
     */
    public function index()
    {
        $user = AuthService::user();
        is_null($user) ? Fail('12001', 'need login', ['is_login'=>false, 'user_email' => '']) : Success(['is_login' => true, 'user_email' => $user['id']]) ;
    }

    /**
     * @SWG\Post(
     *      path="/login",
     *      tags={"session 登录管理"},
     *      summary="【登陆】管理员登录",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="登录post信息：",
     *          @SWG\Schema(
     *              @SWG\Property(property="username", type="string", example="root"),
     *              @SWG\Property(property="password", type="string", example="veda2017"),
     *              @SWG\Property(property="captcha", type="string", example="9716"),
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function create()
    {
        if (!$this->validate->scene('login')->check(input())) {
            Fail($this->validate->getError());
        }
        if (!captcha_check(input('post.captcha'))) {
            Fail("10002", '验证码错误'); // validate code error
        }
        $auth = input();
        $data = $this->repository->selectUserById($auth['email']);
        if (is_null($data)) {
            Fail('10001', '用户名或密码错误');
        }
        if($data['role'] !== 0){
            Fail("10001", "用户名或密码错误");
        }
        if (!password_verify($auth['password'], $data['password'])) {
            Fail('10001', '用户名或密码错误');
        }
        AuthService::login($auth);
        Success();
    }

    /**
     * @SWG\Delete(
     *      path="/logout",
     *      tags={"session 登录管理"},
     *      summary="【注销】用户注销",
     *      operationId="logoutUser",
     *      parameters={},
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function destroy()
    {
        AuthService::logout();
        Success();
    }
}
