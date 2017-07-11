<?php

namespace ZanPHP\LoadBalance;


use ZanPHP\Contracts\LoadBalance\Node;
use ZanPHP\Contracts\LoadBalance\LoadBalancer;

abstract class AbstractLoadBalance implements LoadBalancer
{

    /**
     * @param Node[] $nodes
     * @return null|Node
     */
    public function select(array $nodes)
    {
        // 这里影响有状态的lb
        $availableNodes = [];
        foreach ($nodes as $node) {
            if ($node->getWeight() > 0) {
                $availableNodes[] = $node;
            }
        }

        $nodes = $availableNodes;
        if (empty($nodes)) {
            return null;
        }

        if (count($nodes) === 1) {
            return $nodes[0];
        }

        return $this->doSelect($nodes);
    }

    /**
     * @param Node[] $nodes
     * @return Node|null
     */
    abstract protected function doSelect(array $nodes);
}