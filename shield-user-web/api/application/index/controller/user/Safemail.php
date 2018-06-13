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

class Safemail extends Controller
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
     *      path="/safemail/send",
     *      tags={"user 用户管理"},
     *      summary="【发送】邮件验证码用于修改安全邮箱",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="【注】前端按钮限制一分钟获取一次验证码",
     *          @SWG\Schema(
     *              @SWG\Property(property="safemail", type="string", example="test@veda.com")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function send()
    {
        if (!$this->validate->scene('safemail_send')->check(input())) {
            Fail($this->validate->getError());
        }
        $safemail = input('post.safemail');
        $result = $this->repository->searchByFilter(['safe_email.keyword'=>$safemail]);
        if (!empty($result) && $result[0]['email_verify_status'] === 1) {
            Fail("10007", "该邮箱已设置为安全邮箱");
        }

        if (Cache::get("reset_safemail_".$safemail) !== false) {
            Fail('10005', '五分钟内只能发送一封邮件');
        }

        do {
            $token = Randchar(6);
        } while (Cache::get($token) !== false);

        $result = MailService::send($safemail, $token, "safemail");
        if (!$result) {
            Fail("10004", "邮件发送失败");
        }

        Cache::set("reset_safemail_".$safemail, strtotime('now'), 300);
        Cache::set($token, $safemail);
        Success();
    }

    /**
     * @SWG\Put(
     *      path="/safemail",
     *      tags={"user 用户管理"},
     *      summary="【修改】安全邮箱",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="",
     *          @SWG\Schema(
     *              @SWG\Property(property="safemail", type="string", example="test@veda.com"),
     *              @SWG\Property(property="token", type="string", example="251014")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function update()
    {
        if (!$this->validate->scene('safemail_update')->check(input())) {
            Fail($this->validate->getError());
        }
        $safemail = input('put.safemail');
        $token = input('put.token');
        if (!Cache::get($token) || (Cache::get($token) !== $safemail)) {
            Fail("10006", "验证码错误");
        }
        Cache::rm($token);
        $u_id = AuthService::id();
        $result = $this->repository->updateUser(['safe_email'=>$safemail, 'email_verify_status'=>1], $u_id);
        $result ? Success() : Fail('20001', "es error");
    }
}
