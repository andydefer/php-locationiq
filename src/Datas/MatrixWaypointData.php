<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

/**
 * Read-model describing a resolved waypoint in a Matrix response.
 */
final class MatrixWaypointData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?string $name,
        public readonly ?float $distance,
        public readonly ?float $longitude,
        public readonly ?float $latitude,
        public readonly ?string $hint,
    ) {}
}
