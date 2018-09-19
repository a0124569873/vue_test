<?php
namespace app\index\controller\user;

use app\index\repository\UserRepository;
use app\index\service\Mail as MailService;
use app\index\service\Auth as AuthService;
use app\index\traits\CheckLogin;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;
use think\Cache;

class Password extends Controller
{
    use CheckLogin;
    protected $validate;
    protected $repository;

    public function _initialize()
    {
        $this->validate = Loader::validate('User');
        $this->repository = new UserRepository();
    }

    /**
     * @SWG\Post(
     *      path="/password/send",
     *      tags={"user 用户管理"},
     *      summary="【发送】修改密码的链接并发送邮件",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="",
     *          @SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="test@veda.com"),
     *              @SWG\Property(property="captcha", type="string", example="9716")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function send()
    {
        if (!$this->validate->scene('password_send')->check(input())) {
            Fail($this->validate->getError());
        }
        $email = input('post.email');
        $captcha = input('post.captcha');
        if (!captcha_check($captcha)) {
            Fail('10002', '验证码错误');
        }

        $data = $this->repository->selectUserById($email);
        if (is_null($data)) {
            Fail('10001', '该邮箱未注册');
        }
        
        $safe_email =  $data['email_verify_status'] === 1 ? $data['safe_email'] : $email; //安全邮箱
        if(Cache::get("forget_pwd_".$safe_email) !== false){
            Fail('10005', '五分钟内只能发送一封邮件');
        }
        
        $token = hash("md5", $email.Randchar(10));
        $result = MailService::send($safe_email, $token, "forget");
        if(!$result){
            Fail("10004","邮件发送失败");
        }
        
        Cache::set("forget_pwd_".$safe_email, strtotime('now'),300);
        Cache::set($token, $email);
        Success($safe_email);
    }

    /**
     * @SWG\Put(
     *      path="/password",
     *      tags={"user 用户管理"},
     *      summary="【修改】修改密码",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="通过token修改密码：email，token，new_old；通过登陆修改：old_pwd，new_pwd",
     *          @SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="test@veda.com"),
     *              @SWG\Property(property="token", type="string", example="fa4e51f56d5bba4404f96ea95b1943db"),
     *              @SWG\Property(property="old_pwd", type="string", example="veda2017"),
     *              @SWG\Property(property="new_pwd", type="string", example="veda2018"),
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function update()
    {
        if (!empty(input('put.email')) && !empty(input('put.token')) && !empty(input('put.new_pwd'))) {

            if (!$this->validate->scene('password_token_update')->check(input())) {
                Fail($this->validate->getError());
            }
            $email = input('put.email');
            $token = input('put.token');
            $new_pwd = input('put.new_pwd');

            if (!Cache::get($token) || (Cache::get($token) !== $email)) {
                Fail("10005", "链接超时");
            }
            Cache::rm($token);

        } elseif (!empty(input('put.old_pwd')) && !empty(input('put.new_pwd'))) {

            $this->checkLogin();

            if (!$this->validate->scene('password_update')->check(input())) {
                Fail($this->validate->getError());
            }
            $email = AuthService::id();
            $old_pwd = input('put.old_pwd');
            $new_pwd = input('put.new_pwd');
            $user_info = $this->repository->selectUserById($email);
            if (!password_verify($old_pwd, $user_info['password'])) {
                Fail('10001', '密码错误');
            }

        } else {
            return null;
        }

        $result = $this->repository->updateUser(['password'=>password_hash($new_pwd, PASSWORD_DEFAULT)], $email);
        $result ? Success() : Fail('20001', "es error");
    }
}
