<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Responses;

use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpLocationIq\Collections\IntersectionGraphCollection;
use AndyDefer\PhpLocationIq\Collections\LegGraphCollection;
use AndyDefer\PhpLocationIq\Collections\RouteGraphCollection;
use AndyDefer\PhpLocationIq\Collections\StepGraphCollection;
use AndyDefer\PhpLocationIq\Collections\WaypointGraphCollection;
use AndyDefer\PhpLocationIq\Contracts\Responses\DirectionsResponseInterface;
use AndyDefer\PhpLocationIq\Datas\DirectionsResponseData;
use AndyDefer\PhpLocationIq\Graphs\IntersectionGraph;
use AndyDefer\PhpLocationIq\Graphs\LegGraph;
use AndyDefer\PhpLocationIq\Graphs\ManeuverGraph;
use AndyDefer\PhpLocationIq\Graphs\RouteGraph;
use AndyDefer\PhpLocationIq\Graphs\StepGraph;
use AndyDefer\PhpLocationIq\Graphs\WaypointGraph;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

/**
 * HTTP response for the LocationIQ Directions endpoint.
 *
 * Handles the two shapes returned by LocationIQ: a single object when
 * `alternatives=false`, or an array of objects when alternatives are
 * requested. Both shapes are normalized into typed collections.
 *
 * @see https://locationiq.com/docs#directions
 */
final class DirectionsResponse extends Response implements DirectionsResponseInterface
{
    /**
     * Status code returned by LocationIQ on a successful routing response.
     */
    private const OK_CODE = 'ok';

    /**
     * Expected number of elements in a coordinate pair (`[lon, lat]`).
     */
    private const COORDINATE_PAIR_SIZE = 2;

