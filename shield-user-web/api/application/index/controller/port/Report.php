<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/5/4
 * Time: 14:33
 */

namespace app\index\controller\port;


use app\index\controller\BaseController;
use app\index\repository\PortRepository;
use app\index\repository\ReportRepository;
use think\Request;

class Report extends BaseController
{
    protected $reportRepository = null;

    public function __construct(Request $request)
    {
        $this->repository = new PortRepository();
        $this->reportRepository = new ReportRepository();

        parent::__construct($request);
    }

    /**
     * @SWG\Get(
     *      path="/port/{id}/report/attacks",
     *      tags={"Port 应用防护报表信息"},
     *      summary="获取应用攻击信息",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="应用 Id",
     *          type="integer",
     *      ),
     *      @SWG\Parameter(
     *          name="ip",
     *          in="query",
     *          description="高防IP",
     *          type="integer",
     *      ),
     *      @SWG\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="开始时间(时间戳)",
     *          type="integer",
     *      ),
     *      @SWG\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="结束时间(时间戳)",
     *          type="integer",
     *      ),
     *      @SWG\Parameter(
     *          name="quick_time",
     *          in="query",
     *          description="快速查询的时间，Example: 15:min,30:min,1:hour,24:hour",
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"attacks":{{"time":1525356000,"max_flow":0,"max_attack_flow":0},
     *     {"time":1525356900,"max_flow":0,"max_attack_flow":0},{"time":1525357800,"max_flow":0,"max_attack_flow":0},
     *     {"time":1525385700,"max_flow":0,"max_attack_flow":0},{"time":1525386600,"max_flow":0,"max_attack_flow":0},
     *     {"time":1525387500,"max_flow":0,"max_attack_flow":0},{"time":1525388400,"max_flow":0,"max_attack_flow":0},
     *     {"time":1525391100,"max_flow":0,"max_attack_flow":0},{"time":1525411800,"max_flow":0,"max_attack_flow":0},
     *     {"time":1525412700,"max_flow":0,"max_attack_flow":0}},"flowTotal":0,"dropFlowTotal":0,"pkgTotal":0,"dropPkgTotal":0,
     *     "attackTypeCount":0,"attackCount":64}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @param Request $request
     * @return string
     */
    public function attacks($id, Request $request)
    {
        try {
            $data = $request->only(['ip', 'start_time', 'end_time', 'quick_time']);
            if (!$port = $this->repository->getPortById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }

            $filter = [];
            // 高防IP
            $sip = (array)(!empty($data['ip']) ? $data['ip']: array_column($port['proxy_ip'], 'ip'));
            !empty($sip) && $filter[] = ['terms' => compact('sip')];

            $startTime = $data['start_time'] ?? null;
            $endTime = $data['end_time'] ?? null;
            // 快速查询
            if (!empty($data['quick_time'])) {   // 如果有快速查询时间，需要使用快速查询的时间作为最终查询时间范围
                $timeParsed = $this->reportRepository->parseQuickTime($data['quick_time']);
                $startTime = $timeParsed['start_time'];
                $endTime = $timeParsed['end_time'];
            }
            !empty($startTime) && $filter[] = ['range' => ['start_time' => ['gte' => gmt_withTZ($startTime)]]];
            !empty($endTime) && $filter[] = ['range' => ['start_time' => ['lt' => gmt_withTZ($endTime)]]];

            // 查询条件
            $filter = ['query' => ['bool' => compact('filter')]];
            // 聚合方式
            $filter['aggs'] = $this->reportRepository->getFilterAggs($data);
            $attacks = $this->reportRepository->getAttackTrend($filter);

            return Finalsuccess(['data' => $attacks]);
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_ES_ERROR, '获取应用流量趋势失败！');
        }
    }
}