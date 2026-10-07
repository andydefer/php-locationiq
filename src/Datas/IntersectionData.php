<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class IntersectionData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly int $out,
        public readonly ?IntTypedCollection $entry,
        public readonly ?IntTypedCollection $bearings,
        public readonly ?LocationVO $location,
    ) {}
}
