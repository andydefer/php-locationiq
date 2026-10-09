<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Datas\MatrixWaypointData;

/**
 * @extends AbstractTypedCollection<MatrixWaypointData>
 */
final class MatrixWaypointDataCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(MatrixWaypointData::class);
    }
}
