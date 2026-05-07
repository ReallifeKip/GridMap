<?php

declare (strict_types = 1);

namespace ReallifeKip\GridMap;

use ReallifeKip\GridMap\Objects\DTOs\Config;
use ReallifeKip\GridMap\Objects\DTOs\Slice;

class GridMap
{
    /**
     * Slices the grid into smaller areas by the specified slice dimensions.
     * @param array $slices Array of [width, height] pairs representing slice dimensions.
     * @throws \Exception if a slice cannot be placed within the grid.
     * @return Slice[]
     */
    public static function slice(Config $config)
    {
        $iw = $config->imageWidth;
        $ih = $config->imageHeight;
        $cc = $config->columns;
        $cr = $config->rows;

        $cols    = [];
        $rows    = [];
        $cols[0] = 0;
        $rows[0] = 0;
        for ($x = 1; $x <= $cc; $x++) {
            $cols[$x] = intdiv($x * $iw, $cc);
        }
        for ($y = 1; $y <= $cr; $y++) {
            $rows[$y] = intdiv($y * $ih, $cr);
        }

        $cellCount = $cc * $cr;
        $taken     = array_fill(0, $cellCount, 0);

        $slices   = [];
        $occupied = 0;

        foreach ($config->cells as $cell) {
            $cw     = $cell->colSpan;
            $ch     = $cell->rowSpan;
            $placed = false;

            $gyMax = $cr - $ch;
            $gxMax = $cc - $cw;

            for ($gy = 0; $gy <= $gyMax && !$placed; $gy++) {
                $rowStart = $gy * $cc;

                for ($gx = 0; $gx <= $gxMax; $gx++) {
                    $can = true;

                    for ($dy = 0, $base = $rowStart; $dy < $ch; $dy++, $base += $cc) {
                        $idx = $base + $gx;
                        for ($dx = 0; $dx < $cw; $dx++, $idx++) {
                            if ($taken[$idx] !== 0) {
                                $can = false;
                                break 2;
                            }
                        }
                    }

                    if (!$can) {
                        continue;
                    }

                    for ($dy = 0, $base = $rowStart; $dy < $ch; $dy++, $base += $cc) {
                        $idx = $base + $gx;
                        for ($dx = 0; $dx < $cw; $dx++, $idx++) {
                            $taken[$idx] = 1;
                        }
                    }
                    $occupied += $cw * $ch;

                    $x1 = $cols[$gx];
                    $x2 = $cols[$gx + $cw];
                    $y1 = $rows[$gy];
                    $y2 = $rows[$gy + $ch];

                    $slices[] = Slice::fromArray([
                        'x'      => $x1,
                        'y'      => $y1,
                        'width'  => $x2 - $x1,
                        'height' => $y2 - $y1,
                    ]);

                    $placed = true;
                    if ($placed) {
                        break;
                    }
                }
            }

            if (!$placed) {
                throw new \Exception("Cannot place slice [{$cw},{$ch}] within {$cc}x{$cr} grid.");
            }
        }

        if ($occupied !== $cellCount) {
            throw new \Exception("Grid not fully occupied: {$occupied}/{$cellCount}");
        }

        return $slices;
    }

}
