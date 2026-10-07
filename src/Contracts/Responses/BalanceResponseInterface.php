<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpLocationIq\Datas\BalanceResponseData;

/**
 * Contract for the LocationIQ Balance endpoint response.
 *
 * Exposes the remaining request credits for the current UTC day and,
 * when the request fails, the error message returned by LocationIQ.
 * A unified payload is available through {@see self::getData()}.
 */
interface BalanceResponseInterface extends ResponseInterface
{
    /**
     * Returns the raw status string carried by the response payload.
     */
    public function getStatus(): ?string;

    /**
     * Indicates whether the payload status is `ok` (case-insensitive).
     */
    public function isOk(): bool;

    /**
     * Returns the number of request credits remaining for the current UTC day.
     */
    public function getDayBalance(): ?int;

    /**
     * Returns the error message when the response carries one, or `null`.
     */
    public function getError(): ?string;

    /**
     * Indicates whether the response carries an error message.
     */
    public function hasError(): bool;

    /**
     * Returns a unified DTO wrapping either the balance data or the error.
     */
    public function getData(): BalanceResponseData;
}
