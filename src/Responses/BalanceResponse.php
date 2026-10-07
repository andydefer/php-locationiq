<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpLocationIq\Contracts\Responses\BalanceResponseInterface;
use AndyDefer\PhpLocationIq\Datas\BalanceResponseData;

/**
 * HTTP response for the LocationIQ Balance endpoint.
 *
 * The endpoint returns either a success payload carrying the remaining
 * request credits for the current UTC day, or an error payload carrying
 * a human-readable error message.
 *
 * @see https://locationiq.com/docs#balance
 */
final class BalanceResponse extends Response implements BalanceResponseInterface
{
    /**
     * Expected value of the `status` field on a successful response.
     */
    private const OK_STATUS = 'ok';

    /**
     * {@inheritDoc}
     */
    public function getStatus(): ?string
    {
        $data = $this->getBody()->format();

        return $data['status'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function isOk(): bool
    {
        $status = $this->getStatus();

        return $status !== null && strtolower($status) === self::OK_STATUS;
    }

    /**
     * {@inheritDoc}
     */
    public function getDayBalance(): ?int
    {
        $data = $this->getBody()->format();

        $day = $data['balance']['day'] ?? null;

        return $day !== null ? (int) $day : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): ?string
    {
        $data = $this->getBody()->format();

        return $data['error'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasError(): bool
    {
        return $this->getError() !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): BalanceResponseData
    {
        if ($this->hasError()) {
            return BalanceResponseData::from([
                'error' => $this->getError(),
            ]);
        }

        return BalanceResponseData::from([
            'balance' => [
                'day' => $this->getDayBalance() ?? 0,
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }
}
