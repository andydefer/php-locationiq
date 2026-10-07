<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Collections\Utility\FloatTypedCollection;
use InvalidArgumentException;

final class BoundingBoxVO extends AbstractValueObject
{
    private const EXPECTED_SIZE = 4;

    public function __construct(
        private readonly FloatTypedCollection $coordinates,
    ) {
        if ($coordinates->count() !== self::EXPECTED_SIZE) {
            throw new InvalidArgumentException(
                sprintf(
                    'BoundingBox must contain exactly %d floats, %d given.',
                    self::EXPECTED_SIZE,
                    $coordinates->count()
                )
            );
        }
    }

    /**
     * @param  array<int, float|int|string>  $coordinates  Ordered as [minLat, maxLat, minLon, maxLon].
     */
    public static function fromArray(array $coordinates): self
    {
        $collection = new FloatTypedCollection;

        foreach ($coordinates as $coordinate) {
            if (! is_numeric($coordinate)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'BoundingBox coordinate must be numeric, %s given.',
                        get_debug_type($coordinate)
                    )
                );
            }

            $collection->add((float) $coordinate);
        }

        return new self($collection);
    }

    public function getMinLatitude(): float
    {
        return (float) $this->coordinates->first();
    }

    public function getMaxLatitude(): float
    {
        return (float) $this->coordinates[1];
    }

    public function getMinLongitude(): float
    {
        return (float) $this->coordinates[2];
    }

    public function getMaxLongitude(): float
    {
        return (float) $this->coordinates->last();
    }

    public function getValue(): FloatTypedCollection
    {
        return $this->coordinates;
    }

    public function equals(AbstractValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->coordinates->toArray() === $other->coordinates->toArray();
    }
}
