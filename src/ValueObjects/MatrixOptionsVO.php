<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\DomainStructures\Utils\StrictAssociative;
use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Collections\MatrixAnnotationCollection;
use AndyDefer\PhpLocationIq\Enums\FallbackCoordinate;
use AndyDefer\PhpLocationIq\Enums\MatrixAnnotation;
use InvalidArgumentException;

/**
 * Immutable Value Object holding the validated options for the LocationIQ
 * Matrix endpoint.
 *
 * Encapsulates the coordinate list, the requested annotations, and the
 * optional sources, destinations, and fallback parameters. Provides the
 * serialisation formats expected by LocationIQ.
 *
 * @see https://locationiq.com/docs#matrix
 */
final class MatrixOptionsVO extends AbstractValueObject
{
    /**
     * Minimum number of coordinates accepted by LocationIQ.
     */
    public const MIN_COORDINATES = 2;

    /**
     * Maximum number of coordinates accepted by LocationIQ.
     */
    public const MAX_COORDINATES = 25;

    private function __construct(
        private readonly LocationVOCollection $coordinates,
        private readonly MatrixAnnotationCollection $annotations,
        private readonly ?IntTypedCollection $sources,
        private readonly ?IntTypedCollection $destinations,
        private readonly ?float $fallbackSpeed,
        private readonly ?FallbackCoordinate $fallbackCoordinate,
    ) {
        $this->assertCoordinatesAreValid();
        $this->assertAnnotationsAreValid();
        $this->assertSourcesAreValid();
        $this->assertDestinationsAreValid();
        $this->assertFallbackSpeedIsValid();
    }

    /**
     * Creates a validated Matrix options value object.
     *
     * When no annotations are provided, `duration` is used as the default.
     *
     * @throws InvalidArgumentException When any option is out of the accepted range.
     */
    public static function create(
        LocationVOCollection $coordinates,
        ?MatrixAnnotationCollection $annotations = null,
        ?IntTypedCollection $sources = null,
        ?IntTypedCollection $destinations = null,
        ?float $fallbackSpeed = null,
        ?FallbackCoordinate $fallbackCoordinate = null,
    ): self {
        return new self(
            coordinates: $coordinates,
            annotations: $annotations ?? self::defaultAnnotations(),
            sources: $sources,
            destinations: $destinations,
            fallbackSpeed: $fallbackSpeed,
            fallbackCoordinate: $fallbackCoordinate,
        );
    }

    public function getCoordinates(): LocationVOCollection
    {
        return $this->coordinates;
    }

    public function getAnnotations(): MatrixAnnotationCollection
    {
        return $this->annotations;
    }

    public function getSources(): ?IntTypedCollection
    {
        return $this->sources;
    }

    public function getDestinations(): ?IntTypedCollection
    {
        return $this->destinations;
    }

    public function getFallbackSpeed(): ?float
    {
        return $this->fallbackSpeed;
    }

    public function getFallbackCoordinate(): ?FallbackCoordinate
    {
        return $this->fallbackCoordinate;
    }

    /**
     * Serialises the coordinates as a semicolon-separated string, in the
     * `longitude,latitude` format expected by LocationIQ routing endpoints.
     */
    public function coordinatesToString(): string
    {
        $segments = [];

        foreach ($this->coordinates as $location) {
            $segments[] = $location->getLongitude().','.$location->getLatitude();
        }

        return implode(';', $segments);
    }

    /**
     * Serialises the annotations as a comma-separated string.
     */
    public function annotationsToString(): string
    {
        $segments = [];

        foreach ($this->annotations as $annotation) {
            $segments[] = $annotation->getValue();
        }

        return implode(',', $segments);
    }

    /**
     * Serialises the sources as a semicolon-separated string, or null when
     * all waypoints are used as sources.
     */
    public function sourcesToString(): ?string
    {
        if ($this->sources === null) {
            return null;
        }

        $segments = [];

        foreach ($this->sources as $index) {
            $segments[] = (string) $index;
        }

        return implode(';', $segments);
    }

    /**
     * Serialises the destinations as a semicolon-separated string, or null
     * when all waypoints are used as destinations.
     */
    public function destinationsToString(): ?string
    {
        if ($this->destinations === null) {
            return null;
        }

        $segments = [];

        foreach ($this->destinations as $index) {
            $segments[] = (string) $index;
        }

        return implode(';', $segments);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): StrictAssociative
    {
        return StrictAssociative::from([
            'coordinates' => $this->coordinatesToString(),
            'annotations' => $this->annotationsToString(),
            'sources' => $this->sourcesToString(),
            'destinations' => $this->destinationsToString(),
            'fallback_speed' => $this->fallbackSpeed,
            'fallback_coordinate' => $this->fallbackCoordinate?->getValue(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function equals(AbstractValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->getValue()->toArray() === $other->getValue()->toArray();
    }

    private static function defaultAnnotations(): MatrixAnnotationCollection
    {
        $collection = new MatrixAnnotationCollection;
        $collection->add(MatrixAnnotation::DURATION);

        return $collection;
    }

    private function assertCoordinatesAreValid(): void
    {
        $count = $this->coordinates->count();

        if ($count < self::MIN_COORDINATES) {
            throw new InvalidArgumentException(sprintf(
                'Matrix requires at least %d coordinates, %d given.',
                self::MIN_COORDINATES,
                $count,
            ));
        }

        if ($count > self::MAX_COORDINATES) {
            throw new InvalidArgumentException(sprintf(
                'Matrix accepts at most %d coordinates, %d given.',
                self::MAX_COORDINATES,
                $count,
            ));
        }
    }

    private function assertAnnotationsAreValid(): void
    {
        if ($this->annotations->isEmpty()) {
            throw new InvalidArgumentException(
                'Matrix requires at least one annotation (duration and/or distance).'
            );
        }
    }

    private function assertSourcesAreValid(): void
    {
        if ($this->sources === null) {
            return;
        }

        $this->assertIndexesAreInRange($this->sources, 'sources');
    }

    private function assertDestinationsAreValid(): void
    {
        if ($this->destinations === null) {
            return;
        }

        $this->assertIndexesAreInRange($this->destinations, 'destinations');
    }

    private function assertIndexesAreInRange(
        IntTypedCollection $indexes,
        string $field,
    ): void {
        $max = $this->coordinates->count() - 1;

        foreach ($indexes as $index) {
            if ($index < 0 || $index > $max) {
                throw new InvalidArgumentException(sprintf(
                    'Matrix %s index %d is out of range [0, %d].',
                    $field,
                    $index,
                    $max,
                ));
            }
        }
    }

    private function assertFallbackSpeedIsValid(): void
    {
        if ($this->fallbackSpeed === null) {
            return;
        }

        if ($this->fallbackSpeed <= 0.0) {
            throw new InvalidArgumentException(sprintf(
                'Matrix fallback_speed must be greater than 0, %s given.',
                $this->fallbackSpeed,
            ));
        }
    }
}
