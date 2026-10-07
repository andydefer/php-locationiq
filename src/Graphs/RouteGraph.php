<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\Collections\LegGraphCollection;

final class RouteGraph extends Graph
{
    public function __construct(
        public readonly LegGraphCollection $legs,
        public readonly string $weight_name = '',
        public readonly string $geometry = '',
        public readonly float $weight = 0.0,
        public readonly float $distance = 0.0,
        public readonly float $duration = 0.0,
    ) {}
}
