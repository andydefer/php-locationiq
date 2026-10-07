<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\GeometriesType;
use AndyDefer\PhpLocationIq\Enums\OverviewType;

final class DirectionsRecord extends AbstractRecord
{
    use Hydratable;

    public function __construct(
        public readonly LocationVOCollection $coordinates,
        public readonly DirectionsProfile $profile = DirectionsProfile::DRIVING,
        public readonly OverviewType $overview = OverviewType::SIMPLIFIED,
        public readonly bool $steps = false,
        public readonly bool $alternatives = false,
        public readonly GeometriesType $geometries = GeometriesType::POLYLINE,
    ) {}
}
