<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/4
 * Time: 9:51
 */

namespace app\index\job;


use app\index\model\JobFailed;
use think\Log;
use think\queue\Job;

abstract class BaseJob
{

    // Job 最大尝试次数
    protected $maxAttempt = 3;

    // Job 下次重试推迟秒数
    protected $delay = 0;

    /**
     * 执行该 Job
     * @param Job $job
     * @param $data
     * @return mixed
     */
    abstract public function fire(Job $job, $data);

    /**
     * 队列重试
     * @param Job $job
     */
    public function retry(Job $job)
    {
        // 清除队列
        $job->delete();
        if ($job->attempts() >= $this->maxAttempt) {
            // 触发队列失败的事件
            $job->failed();
        } else {
            // 重新执行
            $job->release($this->delay);
        }

    }

}