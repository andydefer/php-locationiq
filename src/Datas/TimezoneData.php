<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class TimezoneData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly string $name,
        public readonly bool $nowInDst,
        public readonly int $offsetSeconds,
        public readonly string $shortName,
        public readonly string $fullName,
    ) {}
}
