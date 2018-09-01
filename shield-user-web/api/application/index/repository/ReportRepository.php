<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/5/3
 * Time: 10:18
 */

namespace app\index\repository;


use app\index\model\AttackLog;
use app\index\service\Time;
use Carbon\Carbon;

class ReportRepository extends BaseRepository
{
    protected $attackLogModel = null;

    public function __construct()
    {
        $this->attackLogModel = new AttackLog();
    }

    /**
     * @param array $request
     * @return array
     */
    public function getFilterAggs($request = [])
    {
        $endTime = $request['end_time'] ?? time();
        $startTime = $request['start_time'] ?? $endTime - 15 * 60;  // 默认开始时间为结束时间的前15分钟

        // 根据查询的开始时间和结束时间，确定聚合的Interval
        $interval = $this->caclAggsIntervalByTime($startTime, $endTime);
        $aggs = [
            "size_detail"      => [
                "date_histogram" => [
                    "field"           => "start_time",
                    "interval"        => $interval,
                    "format"          => "yyyy-MM-dd HH:mm:ss",
                    "min_doc_count"   => 0,
                    "extended_bounds" => [
                        "min" => date('Y-m-d H:i:s', $startTime),
                        "max" => date('Y-m-d H:i:s', $endTime)
                    ]
                ],
                "aggs"           => [
                    "max_size"      => [
                        "max" => [
                            'field' => 'size'
                        ]
                    ],
                    "max_drop_size" => [
                        "max" => [
                            'field' => 'drop_size'
                        ]
                    ]
                ]
            ],
            'total_size'       => [
                'sum' => ['field' => 'size']
            ],
            'total_drop_size'  => [
                'sum' => ['field' => 'drop_size']
            ],
            'total_count'      => [
                'sum' => ['field' => 'count']
            ],
            'total_drop_count' => [
                'sum' => ['field' => 'drop_count']
            ],
            "deftype"          => [
                "terms" => [
                    "field" => "deftype.keyword"
                ]
            ]
        ];

        return $aggs;
    }

    /**
     * 获取攻击趋势信息
     *
     * @param $filter
     * @param string $interval
     * @return array
     * @throws \Exception
     */
    public function getAttackTrend($filter, $interval = 'minute')
    {
        // 聚合查询所有攻击日志
        $attackLogs = $this->attackLogModel->esAggsSearch($filter, 0, 10000);
        // 对聚合字段进行过滤
        $attacks = $this->processSizeDetailAggs($attackLogs['aggs']['size_detail']);
        $flowTotal = $attackLogs['aggs']['total_size'];
        $dropFlowTotal = $attackLogs['aggs']['total_drop_size'];
        $pkgTotal = $attackLogs['aggs']['total_count'];
        $dropPkgTotal = $attackLogs['aggs']['total_drop_count'];
        $attackTypeCount = count($attackLogs['aggs']['deftype']['buckets']);
        $attackCount = count(array_filter($attacks, function ($v) {
            return $v['max_flow'] != 0;
        }));

        return compact('attacks', 'flowTotal', 'dropFlowTotal', 'pkgTotal', 'dropPkgTotal', 'attackTypeCount', 'attackCount');
    }

    /**
     * 将QuickTime格式（1:min|1:hour）解析为：
     * [
     *  'startTime' => 1234567890
     *  'endTime' => 1234567890
     * ]
     * @param null $quickTime
     * @return array
     */
    public function parseQuickTime($quickTime = null)
    {
        $endTime = $startTime = null;
        if ($quickTime) {
            $endTime = Carbon::now();
            list($num, $unit) = explode(',', $quickTime);
            switch ($unit) {
                case 'hour':
                    $startTime = $endTime->subHours($num);
                    break;
                case 'min':
                    $startTime = $endTime->subMinutes($num);
                    break;
                default:
                    $startTime = $endTime->subMinutes($num);
                    break;
            }
        }

        return compact('startTime', 'endTime');
    }

    /**
     * 过滤流量聚合详细信息
     *
     * @param array $sizeDetailAggs
     * @return array
     */
    private function processSizeDetailAggs($sizeDetailAggs = [])
    {
        $result = [];
        foreach ($sizeDetailAggs['buckets'] as $sizeDetail) {
            $result[] = [
                'time'            => strtotime($sizeDetail['key_as_string']),
                'max_flow'        => $sizeDetail['max_size']['value'] ?? 0,
                'max_attack_flow' => $sizeDetail['max_drop_size']['value'] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * 根据开始时间和结束时间，获取需要聚合的时间间隔
     *
     * @param $startTime
     * @param $endTime
     * @return string
     */
    private function caclAggsIntervalByTime($startTime, $endTime)
    {
        $endTime = Time::createFromTimestamp($endTime);
        $startTime = Time::createFromTimestamp($startTime);
        $diffMins = $endTime->diffInMinutes($startTime);
        switch ($diffMins) {
            case $diffMins < 60:
                $interval = '3m';
                break;
            case $diffMins < 24 * 60:        // 24小时之内，按照每15分钟聚合
                $interval = '15m';
                break;
            case $diffMins > 24 * 60:        // 大于24小时，按照每小时聚合
                $interval = '1h';
                break;
            default :                   // 默认按照3分钟聚合
                $interval = '3m';
        }

        return $interval;
    }
}