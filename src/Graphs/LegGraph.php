<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\Collections\StepGraphCollection;

final class LegGraph extends Graph
{
    public function __construct(
        public readonly StepGraphCollection $steps,
        public readonly float $weight = 0.0,
        public readonly float $distance = 0.0,
        public readonly float $duration = 0.0,
        public readonly string $summary = '',
    ) {}
}
