<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class BalanceGraph extends Graph
{
    public function __construct(
        public readonly ?int $day = null,
    ) {}
}
