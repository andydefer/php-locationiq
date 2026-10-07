<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class BalanceData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly int $day,
    ) {}
}
