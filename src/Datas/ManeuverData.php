<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class ManeuverData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly int $bearingAfter,
        public readonly int $bearingBefore,
        public readonly ?LocationVO $location,
        public readonly string $modifier,
        public readonly string $type,
    ) {}
}
