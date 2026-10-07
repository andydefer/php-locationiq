<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\Collections\IntersectionGraphCollection;

final class StepGraph extends Graph
{
    public function __construct(
        public readonly float $distance = 0.0,
        public readonly float $duration = 0.0,
        public readonly float $weight = 0.0,
        public readonly string $geometry = '',
        public readonly string $name = '',
        public readonly string $mode = '',
        public readonly string $driving_side = '',
        public readonly ?ManeuverGraph $maneuver = null,
        public readonly ?IntersectionGraphCollection $intersections = null,
    ) {}
}
