<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Structs;

use AndyDefer\PhpClient\Abstracts\Struct;

final class TimezoneStruct extends Struct
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $now_in_dst = null,
        public readonly ?int $offset_sec = null,
        public readonly ?string $short_name = null,
        public readonly ?string $full_name = null,
    ) {}
}
