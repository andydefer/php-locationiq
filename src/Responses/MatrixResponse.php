<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Responses;

use AndyDefer\DomainStructures\Collections\Utility\FloatTypedCollection;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpLocationIq\Collections\FloatMatrixCollection;
use AndyDefer\PhpLocationIq\Contracts\Responses\MatrixResponseInterface;
use AndyDefer\PhpLocationIq\Datas\Collections\MatrixWaypointDataCollection;
use AndyDefer\PhpLocationIq\Datas\MatrixResponseData;
use AndyDefer\PhpLocationIq\Datas\MatrixWaypointData;
use AndyDefer\PhpLocationIq\ValueObjects\FloatMatrixVO;

/**
 * HTTP response for the LocationIQ Matrix endpoint.
 *
 * The endpoint returns either a success payload carrying the durations
 * matrix, the distances matrix, or both, plus the resolved sources and
 * destinations, or an error payload carrying a status code such as
 * `NoTable` or `NotImplemented`.
 *
 * @see https://locationiq.com/docs#matrix
 */
final class MatrixResponse extends Response implements MatrixResponseInterface
{
    /**
     * Expected value of the `code` field on a successful response.
     */
    private const OK_CODE = 'ok';

    /**
     * {@inheritDoc}
     */
    public function getCode(): ?string
    {
        $data = $this->getBody()->format();

        return $data['code'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function isOk(): bool
    {
        $code = $this->getCode();

        return $code !== null && strtolower($code) === self::OK_CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getDurations(): ?FloatMatrixVO
    {
        return $this->buildMatrix('durations');
    }

    /**
     * {@inheritDoc}
     */
    public function getDistances(): ?FloatMatrixVO
    {
        return $this->buildMatrix('distances');
    }

    /**
     * {@inheritDoc}
     */
    public function getSources(): MatrixWaypointDataCollection
    {
        return $this->buildWaypoints('sources');
    }

    /**
     * {@inheritDoc}
     */
    public function getDestinations(): MatrixWaypointDataCollection
    {
        return $this->buildWaypoints('destinations');
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): ?string
    {
        return $this->isOk() ? null : $this->getCode();
    }

    /**
     * {@inheritDoc}
     */
    public function hasError(): bool
    {
        return ! $this->isOk();
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): MatrixResponseData
    {
        if ($this->hasError()) {
            return new MatrixResponseData(
                durations: null,
                distances: null,
                sources: new MatrixWaypointDataCollection,
                destinations: new MatrixWaypointDataCollection,
                error: $this->getError(),
            );
        }

        return new MatrixResponseData(
            durations: $this->getDurations(),
            distances: $this->getDistances(),
            sources: $this->getSources(),
            destinations: $this->getDestinations(),
            error: null,
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }

    /**
     * Builds a typed matrix VO from the given payload key.
     *
     * Returns null when the payload does not contain the requested key or
     * when the value is not an array. Cells without a route are represented
     * by {@see FloatMatrixVO::NO_ROUTE_SENTINEL}.
     */
    private function buildMatrix(string $key): ?FloatMatrixVO
    {
        $data = $this->getBody()->format();

        $raw = $data[$key] ?? null;

        if (! is_array($raw)) {
            return null;
        }

        $matrix = new FloatMatrixCollection;

        foreach ($raw as $rawRow) {
            if (! is_array($rawRow)) {
                continue;
            }

            $row = new FloatTypedCollection;

            foreach ($rawRow as $cell) {
                $row->add(
                    $cell === null
                        ? FloatMatrixVO::NO_ROUTE_SENTINEL
                        : (float) $cell
                );
            }

            $matrix->add($row);
        }

        return new FloatMatrixVO($matrix);
    }

    /**
     * Builds a typed collection of waypoints from the given payload key.
     */
    private function buildWaypoints(string $key): MatrixWaypointDataCollection
    {
        $data = $this->getBody()->format();
        $waypoints = $data[$key] ?? null;

        $collection = new MatrixWaypointDataCollection;

        if (! is_array($waypoints)) {
            return $collection;
        }

        foreach ($waypoints as $waypoint) {
            if (! is_array($waypoint)) {
                continue;
            }

            $location = $waypoint['location'] ?? null;
            $longitude = is_array($location) ? ($location[0] ?? null) : null;
            $latitude = is_array($location) ? ($location[1] ?? null) : null;

            $collection->add(new MatrixWaypointData(
                name: $waypoint['name'] ?? null,
                distance: isset($waypoint['distance']) ? (float) $waypoint['distance'] : null,
                longitude: $longitude !== null ? (float) $longitude : null,
                latitude: $latitude !== null ? (float) $latitude : null,
                hint: $waypoint['hint'] ?? null,
            ));
        }

        return $collection;
    }
}
