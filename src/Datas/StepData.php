<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Collections\IntersectionDataCollection;

final class StepData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly float $distance,
        public readonly float $duration,
        public readonly float $weight,
        public readonly string $geometry,
        public readonly string $name,
        public readonly string $mode,
        public readonly string $drivingSide,
        public readonly ?ManeuverData $maneuver,
        public readonly ?IntersectionDataCollection $intersections,
    ) {}
}
