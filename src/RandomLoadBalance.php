<?php

namespace ZanPHP\LoadBalance;


use ZanPHP\Contracts\LoadBalance\Node;


/**
 * Class RandomLoadBalance
 * @package ZanPHP\LoadBalance
 *
 * 随机：
 * 优点: 简单、无状态、平滑
 * 缺点: 不够均衡
 */
class RandomLoadBalance extends AbstractLoadBalance
{

    /**
     * @param Node[] $nodes
     * @return string
     */
    protected function doSelect(array $nodes)
    {
        assert(count($nodes) > 1);

        $totalWeight = 0; // 总权重
        $sameWeight = true; // 权重是否都一样


        $lastWeight = null;
        foreach ($nodes as $node) {
            $weight = $node->getWeight();
            $totalWeight += $weight; // 累计总权重

            if ($lastWeight !== null && $sameWeight && $weight !== $lastWeight) {
                $sameWeight = false;
            }
            $lastWeight = $weight;
        }

        if ($totalWeight > 0 && ! $sameWeight) {
            // 如果权重不相同且权重大于0则按总权重数随机
            $offset = mt_rand(0, $totalWeight - 1);

            // 并确定随机值落在哪个片断上
            foreach ($nodes as $node) {
                $offset -= $node->getWeight();
                if ($offset < 0) {
                    return $node;
                }
            }
        }

        // 如果权重相同或权重为0则均等随机
        return $nodes[array_rand($nodes)];
    }
}