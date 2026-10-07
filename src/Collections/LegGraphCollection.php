<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Graphs\LegGraph;

/**
 * @extends AbstractTypedCollection<LegGraph>
 */
final class LegGraphCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(LegGraph::class);
    }
}
