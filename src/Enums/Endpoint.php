<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

use AndyDefer\PhpClient\Enums\HttpMethod;

enum Endpoint: string
{
    case TIMEZONE = '/v1/timezone';
    case DIRECTIONS = '/v1/directions/{profile}/{coordinates}';
    case BALANCE = '/v1/balance';

    public function method(): HttpMethod
    {
        return match ($this) {
            self::TIMEZONE,
            self::DIRECTIONS,
            self::BALANCE => HttpMethod::GET,
        };
    }

    /**
     * @param  array<string, string>  $params
     */
    public function withParameters(array $params = []): string
    {
        $path = $this->value;

        foreach ($params as $key => $value) {
            $path = str_replace('{'.$key.'}', $value, $path);
        }

        return $path;
    }
}
