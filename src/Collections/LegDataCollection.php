<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\LegData;

/**
 * @extends AbstractTypedCollection<LegData>
 */
final class LegDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(LegData::class);
    }
}
