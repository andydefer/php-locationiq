<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Collections\Utility\FloatTypedCollection;
use AndyDefer\DomainStructures\Utils\StrictAssociative;
use AndyDefer\PhpLocationIq\Collections\FloatMatrixCollection;
use InvalidArgumentException;

/**
 * Immutable Value Object representing a LocationIQ Matrix.
 *
 * Wraps the underlying row collection and exposes business helpers such as
 * {@see self::isNoRoute()} and {@see self::getCell()}.
 *
 * Cells without a route are represented by the sentinel value
 * {@see self::NO_ROUTE_SENTINEL}.
 */
final class FloatMatrixVO extends AbstractValueObject
{
    /**
     * Sentinel value used for cells without a route.
     */
    public const NO_ROUTE_SENTINEL = -1.0;

    public function __construct(
        private readonly FloatMatrixCollection $rows,
    ) {
        $this->assertRowsAreValid();
    }

    /**
     * Returns the underlying row collection.
     */
    public function getRows(): FloatMatrixCollection
    {
        return $this->rows;
    }

    /**
     * Returns the value of the cell at the given row and column, or the
     * sentinel value when the cell has no route.
     */
    public function getCell(int $row, int $column): float
    {
        $rowCollection = $this->rows->offsetGet($row);

        if (! $rowCollection instanceof FloatTypedCollection) {
            return self::NO_ROUTE_SENTINEL;
        }

        $value = $rowCollection->offsetGet($column);

        return is_float($value) ? $value : self::NO_ROUTE_SENTINEL;
    }

    /**
     * Whether the cell at the given row and column has no route.
     */
    public function isNoRoute(int $row, int $column): bool
    {
        return $this->getCell($row, $column) === self::NO_ROUTE_SENTINEL;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): StrictAssociative
    {
        $serialisedRows = [];

        foreach ($this->rows as $row) {
            $serialisedRow = [];

            foreach ($row as $cell) {
                $serialisedRow[] = $cell;
            }

            $serialisedRows[] = $serialisedRow;
        }

        return StrictAssociative::from([
            'rows' => $serialisedRows,
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

    /**
     * Ensures every row has the same length, so the matrix is rectangular.
     */
    private function assertRowsAreValid(): void
    {
        if ($this->rows->isEmpty()) {
            return;
        }

        $expected = null;

        foreach ($this->rows as $row) {
            $count = $row->count();

            if ($expected === null) {
                $expected = $count;

                continue;
            }

            if ($count !== $expected) {
                throw new InvalidArgumentException(sprintf(
                    'Matrix rows must have the same length. Expected %d, got %d.',
                    $expected,
                    $count,
                ));
            }
        }
    }
}
