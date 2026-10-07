<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpLocationIq\Datas\TimezoneResponseData;

/**
 * Contract for the LocationIQ Timezone endpoint response.
 *
 * Exposes the resolved time zone fields and, when the request fails,
 * the error message returned by LocationIQ. A unified payload is
 * available through {@see self::getData()}.
 */
interface TimezoneResponseInterface extends ResponseInterface
{
    /**
     * Returns the IANA time zone name (e.g., `Asia/Kolkata`).
     */
    public function getName(): ?string;

    /**
     * Indicates whether the location is currently observing daylight saving time.
     */
    public function isInDst(): bool;

    /**
     * Returns the UTC offset in seconds (e.g., `19800` for IST).
     */
    public function getOffsetSeconds(): ?int;

    /**
     * Returns the abbreviated time zone name (e.g., `IST`).
     */
    public function getShortName(): ?string;

    /**
     * Returns the full time zone name (e.g., `India Standard Time`).
     */
    public function getFullName(): ?string;

    /**
     * Returns the error message when the response carries one, or `null`.
     */
    public function getError(): ?string;

    /**
     * Indicates whether the response carries an error message.
     */
    public function hasError(): bool;

    /**
     * Returns a unified DTO wrapping either the time zone data or the error.
     */
    public function getData(): TimezoneResponseData;
}
