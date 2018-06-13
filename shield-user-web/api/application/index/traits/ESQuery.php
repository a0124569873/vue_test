<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/5/2
 * Time: 12:38
 */

namespace app\index\traits;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use think\Log;

trait ESQuery
{
    protected static $esInstance = null;

    // ES Index
    protected $esIndex = null;

    // ES Type
    protected $esType = null;

    /**
     * 获取ES Client 实例
     * @return \Elasticsearch\Client|null
     */
    protected static function esInstance()
    {
        if (!self::$esInstance) {
            $ESHost = config('database.es_host');
            self::$esInstance = ClientBuilder::create()->setHosts($ESHost)->build();
        }

        return self::$esInstance;
    }

    /**
     * 执行ES 搜索
     * @param $filter
     * @param int $from
     * @param null $size
     * @return array
     * @throws \Exception
     */
    public function esSearch($filter, $from = 0, $size = null)
    {
        try {
            $filter = [
                'index' => $this->esIndex, 'type' => $this->esType, 'from' => $from,
                'body'  => $filter
            ];
            $filter['size'] = $size ?: 50;

            Log::info('ES Search Filter:' . json_encode(compact('filter', 'from', 'size')));

            $resources = self::esInstance()->search($filter);
            // 对查询命中结果进行处理
            $result = $this->processESHits($resources['hits']['hits']);
            /*
            $hits = $resources['hits']['hits'];
            $result = array_map(function ($hit) {
                return array_merge($hit['_source'], ['id' => $hit['_id']]);
            }, $hits);
            */
            Log::info('ES Search Result:' . json_encode($result));

            return $result;
        } catch (\Exception $e) {
            Log::info('ES Search Error:' . $e->getMessage());
            //  404 Exception
            if ($e instanceof Missing404Exception) {
                return [];
            }
            // Bad Request Exception
            if ($e instanceof BadRequest400Exception) {
                return [];
            }
            throw $e;
        }
    }

    public function esCount($filter, $from = 0, $size = 1)
    {
        try {

            $filter = [
                'index' => $this->esIndex, 'type' => $this->esType, 'from' => $from,
                'body'  => $filter
            ];
            $filter['size'] = $size ?: 50;
            Log::info('ES Search Filter:' . json_encode(compact('filter', 'from', 'size')));
            $resources = self::esInstance()->search($filter);

            return $resources['hits']['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 执行ES添加
     * @param $data
     * @param null $id
     * @return array|bool
     * @throws \Exception
     */
    public function esAdd($data, $id = null)
    {
        try {
            $attributes = array_merge(['index' => $this->esIndex, 'type' => $this->esType], ['body' => $data]);
            if ($id) {
                $attributes['id'] = $id;
            }

            Log::info('ES Add Data:' . json_encode(compact('data', 'id')));
            $result = self::esInstance()->index($attributes);
            Log::info('ES Add Result:' . json_encode(compact('data', 'id', 'result')));

            return $result;
        } catch (\Exception $e) {
            Log::info('ES Add Error:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     *
     * 根据ID 获取ES数据
     * @param $id
     * @return null|string
     * @throws \Exception
     */
    public function esGetById($id)
    {
        try {
            $filter = array_merge(['index' => $this->esIndex, 'type' => $this->esType, 'id' => $id]);
            $resource = self::esInstance()->get($filter);

            return $resource['_source'];
        } catch (\Exception $e) {
            if ($e instanceof Missing404Exception) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * 更新ES 记录的值
     * @param array $data
     * @param $id
     * @return array|bool
     * @throws \Exception
     */
    public function esUpdateById($data = [], $id)
    {
        try {
            $attributes = array_merge([
                'index' => $this->esIndex,
                'type'  => $this->esType,
                'id'    => $id,
                'body'  => [
                    'doc' => $data
                ]
            ]);

            Log::info('ES Update Data:' . json_encode(compact('data', 'id')));
            $result = self::esInstance()->update($attributes);
            if ($result) {
                $result['id'] = $id;
            }
            Log::info('ES Update Result:' . json_encode(compact('data', 'id', 'result')));

            return $result;
        } catch (\Exception $e) {
            Log::info('ES Update Result:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除特定ID的ES记录
     * @param $id
     * @return array|bool
     * @throws \Exception
     */
    public function esDeleteById($id)
    {
        try {
            $attributes = [
                'index' => $this->esIndex,
                'type'  => $this->esType,
                'id'    => $id,
            ];

            return self::esInstance()->delete($attributes);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function esBulkAdd($data)
    {
        try {
            return (bool)self::esInstance()->bulk($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * ES 批量删除操作
     * @param $ids
     * @return array|bool
     * @throws \Exception
     */
    public function esBulkDelete($ids)
    {
        try {
            $attributes = [];
            foreach ($ids as $id) {
                $attributes['body'][] = [
                    'delete' => [
                        '_index' => $this->esIndex,
                        '_type'  => $this->esType,
                        '_id'    => $id
                    ]
                ];
            }

            return self::esInstance()->bulk($attributes);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * ES 聚合查询
     * @param $filter
     * @param int $from
     * @param null $size
     * @return array
     * @throws \Exception
     */
    public function esAggsSearch($filter, $from = 0, $size = null)
    {
        try {
            $filter['aggs'] = $filter['aggs'] ?? ['_' => ['sum' => ['field' => '_']]];
            $filter = [
                'index' => $this->esIndex, 'type' => $this->esType, 'from' => $from,
                'body'  => $filter
            ];
            $filter['size'] = $size ?: 50;

            Log::info('ES Search Filter:' . json_encode(compact('filter', 'from', 'size')));

            $resources = self::esInstance()->search($filter);
            $hits = $this->processESHits($resources['hits']['hits']);
            $aggs = $this->processESAggs($resources['aggregations']);

            return compact('hits', 'aggs');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getESIndex()
    {
        return $this->esIndex;
    }

    public function getESType()
    {
        return $this->esIndex;
    }

    private function processESHits($hits)
    {
        $results = array_map(function ($hit) {
            return array_merge($hit['_source'], ['id' => $hit['_id']]);
        }, $hits);

        return $results;
    }

    private function processESAggs($aggs)
    {
        $result = [];
        foreach ($aggs as $key => $agg) {
            $result[$key] = $agg['value'] ?? $agg;
        }

        return $result;
    }

}