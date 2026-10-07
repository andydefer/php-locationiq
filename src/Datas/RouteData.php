<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Collections\LegDataCollection;

final class RouteData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly LegDataCollection $legs,
        public readonly string $weightName,
        public readonly string $geometry,
        public readonly float $weight,
        public readonly float $distance,
        public readonly float $duration,
    ) {}
}
