<?php

namespace ReallifeKip\GridMap;

class GridMap
{
    public function __construct(
        /** @var int Needs to be set to the area width */
        private int $area_w,
        /** @var int Needs to be set to the area height */
        private int $area_h,
        /** @var int Max horizontal grid count */
        private int $grids_w = 24,
        /** @var int Max vertical grid count */
        private int $grids_h = 12,
    ) {
    }
    /**
     * Slices the grid into smaller areas by the specified slice dimensions.
     * @param array $slices Array of [width, height] pairs representing slice dimensions.
     * @throws \Exception if a slice cannot be placed within the grid.
     * @return array<int, array{x:int,y:int,width:int,height:int}>
     */
    public function slice(array $slices = [])
    {
        $iw = $this->area_w;
        $ih = $this->area_h;
        $gw = $this->grids_w;
        $gh = $this->grids_h;

        $cols = [];
        $rows = [];
        $cols[0] = 0;
        $rows[0] = 0;
        for ($x = 1; $x <= $gw; $x++) {
            $cols[$x] = intdiv($x * $iw, $gw);
        }
        for ($y = 1; $y <= $gh; $y++) {
            $rows[$y] = intdiv($y * $ih, $gh);
        }

        $cellCount = $gw * $gh;
        $taken = array_fill(0, $cellCount, 0);

        $areas = [];
        $occupied = 0;

        foreach ($slices as $slice) {
            [$cw, $ch] = $slice;
            $placed = false;

            $gyMax = $gh - $ch;
            $gxMax = $gw - $cw;

            for ($gy = 0; $gy <= $gyMax && !$placed; $gy++) {
                $rowStart = $gy * $gw;

                for ($gx = 0; $gx <= $gxMax; $gx++) {
                    $can = true;

                    for ($dy = 0, $base = $rowStart; $dy < $ch; $dy++, $base += $gw) {
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

                    for ($dy = 0, $base = $rowStart; $dy < $ch; $dy++, $base += $gw) {
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

                    $areas[] = [
                        'x'      => $x1,
                        'y'      => $y1,
                        'width'  => $x2 - $x1,
                        'height' => $y2 - $y1,
                    ];

                    $placed = true;
                    if ($placed) {
                        break;
                    }
                }
            }

            if (!$placed) {
                throw new \Exception("Cannot place slice [{$cw},{$ch}] within {$gw}x{$gh} grid.");
            }
        }

        if ($occupied !== $cellCount) {
            trigger_error(
                "Grid not fully occupied: {$occupied}/{$cellCount}",
                E_USER_NOTICE
            );
        }

        return $areas;
    }

}
