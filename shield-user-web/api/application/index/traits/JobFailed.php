<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/8
 * Time: 14:26
 */

namespace app\index\traits;

use think\Log;
use app\index\model\JobFailed as Model;

trait JobFailed
{

    /**
     * Job 执行失败的处理
     *
     * @param $data
     * @return void
     */
    public function failed($data)
    {
        // 记录队列执行错误
        $this->addLog($data);

        // 将失败的队列写入 job_failed表
        $this->setJobFailed($data);
    }

    private function getJobName()
    {
        return get_called_class();
    }

    /**
     * 日志记录执行失败的队列任务
     * @param $data
     */
    private function addLog($data)
    {
        Log::info('Job failed with data:' . json_encode(['job' => $this->getJobName(), 'data' => $data]));
    }

    /**
     * 将失败的队列任务写入 job_failed表
     * @param $data
     */
    private function setJobFailed($data)
    {
        Model::create(['job' => $this->getJobName(), 'data' => json_encode($data)]);
    }
}