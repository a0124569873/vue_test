<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/4
 * Time: 9:31
 */

namespace app\index\controller;

use app\index\job\Job1;
use app\index\job\SetDNSConfig;
use app\index\model\JobFailed;
use app\index\repository\DDoSRepository;
use think\Queue;

class TestJob extends BaseController
{
    public function index()
    {

        Queue::push(SetDNSConfig::class, ['name' => 'job1']);
        exit('here');
    }

}