<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas\Nominatim;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class AddressData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?string $houseNumber = null,
        public readonly ?string $road = null,
        public readonly ?string $neighbourhood = null,
        public readonly ?string $suburb = null,
        public readonly ?string $cityDistrict = null,
        public readonly ?string $city = null,
        public readonly ?string $municipality = null,
        public readonly ?string $county = null,
        public readonly ?string $stateDistrict = null,
        public readonly ?string $state = null,
        public readonly ?string $iso3166Lvl4 = null,
        public readonly ?string $postcode = null,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
    ) {}
}
