<?php

declare (strict_types = 1);

namespace ReallifeKip\GridMap\Objects\DTOs;

use ReallifeKip\ImmutableBase\Attributes\ArrayOf;
use ReallifeKip\ImmutableBase\Attributes\Defaults;
use ReallifeKip\ImmutableBase\Objects\DataTransferObject;

/**
 * Configuration for GridMap image slicing
 *
 * @property int $imageWidth Needs to be set to the area width
 * @property int $imageHeight Needs to be set to the area height
 * @property int $columns Max horizontal grid count
 * @property int $rows Max vertical grid count
 * @property Cell[] $cells Grid cells to slice
 */
readonly class Config extends DataTransferObject
{
    #[Defaults(2500)]
    public int $imageWidth;
    #[Defaults(1686)]
    public int $imageHeight;
    #[Defaults(24)]
    public int $columns;
    #[Defaults(12)]
    public int $rows;
    #[ArrayOf(Cell::class)]
    public array $cells;
}
