<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Graphs\Nominatim;

use AndyDefer\PhpClient\Abstracts\Graph;

final class AddressGraph extends Graph
{
    public function __construct(
        public readonly ?string $house_number = null,
        public readonly ?string $road = null,
        public readonly ?string $neighbourhood = null,
        public readonly ?string $suburb = null,
        public readonly ?string $city_district = null,
        public readonly ?string $city = null,
        public readonly ?string $municipality = null,
        public readonly ?string $county = null,
        public readonly ?string $state_district = null,
        public readonly ?string $state = null,
        public readonly ?string $ISO3166_2_lvl4 = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $country_code = null,
    ) {}
}
