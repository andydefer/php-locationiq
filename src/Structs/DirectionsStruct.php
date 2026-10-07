<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Structs;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpLocationIq\Graphs\RouteGraph;
use AndyDefer\PhpLocationIq\Graphs\WaypointGraph;

final class DirectionsStruct extends Struct
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?WaypointGraph $waypoint = null,
        public readonly ?RouteGraph $route = null,
    ) {}
}
