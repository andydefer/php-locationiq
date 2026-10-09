<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

/**
 * Requested annotations for the LocationIQ Matrix endpoint.
 *
 * The Matrix endpoint can return a duration matrix, a distance matrix,
 * or both. Each annotation maps to one array in the response payload.
 *
 * @see https://locationiq.com/docs#matrix
 */
enum MatrixAnnotation: string
{
    case DURATION = 'duration';
    case DISTANCE = 'distance';

    public function getValue(): string
    {
        return $this->value;
    }
}
