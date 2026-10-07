<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\StepData;

/**
 * @extends AbstractTypedCollection<StepData>
 */
final class StepDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(StepData::class);
    }
}
