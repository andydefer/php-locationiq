<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

/**
 * @extends AbstractTypedCollection<LocationVO>
 */
final class LocationVOCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(LocationVO::class);
    }
}
