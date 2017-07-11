<?php

namespace ZanPHP\LoadBalance;


use ZanPHP\Contracts\LoadBalance\Node;

/**
 * Class DubboRoundRobinLoadBalance
 * @package ZanPHP\LoadBalance
 *
 * 优点：均衡
 * 缺点：有状态, 不平滑(连续落在权重大的节点!!!)
 */
class DubboRoundRobinLoadBalance extends AbstractLoadBalance
{
    private $currentIndex = -1;

    /**
     * @param Node[] $nodes
     * @return Node|null
     */
    protected function doSelect(array $nodes)
    {
        $this->currentIndex++;

        $weights = [];
        foreach ($nodes as $index => $node) {
            $weights[$index] = $node->getWeight();
        }

        $maxWeight = $this->maxWeight($weights); // 最大权重
        $minWeight = $this->minWeight($weights); // 最小权重
        $weightSum = array_sum($weights);

        if ($maxWeight > 0 && $minWeight < $maxWeight) { // 权重不一样
            $mod = $this->currentIndex % $weightSum;

            for ($i = 0; $i < $maxWeight; $i++) {
                foreach ($weights as $index => $weight) {
                    if ($mod === 0 && $weight > 0) {
                        return $nodes[$index];
                    }

                    if ($weight > 0) {
                        $weights[$index]--;
                        $mod--;
                    }
                }
            }
        }

        // 取模轮循
        return $nodes[$this->currentIndex % count($nodes)];
    }

    private function minWeight(array $weights)
    {
        return min(...$weights);
    }

    private function maxWeight(array $weights)
    {
        return max(...$weights);
    }
}