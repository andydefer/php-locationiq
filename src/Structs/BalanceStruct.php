<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Structs;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpLocationIq\Graphs\BalanceGraph;

final class BalanceStruct extends Struct
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?BalanceGraph $balance = null,
        public readonly ?string $error = null,
    ) {}
}
