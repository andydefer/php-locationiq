<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Records\Nominatim;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Enums\NominatimFormat;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;

final class ReverseRecord extends AbstractRecord
{
    use Hydratable;

    public function __construct(
        public readonly CoordinatesVO $coordinates,
        public readonly NominatimFormat $format = NominatimFormat::JSONV2,
        public readonly ?string $acceptLanguage = null,
        public readonly ?int $zoom = null,
        public readonly ?bool $addressDetails = null,
    ) {}
}
