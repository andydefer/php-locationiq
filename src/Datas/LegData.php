<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Collections\StepDataCollection;

final class LegData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly StepDataCollection $steps,
        public readonly float $weight,
        public readonly float $distance,
        public readonly float $duration,
        public readonly string $summary,
    ) {}
}
