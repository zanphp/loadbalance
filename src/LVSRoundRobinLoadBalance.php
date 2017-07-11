<?php

namespace ZanPHP\LoadBalance;

use ZanPHP\Contracts\LoadBalance\Node;


/**
 * Class RoundRobinLoadBalance
 * @package ZanPHP\LoadBalance
 *
 * 轮询:
 * 优点：均衡
 * 缺点：有状态(可以当做无状态使用), 不平滑(连续落在权重大的节点!!!)
 */
class LVSRoundRobinLoadBalance extends AbstractLoadBalance
{
    private $currentIndex = -1;

    private $currentWeight = 0;

    /**
     * @param Node[] $nodes
     * @return Node|null
     */
    protected function doSelect(array $nodes)
    {
        assert (count($nodes) > 1);

        $weights = [];
        foreach ($nodes as $node) {
            $weights[] = $node->getWeight();
        }

		$index = $this->roundRobin($weights);
        if ($index < 0) {
            $index = array_rand($nodes);
        }
        return $nodes[$index];
    }

    private function roundRobin(array $weights)
    {
        $nodeSize = count($weights);
        $gcdWeights = $this->gcdWeights($weights);
        $maxWeight = $this->maxWeight($weights);

        if ($this->currentIndex >= $nodeSize) {
            $this->currentIndex = $nodeSize - 1;
        }

        while (true) {
            $this->currentIndex = ($this->currentIndex + 1) % $nodeSize;

            if ($this->currentIndex === 0) {
                $this->currentWeight = $this->currentWeight - $gcdWeights;
                if ($this->currentWeight <= 0) {
                    $this->currentWeight = $maxWeight;
                    if ($this->currentWeight === 0) {
                        return -1;
                    }
                }
            }

            if ($weights[$this->currentIndex] >= $this->currentWeight) {
                return $this->currentIndex;
            }
        }
    }

    private function maxWeight(array $weights)
    {
        return max(...$weights);
    }

    private function gcdWeights(array $weights)
    {
		return $this->gcdN($weights, count($weights));
	}

    private function gcd($a, $b)
    {
        if ($b === 0) {
            return $a;
        } else {
            return $this->gcd($b, $a % $b);
        }
    }

    private function gcdN(array $digits, $length)
    {
		if ($length === 1) {
			return $digits[0];
		} else {
            return $this->gcd($digits[$length - 1], $this->gcdN($digits, $length - 1));
        }
    }
}