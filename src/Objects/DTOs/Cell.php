<?php

declare (strict_types = 1);

namespace ReallifeKip\GridMap\Objects\DTOs;

use ReallifeKip\ImmutableBase\Objects\DataTransferObject;

/**
 * Represents a single cell in the grid
 *
 * @property int $colSpan Number of columns this cell spans
 * @property int $rowSpan Number of rows this cell spans
 */
readonly class Cell extends DataTransferObject
{
    public int $colSpan;
    public int $rowSpan;
}
