<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

enum DirectionsProfile: string
{
    case DRIVING = 'driving';
    case WALKING = 'walking';
    case CYCLING = 'cycling';
}
