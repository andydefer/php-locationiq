<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\DomainStructures\Collections\Utility\FloatTypedCollection;

/**
 * Typed collection representing a full LocationIQ Matrix.
 *
 * Each entry is a {@see FloatTypedCollection} holding one row of the
 * matrix, in row-major order.
 *
 * @extends AbstractTypedCollection<FloatTypedCollection>
 */
final class FloatMatrixCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(FloatTypedCollection::class);
    }
}
