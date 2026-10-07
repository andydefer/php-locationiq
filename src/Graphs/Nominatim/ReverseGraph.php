<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs\Nominatim;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpLocationIq\ValueObjects\BoundingBoxVO;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class ReverseGraph extends Graph
{
    public function __construct(
        public readonly ?string $licence = null,
        public readonly ?string $osm_type = null,
        public readonly ?int $osm_id = null,
        public readonly ?LocationVO $location = null,
        public readonly ?string $category = null,
        public readonly ?string $type = null,
        public readonly ?int $place_rank = null,
        public readonly ?float $importance = null,
        public readonly ?string $addresstype = null,
        public readonly ?string $name = null,
        public readonly ?string $display_name = null,
        public readonly ?AddressGraph $address = null,
        public readonly ?BoundingBoxVO $boundingbox = null,
        public readonly ?string $error = null,
    ) {}
}
