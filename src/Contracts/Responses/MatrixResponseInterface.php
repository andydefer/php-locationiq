<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpLocationIq\Datas\Collections\MatrixWaypointDataCollection;
use AndyDefer\PhpLocationIq\Datas\MatrixResponseData;
use AndyDefer\PhpLocationIq\ValueObjects\FloatMatrixVO;

/**
 * Contract for the LocationIQ Matrix endpoint response.
 *
 * Exposes the duration and distance matrices (row-major order), plus the
 * resolved sources and destinations. A unified payload is available
 * through {@see self::getData()}.
 *
 * Cells without a route are represented by the sentinel value
 * {@see FloatMatrixVO::NO_ROUTE_SENTINEL}.
 *
 * @see https://locationiq.com/docs#matrix
 */
interface MatrixResponseInterface extends ResponseInterface
{
    /**
     * Returns the raw status code string carried by the response payload
     * (`Ok`, `NoTable`, `NotImplemented`, ...).
     */
    public function getCode(): ?string;

    /**
     * Indicates whether the payload status is `Ok` (case-insensitive).
     */
    public function isOk(): bool;

    /**
     * Returns the durations matrix in seconds, or null when the response
     * does not include it.
     */
    public function getDurations(): ?FloatMatrixVO;

    /**
     * Returns the distances matrix in meters, or null when the response
     * does not include it.
     */
    public function getDistances(): ?FloatMatrixVO;

    /**
     * Returns the resolved source waypoints.
     */
    public function getSources(): MatrixWaypointDataCollection;

    /**
     * Returns the resolved destination waypoints.
     */
    public function getDestinations(): MatrixWaypointDataCollection;

    /**
     * Returns the error message when the response carries one, or null.
     */
    public function getError(): ?string;

    /**
     * Indicates whether the response carries an error.
     */
    public function hasError(): bool;

    /**
     * Returns a unified DTO wrapping either the matrices or the error.
     */
    public function getData(): MatrixResponseData;
}
