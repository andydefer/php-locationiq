<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

use AndyDefer\PhpClient\Enums\HttpMethod;

enum NominatimEndpoint: string
{
    case REVERSE = '/reverse';

    public function method(): HttpMethod
    {
        return HttpMethod::GET;
    }
}
