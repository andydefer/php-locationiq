<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpVo\ValueObjects\LocationVO;

final class WaypointGraph extends Graph
{
    public function __construct(
        public readonly float $distance = 0.0,
        public readonly ?LocationVO $location = null,
        public readonly string $name = '',
    ) {}
}
