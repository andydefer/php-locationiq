<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Graphs\RouteGraph;

/**
 * @extends AbstractTypedCollection<RouteGraph>
 */
final class RouteGraphCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(RouteGraph::class);
    }
}
