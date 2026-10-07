<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpLocationIq\Collections\RouteGraphCollection;
use AndyDefer\PhpLocationIq\Collections\WaypointGraphCollection;
use AndyDefer\PhpLocationIq\Datas\DirectionsResponseData;

/**
 * Contract for the LocationIQ Directions endpoint response.
 *
 * Exposes the normalized waypoints and routes regardless of the raw
 * payload shape (single object or array of objects). A unified payload
 * is available through {@see self::getData()}.
 */
interface DirectionsResponseInterface extends ResponseInterface
{
    /**
     * Returns the raw status code carried by the response payload.
     *
     * LocationIQ returns `ok` on success, or a short error identifier
     * (e.g., `InvalidOptions`) when the request cannot be routed.
     */
    public function getCode(): ?string;

    /**
     * Indicates whether the payload status code is `ok` (case-insensitive).
     */
    public function isOk(): bool;

    /**
     * Returns the waypoints normalized into a typed collection.
     */
    public function getWaypoints(): WaypointGraphCollection;

    /**
     * Returns the routes normalized into a typed collection.
     */
    public function getRoutes(): RouteGraphCollection;

    /**
     * Returns the error identifier when the response is not `ok`, or `null`.
     */
    public function getError(): ?string;

    /**
     * Indicates whether the response carries an error identifier.
     */
    public function hasError(): bool;

    /**
     * Returns a unified DTO wrapping either the directions data or the error.
     */
    public function getData(): DirectionsResponseData;
}
