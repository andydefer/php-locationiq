<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs\Nominatim;

use AndyDefer\PhpClient\Abstracts\Graph;

final class BoundingBoxGraph extends Graph
{
    public function __construct(
        public readonly string $min_lat = '',
        public readonly string $max_lat = '',
        public readonly string $min_lon = '',
        public readonly string $max_lon = '',
    ) {}
}
