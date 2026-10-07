<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;

final class TimezoneRecord extends AbstractRecord
{
    use Hydratable;

    public function __construct(
        public readonly CoordinatesVO $coordinates,
        public readonly ?int $timestamp = null,
    ) {}
}
