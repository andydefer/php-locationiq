<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Collections\RouteDataCollection;
use AndyDefer\PhpLocationIq\Collections\WaypointDataCollection;

final class DirectionsData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly string $code,
        public readonly WaypointDataCollection $waypoints,
        public readonly RouteDataCollection $routes,
    ) {}
}
