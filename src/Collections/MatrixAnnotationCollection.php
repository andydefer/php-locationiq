<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpLocationIq\Enums\MatrixAnnotation;

/**
 * @extends AbstractTypedCollection<MatrixAnnotation>
 */
final class MatrixAnnotationCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(MatrixAnnotation::class);
    }
}
