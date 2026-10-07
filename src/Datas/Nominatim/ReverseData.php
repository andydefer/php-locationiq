<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas\Nominatim;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\ValueObjects\BoundingBoxVO;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class ReverseData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly string $licence,
        public readonly string $osmType,
        public readonly int $osmId,
        public readonly ?LocationVO $location,
        public readonly string $category,
        public readonly string $type,
        public readonly int $placeRank,
        public readonly float $importance,
        public readonly string $addressType,
        public readonly string $name,
        public readonly string $displayName,
        public readonly ?AddressData $address,
        public readonly ?BoundingBoxVO $boundingBox,
    ) {}
}
