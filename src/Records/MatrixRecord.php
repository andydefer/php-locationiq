<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\ValueObjects\MatrixOptionsVO;

/**
 * Input record for the LocationIQ Matrix endpoint.
 *
 * Pure data carrier: validation and serialisation are delegated to
 * {@see MatrixOptionsVO}.
 *
 * @see https://locationiq.com/docs#matrix
 */
final class MatrixRecord extends AbstractRecord
{
    public function __construct(
        public readonly MatrixOptionsVO $options,
        public readonly DirectionsProfile $profile = DirectionsProfile::DRIVING,
    ) {}
}
