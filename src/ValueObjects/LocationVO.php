<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Collections\Utility\FloatTypedCollection;
use InvalidArgumentException;

/**
 * Immutable Value Object representing a geographic coordinate pair.
 *
 * Stores exactly two floats in the order expected by LocationIQ:
 * `[longitude, latitude]`. Any other arity is rejected at construction.
 *
 * @example
 * $location = LocationVO::fromArray([15.3222, -4.3250]);
 * echo $location->getLongitude(); // 15.3222
 * echo $location->getLatitude();  // -4.325
 * echo (string) $location;        // "15.322200,-4.325000"
 */
final class LocationVO extends AbstractValueObject
{
    /**
     * Exact number of floats a location must contain.
     */
    private const COORDINATE_COUNT = 2;

    public function __construct(
        private readonly FloatTypedCollection $coordinates,
    ) {
        if ($coordinates->count() !== self::COORDINATE_COUNT) {
            throw new InvalidArgumentException(
                sprintf(
                    'Location must contain exactly %d floats, %d given.',
                    self::COORDINATE_COUNT,
                    $coordinates->count()
                )
            );
        }
    }

    /**
     * Creates a location from a raw array of numeric coordinates.
     *
     * @param  array<int, float|int|string>  $coordinates  Ordered as `[longitude, latitude]`.
     *
     * @throws InvalidArgumentException When a coordinate is not numeric or the arity is invalid.
     */
    public static function fromArray(array $coordinates): self
    {
        $collection = new FloatTypedCollection;

        foreach ($coordinates as $coordinate) {
            if (! is_numeric($coordinate)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Location coordinate must be numeric, %s given.',
                        get_debug_type($coordinate)
                    )
                );
            }

            $collection->add((float) $coordinate);
        }

        return new self($collection);
    }

    /**
     * Returns the longitude (first element of the coordinate pair).
     */
    public function getLongitude(): float
    {
        return (float) $this->coordinates->first();
    }

    /**
     * Returns the latitude (second element of the coordinate pair).
     */
    public function getLatitude(): float
    {
        return (float) $this->coordinates->last();
    }

    /**
     * Returns the underlying coordinate collection.
     */
    public function getValue(): FloatTypedCollection
    {
        return $this->coordinates;
    }

    /**
     * {@inheritDoc}
     */
    public function equals(AbstractValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->coordinates->toArray() === $other->coordinates->toArray();
    }

    /**
     * Returns the location formatted as `longitude,latitude` with 6 decimals.
     */
    public function __toString(): string
    {
        return sprintf('%F,%F', $this->getLongitude(), $this->getLatitude());
    }
}
