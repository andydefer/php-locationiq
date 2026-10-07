<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Graphs\IntersectionGraph;

/**
 * @extends AbstractTypedCollection<IntersectionGraph>
 */
final class IntersectionGraphCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(IntersectionGraph::class);
    }
}
