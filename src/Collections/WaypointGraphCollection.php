<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Graphs\WaypointGraph;

/**
 * @extends AbstractTypedCollection<WaypointGraph>
 */
final class WaypointGraphCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(WaypointGraph::class);
    }
}
