<?php

declare (strict_types = 1);

namespace ReallifeKip\GridMap\Objects\DTOs;

use ReallifeKip\ImmutableBase\Objects\DataTransferObject;

/**
 * Represents a sliced region of the image
 *
 * @property int $x X coordinate of the slice
 * @property int $y Y coordinate of the slice
 * @property int $width Width of the slice
 * @property int $height Height of the slice
 */
readonly class Slice extends DataTransferObject
{
    public int $x;
    public int $y;
    public int $width;
    public int $height;
}
