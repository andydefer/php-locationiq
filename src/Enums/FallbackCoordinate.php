<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

/**
 * Coordinate source used when the LocationIQ Matrix endpoint falls back
 * to as-the-crow-flies estimation for an unroutable pair.
 *
 * @see https://locationiq.com/docs#matrix
 */
enum FallbackCoordinate: string
{
    case INPUT = 'input';
    case SNAPPED = 'snapped';

    public function getValue(): string
    {
        return $this->value;
    }
}
