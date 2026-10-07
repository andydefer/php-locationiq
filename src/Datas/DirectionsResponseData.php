<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;

final class DirectionsResponseData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?DirectionsData $directions = null,
        public readonly ?string $error = null,
    ) {}
}
