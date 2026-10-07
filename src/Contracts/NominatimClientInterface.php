<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts;

use AndyDefer\PhpLocationIq\Contracts\Responses\ReverseResponseInterface;
use AndyDefer\PhpLocationIq\Enums\NominatimBaseUrl;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;

/**
 * Contract for the Nominatim API client.
 *
 * Defines the public surface exposed to consumers of the Nominatim part of
 * the package. Implementations are responsible for dispatching HTTP requests
 * to a Nominatim instance and returning typed response objects.
 *
 * @see https://nominatim.org/release-docs/latest/api/Overview/
 */
interface NominatimClientInterface
{
    /**
     * Switches the client to a different Nominatim base URL.
     *
     * @param  NominatimBaseUrl  $baseUrl  Nominatim base URL (e.g., PUBLIC).
     * @return self The same client instance, for method chaining.
     */
    public function setBaseUrl(NominatimBaseUrl $baseUrl): self;

    /**
     * Overrides the User-Agent header sent with every request.
     *
     * Nominatim's usage policy requires an identifiable User-Agent.
     *
     * @param  string  $userAgent  User-Agent string.
     * @return self The same client instance, for method chaining.
     */
    public function setUserAgent(string $userAgent): self;

    /**
     * Performs a reverse geocoding request.
     *
     * Resolves an address from a coordinate pair.
     *
     * @param  ReverseRecord  $record  Coordinates and query options.
     */
    public function reverse(ReverseRecord $record): ReverseResponseInterface;
}
