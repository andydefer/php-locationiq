<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

enum LocationIqBaseUrl: string
{
    case US1 = 'https://us1.locationiq.com';
    case EU1 = 'https://eu1.locationiq.com';
}
