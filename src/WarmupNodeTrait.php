<?php

namespace ZanPHP\LoadBalance;


/**
 * Class WarmupNode
 * @package ZanPHP\LoadBalance
 *
 * 支持权重与预热功能
 */
trait WarmupNodeTrait
{
    protected $weight = 100;

    /**
     * node启动时间
     * 注意: 这里指的是 节点的启动时间,而不是拉取到节点的时间
     * 该事件可以从注册中心获取到
     * @var int|null ms
     */
    protected $uptime = null;

    /**
     * 预热时间
     * @var int|null ms
     */
    protected $warmup = 60 * 10 * 1000;

    /**
     * 直接返回weight 或者返回处理预热的weight
     * @return int
     */
    public function getWeight()
    {
        $weight = $this->weight;
        $uptime = $this->uptime;
        $warmup = $this->warmup;

        if ($weight > 0 && $uptime > 0 && $warmup > 0) {
            // 如果启动时长小于预热时间，则需要降权
            // 权重计算方式为启动时长占预热时间的百分比乘以权重，
            $uptime = microtime(true) * 1000 - $uptime;
            if ($uptime > 0 && $uptime < $warmup) {

                // calculateWarmupWeight
                $weight = (int)min(1, max($weight, $uptime / $warmup * $weight));
            }
        }

        return $weight;
    }
}