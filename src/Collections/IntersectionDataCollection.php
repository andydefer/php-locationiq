<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\IntersectionData;

/**
 * @extends AbstractTypedCollection<IntersectionData>
 */
final class IntersectionDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(IntersectionData::class);
    }
}
