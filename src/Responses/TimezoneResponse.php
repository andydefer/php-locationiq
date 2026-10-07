<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpLocationIq\Contracts\Responses\TimezoneResponseInterface;
use AndyDefer\PhpLocationIq\Datas\TimezoneResponseData;

/**
 * HTTP response for the LocationIQ Timezone endpoint.
 *
 * Exposes the resolved time zone information (name, offset, DST state)
 * or the error message returned by LocationIQ when the request fails.
 *
 * @see https://locationiq.com/docs#timezone
 */
final class TimezoneResponse extends Response implements TimezoneResponseInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->timezone()['name'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function isInDst(): bool
    {
        return (int) ($this->timezone()['now_in_dst'] ?? 0) === 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getOffsetSeconds(): ?int
    {
        $value = $this->timezone()['offset_sec'] ?? null;

        return $value !== null ? (int) $value : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getShortName(): ?string
    {
        return $this->timezone()['short_name'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFullName(): ?string
    {
        return $this->timezone()['full_name'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): ?string
    {
        return $this->payload()['error'] ?? null;
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
    public function getData(): TimezoneResponseData
    {
        if ($this->hasError()) {
            return TimezoneResponseData::from([
                'error' => $this->getError(),
            ]);
        }

        return TimezoneResponseData::from([
            'timezone' => [
                'name' => $this->getName() ?? '',
                'nowInDst' => $this->isInDst(),
                'offsetSeconds' => $this->getOffsetSeconds() ?? 0,
                'shortName' => $this->getShortName() ?? '',
                'fullName' => $this->getFullName() ?? '',
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

    /**
     * Returns the decoded response body as an associative array.
     *
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return $this->getBody()->format();
    }

    /**
     * Returns the `timezone` sub-object from the payload, or an empty array.
     *
     * @return array<string, mixed>
     */
    private function timezone(): array
    {
        /** @var array<string, mixed> $timezone */
        $timezone = $this->payload()['timezone'] ?? [];

        return $timezone;
    }
}
