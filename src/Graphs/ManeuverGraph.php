<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class ManeuverGraph extends Graph
{
    public function __construct(
        public readonly int $bearing_after = 0,
        public readonly int $bearing_before = 0,
        public readonly ?LocationVO $location = null,
        public readonly string $modifier = '',
        public readonly string $type = '',
    ) {}
}