    /**
     * {@inheritDoc}
     */
    public function getCode(): ?string
    {
        return $this->payload()['code'] ?? null;
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
    public function getWaypoints(): WaypointGraphCollection
    {
        $collection = new WaypointGraphCollection;
        $raw = $this->payload()['waypoints'] ?? null;

        if (! is_array($raw)) {
            return $collection;
        }

        if ($this->isSingleWaypoint($raw)) {
            $collection->add($this->hydrateWaypoint($raw));

            return $collection;
        }

        foreach ($raw as $waypoint) {
            if (is_array($waypoint)) {
                $collection->add($this->hydrateWaypoint($waypoint));
            }
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes(): RouteGraphCollection
    {
        $collection = new RouteGraphCollection;
        $raw = $this->payload()['routes'] ?? null;

        if (! is_array($raw)) {
            return $collection;
        }

        if ($this->isSingleRoute($raw)) {
            $collection->add($this->hydrateRoute($raw));

            return $collection;
        }

        foreach ($raw as $route) {
            if (is_array($route)) {
                $collection->add($this->hydrateRoute($route));
            }
        }

        return $collection;
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
    public function getData(): DirectionsResponseData
    {
        if ($this->hasError()) {
            return DirectionsResponseData::from([
                'error' => $this->getError(),
            ]);
        }

        return DirectionsResponseData::from([
            'directions' => [
                'code' => (string) $this->getCode(),
                'waypoints' => $this->getWaypoints(),
                'routes' => $this->getRoutes(),
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
     * @param  array<string, mixed>  $data
     */
    private function isSingleWaypoint(array $data): bool
    {
        return array_key_exists('location', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isSingleRoute(array $data): bool
    {
        return array_key_exists('legs', $data) || array_key_exists('geometry', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hydrateWaypoint(array $data): WaypointGraph
    {
        return new WaypointGraph(
            distance: (float) ($data['distance'] ?? 0.0),
            location: $this->hydrateLocation($data['location'] ?? null),
            name: (string) ($data['name'] ?? ''),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hydrateRoute(array $data): RouteGraph
    {
        return new RouteGraph(
            legs: $this->hydrateLegs($data['legs'] ?? null),
            weight_name: (string) ($data['weight_name'] ?? ''),
            geometry: (string) ($data['geometry'] ?? ''),
            weight: (float) ($data['weight'] ?? 0.0),
            distance: (float) ($data['distance'] ?? 0.0),
            duration: (float) ($data['duration'] ?? 0.0),
        );
    }

    private function hydrateLegs(mixed $raw): LegGraphCollection
    {
        $collection = new LegGraphCollection;

        if (! is_array($raw)) {
            return $collection;
        }

        if (array_key_exists('steps', $raw)) {
            $collection->add($this->hydrateLeg($raw));

            return $collection;
        }

        foreach ($raw as $leg) {
            if (is_array($leg)) {
                $collection->add($this->hydrateLeg($leg));
            }
        }

        return $collection;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hydrateLeg(array $data): LegGraph
    {
        return new LegGraph(
            steps: $this->hydrateSteps($data['steps'] ?? null),
            weight: (float) ($data['weight'] ?? 0.0),
            distance: (float) ($data['distance'] ?? 0.0),
            duration: (float) ($data['duration'] ?? 0.0),
            summary: (string) ($data['summary'] ?? ''),
        );
    }

    private function hydrateSteps(mixed $raw): StepGraphCollection
    {
        $collection = new StepGraphCollection;

        if (! is_array($raw)) {
            return $collection;
        }

        foreach ($raw as $step) {
            if (is_array($step)) {
                $collection->add($this->hydrateStep($step));
            }
        }

        return $collection;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hydrateStep(array $data): StepGraph
    {
        return new StepGraph(
            distance: (float) ($data['distance'] ?? 0.0),
            duration: (float) ($data['duration'] ?? 0.0),
            weight: (float) ($data['weight'] ?? 0.0),
            geometry: (string) ($data['geometry'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            mode: (string) ($data['mode'] ?? ''),
            driving_side: (string) ($data['driving_side'] ?? ''),
            maneuver: $this->hydrateManeuver($data['maneuver'] ?? null),
            intersections: $this->hydrateIntersections($data['intersections'] ?? null),
        );
    }

    private function hydrateManeuver(mixed $raw): ?ManeuverGraph
    {
        if (! is_array($raw)) {
            return null;
        }

        return new ManeuverGraph(
            bearing_after: (int) ($raw['bearing_after'] ?? 0),
            bearing_before: (int) ($raw['bearing_before'] ?? 0),
            location: $this->hydrateLocation($raw['location'] ?? null),
            modifier: (string) ($raw['modifier'] ?? ''),
            type: (string) ($raw['type'] ?? ''),
        );
    }

    private function hydrateIntersections(mixed $raw): ?IntersectionGraphCollection
    {
        if (! is_array($raw)) {
            return null;
        }

        $collection = new IntersectionGraphCollection;

        foreach ($raw as $intersection) {
            if (is_array($intersection)) {
                $collection->add($this->hydrateIntersection($intersection));
            }
        }

        return $collection;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hydrateIntersection(array $data): IntersectionGraph
    {
        return new IntersectionGraph(
            out: (int) ($data['out'] ?? 0),
            entry: $this->hydrateIntCollection($data['entry'] ?? null),
            bearings: $this->hydrateIntCollection($data['bearings'] ?? null),
            location: $this->hydrateLocation($data['location'] ?? null),
        );
    }

    private function hydrateIntCollection(mixed $raw): ?IntTypedCollection
    {
        if (! is_array($raw)) {
            return null;
        }

        $collection = new IntTypedCollection;

        foreach ($raw as $value) {
            if (is_int($value) || is_numeric($value)) {
                $collection->add((int) $value);
            }
        }

        return $collection;
    }

    private function hydrateLocation(mixed $raw): ?LocationVO
    {
        if (! is_array($raw) || count($raw) !== self::COORDINATE_PAIR_SIZE) {
            return null;
        }

        return LocationVO::fromArray($raw);
    }
}
