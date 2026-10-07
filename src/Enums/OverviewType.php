<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Enums;

enum OverviewType: string
{
    case SIMPLIFIED = 'simplified';
    case FULL = 'full';
    case FALSE = 'false';

    public static function default(): self
    {
        return self::SIMPLIFIED;
    }

    public function isFalse(): bool
    {
        return $this === self::FALSE;
    }
}
