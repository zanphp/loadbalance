<?php

namespace ZanPHP\LoadBalance;



use ZanPHP\Contracts\LoadBalance\LoadBalancer;
use ZanPHP\Contracts\LoadBalance\Node;

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../../contracts/vendor/autoload.php";

error_reporting(E_ALL);


class XClient implements Node
{
    use WarmupNodeTrait;

    public $name;

    public function __construct($name, $weight)
    {
        $this->name = $name;
        $this->weight = $weight;
    }
}

function testLB(LoadBalancer $lb)
{
    $N = 50;

    $nodeA = new XClient("A", 10);
    $nodeB = new XClient("B", 10);
    $nodeC = new XClient("C", 80);

    $map = [
        "A" => 0,
        "B" => 0,
        "C" => 0,
    ];

    $class = strrchr(get_class($lb), "\\");
    echo "$class\n";


    $n = $N;
    while ($n--) {
        $node = $lb->select([$nodeA, $nodeB, $nodeC]);
        /** @var XClient $node */
        $value = $node->name;
        echo $value;
        $map[$value]++;
    }
    echo "\n";

    foreach ($map as $char => $n) {
        printf("%%%.2f ", $n / $N);
    }

    echo "\n\n";
}

testLB(new RandomLoadBalance());
testLB(new LVSRoundRobinLoadBalance());
testLB(new NginxRoundRobinLoadBalance());
testLB(new DubboRoundRobinLoadBalance());