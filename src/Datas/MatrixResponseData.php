<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\PhpLocationIq\Datas\Collections\MatrixWaypointDataCollection;
use AndyDefer\PhpLocationIq\ValueObjects\FloatMatrixVO;

/**
 * Unified payload for a LocationIQ Matrix response.
 *
 * Wraps either the resolved matrices and waypoints on success, or the
 * error message when the request failed.
 *
 * Cells without a route are represented by the sentinel value
 * {@see FloatMatrixVO::NO_ROUTE_SENTINEL}.
 */
final class MatrixResponseData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?FloatMatrixVO $durations,
        public readonly ?FloatMatrixVO $distances,
        public readonly MatrixWaypointDataCollection $sources,
        public readonly MatrixWaypointDataCollection $destinations,
        public readonly ?string $error,
    ) {}
}
