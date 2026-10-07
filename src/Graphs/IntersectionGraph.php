<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs;

use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class IntersectionGraph extends Graph
{
    public function __construct(
        public readonly int $out = 0,
        public readonly ?IntTypedCollection $entry = null,
        public readonly ?IntTypedCollection $bearings = null,
        public readonly ?LocationVO $location = null,
    ) {}
}
