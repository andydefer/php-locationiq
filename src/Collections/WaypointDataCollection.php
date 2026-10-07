<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\WaypointData;

/**
 * @extends AbstractTypedCollection<WaypointData>
 */
final class WaypointDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(WaypointData::class);
    }
}
