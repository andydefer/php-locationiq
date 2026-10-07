<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\RouteData;

/**
 * @extends AbstractTypedCollection<RouteData>
 */
final class RouteDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(RouteData::class);
    }
}
