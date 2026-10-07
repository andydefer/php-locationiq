<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas\Nominatim;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class ReverseResponseData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?ReverseData $reverse = null,
        public readonly ?string $error = null,
    ) {}
}
