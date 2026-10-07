<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Graphs\StepGraph;

/**
 * @extends AbstractTypedCollection<StepGraph>
 */
final class StepGraphCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(StepGraph::class);
    }
}
