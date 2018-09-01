<?php
namespace app\index\controller\user;

use app\index\repository\UserRepository;
use app\index\service\Auth as AuthService;
use app\index\traits\CheckLogin;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;
use think\Cache;

class Realname extends Controller
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
     * @SWG\Put(
     *      path="/realname",
     *      tags={"user 用户管理"},
     *      summary="【更新】实名验证",
     *      @SWG\Parameter(
     *          name="",
     *          required=true,
     *          in="body",
     *          description="",
     *          @SWG\Schema(
     *              @SWG\Property(property="real_name", type="string", example="土豪"),
     *              @SWG\Property(property="id_number", type="string", example="410527************"),
     *              @SWG\Property(property="captcha", type="string", example="25101")
     *          )
     *      ),
     *      @SWG\Response(response="200", description="", ref="#/definitions/Success")
     * )
     */
    public function update()
    {
        if (!$this->validate->scene('realname')->check(input())) {
            Fail($this->validate->getError());
        }
        $real_name = input('put.real_name');
        $id_number = input('put.id_number');
        if (!captcha_check(input('put.captcha'))) {
            Fail("10002", '验证码错误');
        }
        $u_id = AuthService::id();
        $result = $this->repository->updateUser(['real_name'=>$real_name, 'id_number'=>$id_number, 'id_verify_status'=>1], $u_id);
        $result ? Success() : Fail('20001', "es error");
    }
}
