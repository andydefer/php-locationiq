<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

enum NominatimFormat: string
{
    case JSON = 'json';
    case JSONV2 = 'jsonv2';
    case GEOJSON = 'geojson';
    case GEOCODEJSON = 'geocodejson';

    public static function default(): self
    {
        return self::JSONV2;
    }
}
