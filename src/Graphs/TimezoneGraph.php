<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class TimezoneGraph extends Graph
{
    public function __construct(
        public readonly string $name = '',
        public readonly string $short = '',
        public readonly int $utc_offset = 0,
        public readonly int $timestamp = 0,
    ) {}
}
