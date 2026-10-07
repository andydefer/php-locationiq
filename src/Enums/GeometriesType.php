<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

enum GeometriesType: string
{
    case POLYLINE = 'polyline';
    case POLYLINE6 = 'polyline6';
    case GEOJSON = 'geojson';

    public static function default(): self
    {
        return self::POLYLINE;
    }
}
