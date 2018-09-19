<?php

namespace app\index\controller\user;

use app\index\service\Auth as AuthService;
use app\index\repository\UserRepository;
use app\index\traits\CheckLogin;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

class Users extends Controller
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
        'checkLogin' => ['only'=>"profile"],
    ];

    /**
     * @SWG\Get(
     *      path="/users/profile",
     *      tags={"user 用户管理"},
     *      summary="【获取】用户资料",
     *      @SWG\Response(response="200", ref="#/definitions/Userinfo")
     * )
     */
    public function profile()
    {
        $email = AuthService::id();
        $user_data = $this->repository->selectUserById($email);
        unset($user_data['password']);
        Success($user_data);
    }

    /**
     * @SWG\Post(
     *      path="/users",
     *      tags={"user 用户管理"},
     *      summary="【注册】新用户",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="注册用户信息：",
     *          @SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="test@veda.com"),
     *              @SWG\Property(property="password", type="string", example="veda2017"),
     *              @SWG\Property(property="captcha", type="string", example="9716"),
     *              @SWG\Property(property="mobile", type="string", example="13888888888")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function create()
    {
        if (!$this->validate->scene('register')->check(input())) {
            Fail($this->validate->getError());
        }
        if (!captcha_check(input('post.captcha'))) {
            Fail("10002", '验证码错误');
        }
        $result = $this->repository->selectUserById(input('post.email'));
        if (!is_null($result)) {
            Fail("10003", "该邮箱已注册");
        }
        $user_info = [
            "email"                      =>  input('post.email'),
            "password"                =>  password_hash(input('post.password'), PASSWORD_DEFAULT),
            "mobile"                    =>  !input("?post.mobile") ?  "" : input('post.mobile'),
            "mobile_verify_status"=>  0,
            "safe_email"             =>  "",
            "email_verify_status" =>  0,
            "id_number"				=>  "",
            "real_name"				=>  "",
            "id_verify_status"		=>  0,
            "account"                 =>  0,
            "role"                      =>  0 
        ];
        $result = $this->repository->insertUser($user_info);
        $result ? Success() : Fail('20001', "es error");
    }
}
