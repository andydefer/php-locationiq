<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts;

use AndyDefer\PhpLocationIq\Contracts\Responses\BalanceResponseInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\DirectionsResponseInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\TimezoneResponseInterface;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;

/**
 * Contract for the LocationIQ API client.
 *
 * Defines the public surface exposed to consumers of the package.
 * Implementations are responsible for dispatching HTTP requests to
 * LocationIQ and returning typed response objects.
 *
 * @see https://locationiq.com/docs
 */
interface LocationIqClientInterface
{
    /**
     * Switches the client to a different regional API base URL.
     *
     * @param  LocationIqBaseUrl  $baseUrl  Regional base URL (e.g., US1, EU1).
     * @return self The same client instance, for method chaining.
     */
    public function setBaseUrl(LocationIqBaseUrl $baseUrl): self;

    /**
     * Resolves the time zone information for a given coordinate pair.
     *
     * @param  TimezoneRecord  $record  Coordinates and optional timestamp.
     */
    public function getTimezone(TimezoneRecord $record): TimezoneResponseInterface;

    /**
     * Computes a route between two or more coordinates.
     *
     * @param  DirectionsRecord  $record  Routing parameters and coordinates.
     */
    public function getDirections(DirectionsRecord $record): DirectionsResponseInterface;

    /**
     * Returns the remaining request credits for the current UTC day.
     */
    public function getBalance(): BalanceResponseInterface;
}
