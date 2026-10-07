<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class WaypointData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly float $distance,
        public readonly ?LocationVO $location,
        public readonly string $name,
    ) {}
}
