<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class TimezoneResponseData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?TimezoneData $timezone = null,
        public readonly ?string $error = null,
    ) {}
}
