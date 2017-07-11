<?php

namespace ZanPHP\LoadBalance;
use ZanPHP\Contracts\LoadBalance\Node;


/**
 * Class SmoothRoundRobinLoadBalance
 * @package ZanPHP\LoadBalance
 *
 * 平滑轮询 smooth
 * 优点：均衡, 平滑
 * 缺点：有状态
 *
 * https://github.com/phusion/nginx/commit/27e94984486058d73157038f7950a0a36ecc6e35
 * http://colobu.com/2016/12/04/smooth-weighted-round-robin-algorithm/
 */
class NginxRoundRobinLoadBalance extends AbstractLoadBalance
{

    /**
     * @param Node[] $nodes
     * @return Node|null
     */
    protected function doSelect(array $nodes)
    {
        $total = 0;

        /** @var Node $selected */
        $selected = null;

        foreach ($nodes as $node) {
            $weight = $node->getWeight();

            if (!isset($node->effectiveWeight)) {
                $node->effectiveWeight = $weight;
            }
            if (!isset($node->currentWeight)) {
                $node->currentWeight = 0;
            }


            $node->currentWeight += $node->effectiveWeight;
            $total += $node->effectiveWeight;

            if ($node->effectiveWeight < $weight) {
                $node->effectiveWeight++;
            }

            if ($selected === null || $node->currentWeight > $selected->currentWeight ) {
                $selected = $node;
            }
        }

        if ($selected === null) {
            return null;
        } else {
            $selected->currentWeight -= $total;
            return $selected;
        }
    }
}