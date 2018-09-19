<?php
namespace app\index\controller;

use app\index\repository\DomainRepository;
use app\index\repository\UserRepository;
use app\index\service\Auth as AuthService;
use app\index\traits\CheckLogin;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;
use think\Cache;

class Manage extends Controller
{
    use CheckLogin;
    // protected $validate;

    public function _initialize()
    {
        // $this->validate = Loader::validate('User');
    }

    protected $beforeActionList = [
        'checkLogin',
    ];

    /**
     * @SWG\Get(
     *      path="/manage",
     *      tags={"manage 用户主页"},
     *      summary="【获取】用户主页",
     *      @SWG\Response(response="200", ref="#/definitions/Manageinfo")
     * )
     */
    public function get()
    {
        $domain_repository = new DomainRepository();
        $user_id = AuthService::id();
        $filter = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['type' => 0]],
                        ['match' => ['type' => 1]],
                        ['match' => ['type' => 2]]
                    ]
                ],
                'must'  =>  [
                    ['term' => ['uid.keyword' => $user_id]]
                ]
            ]
        ];
        $all_domains = $domain_repository->domainList($filter);

        $filter = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['type' => 0]],
                        ['match' => ['type' => 1]]
                    ],
                    'must'  =>  [
                        ['match' => ['status' => 4]],
                        ['term' => ['uid.keyword' => $user_id]]
                    ]
                ]
            ]
        ];
        $join_sites = $domain_repository->domainList($filter);

        $filter = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['match' => ['type' => 3]]
                    ]
                ],
                'must'  =>  [
                    ['term' => ['uid.keyword' => $user_id]]
                ]
            ]
        ];
        $join_ports = $domain_repository->domainList($filter);

        $user_repository = new UserRepository();
        $user_info = $user_repository->selectUserById($user_id);
        $user_account = $user_info['account'];
        $response = [
            'all_domain_counts'=>count($all_domains),
            'joined_site_counts'=>count($join_sites),
            'joined_port_counts'=>count($join_ports),
            'account'      =>$user_account,
            'user_email'   =>$user_id
        ];

        return Finalsuccess($response);
    }
}
