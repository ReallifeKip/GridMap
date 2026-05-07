# GridMap

**Language:** English | [繁體中文](README.zh-TW.md)

A lightweight PHP micro-library for **grid slicing**. It divides a fixed-size plane (e.g., 1920×1080 canvas, video wall, media composition, dashboard, or layout system) into a grid according to specified proportions, then sequentially "fills" rectangular slices into available cells and returns their pixel coordinates and sizes.

`GridMap` uses a simple row-major first-fit scanning algorithm to find the first available area that can fit a slice of the requested column/row span. If successful, the area is marked as occupied and the next slice is processed. If a slice cannot fit, or if the grid is not fully occupied at the end, an exception is thrown.

---

## ✨ Features Overview

- ✅ Single static class, easy to understand and integrate
- ✅ Pure PHP ≥ 8.0 with typed, immutable DTOs
- ✅ Define slices in "grid units" and automatically convert to pixel coordinates `x, y, width, height`
- ✅ Deterministic first-fit placement, reproducible results
- ✅ Uses integer division `intdiv` to avoid floating-point rounding errors
- ✅ Automatically detects unplaceable or unfilled slices and throws clear exceptions
- ✅ Suitable for dynamic layouts, video walls, auto-layout suggestions

---

## 📦 Installation

```bash
composer require reallifekip/grid-map
```

---

## 🚀 Quick Start

```php
use ReallifeKip\GridMap\GridMap;
use ReallifeKip\GridMap\Objects\DTOs\Config;

// Canvas: 1920×1080, divided into a 24×12 grid (typical 16:9 split)
$result = GridMap::slice(
    Config::fromArray([
        'imageWidth'  => 1920,
        'imageHeight' => 1080,
        'columns'     => 24,
        'rows'        => 12,
        'cells'       => [
            ['colSpan' => 6, 'rowSpan' => 6],
            ['colSpan' => 6, 'rowSpan' => 6],
            ['colSpan' => 6, 'rowSpan' => 6],
            ['colSpan' => 6, 'rowSpan' => 6],
            ['colSpan' => 12, 'rowSpan' => 6],
            ['colSpan' => 12, 'rowSpan' => 6],
        ],
    ])
);

print_r($result);
```

Example output:

```
Array
(
    [0] => Slice Object ( [x] => 0    [y] => 0    [width] => 480  [height] => 540 )
    [1] => Slice Object ( [x] => 480  [y] => 0    [width] => 480  [height] => 540 )
    [2] => Slice Object ( [x] => 960  [y] => 0    [width] => 480  [height] => 540 )
    [3] => Slice Object ( [x] => 1440 [y] => 0    [width] => 480  [height] => 540 )
    [4] => Slice Object ( [x] => 0    [y] => 540  [width] => 960  [height] => 540 )
    [5] => Slice Object ( [x] => 960  [y] => 540  [width] => 960  [height] => 540 )
)
```

> Width/height are actual pixels, calculated via `intdiv`.
> For example: `1920/24 = 80`, `1080/12 = 90`.
> A 6-column span = 6 × 80 = 480 px; a 6-row span = 6 × 90 = 540 px.

---

## 🧠 Core Concept

| Term                       | Description                                                  |
| -------------------------- | ------------------------------------------------------------ |
| `imageWidth`, `imageHeight`| Total canvas size (pixels)                                   |
| `columns`, `rows`          | Number of grid divisions (horizontal / vertical)             |
| `Cell` `colSpan`, `rowSpan`| Slice rectangle defined in grid units, not pixels            |
| return `Slice[]`           | Each placed slice object with `x`, `y`, `width`, `height` (px) |

### Config defaults

| Property      | Default |
| ------------- | ------- |
| `imageWidth`  | `2500`  |
| `imageHeight` | `1686`  |
| `columns`     | `24`    |
| `rows`        | `12`    |

Steps (simplified):

1. Compute all grid line coordinates:
   `cols[x] = intdiv(x * imageWidth, columns)`, `rows[y] = intdiv(y * imageHeight, rows)`
2. Use a 1D array to mark whether each cell is occupied
3. For each `Cell`:
   - Scan row-major for the first fitting location
   - Check that all covered cells are free
   - Mark occupied and convert to a pixel `Slice`
4. Throw `\Exception` if a cell cannot be placed
5. Throw `\Exception` if the grid is not fully occupied after all cells are placed

---

## ✅ Example Use Cases

| Use Case           | Description                                       |
| ------------------ | ------------------------------------------------- |
| Media/Video wall   | Automatically arrange multiple video sources      |
| Video compositing  | Map multiple tracks into fixed canvas coordinates |
| Realtime dashboard | Auto-generate initial layouts for widget cards    |
| Game/Level editor  | Plan map or scene block placements                |
| Ad screen layout   | Place multiple ad creatives into grid layout      |

---

## 🛠️ Advanced Example: Mixed Sizes & Error Handling

```php
use ReallifeKip\GridMap\GridMap;
use ReallifeKip\GridMap\Objects\DTOs\Config;

try {
    $result = GridMap::slice(
        Config::fromArray([
            'imageWidth'  => 1200,
            'imageHeight' => 800,
            'columns'     => 20,
            'rows'        => 10,
            'cells'       => [
                ['colSpan' => 4,  'rowSpan' => 4], // A
                ['colSpan' => 8,  'rowSpan' => 4], // B
                ['colSpan' => 8,  'rowSpan' => 4], // C — must fully fill the grid
                ['colSpan' => 20, 'rowSpan' => 6], // D
            ],
        ])
    );
} catch (\Exception $e) {
    // Thrown if a slice cannot be placed, or if the grid is left partially unfilled
    echo 'Slice failed: ' . $e->getMessage();
}
```

---

## ⚠️ Notes

1. Each `Cell` must have two positive integers: `colSpan` and `rowSpan`
2. `colSpan` cannot exceed `columns`; `rowSpan` cannot exceed `rows`
3. Strategy is "first feasible placement": not optimized for space, just deterministic
4. For "optimal packing / rotation / reordering", preprocess cells (e.g., sort by size)
5. The returned `Slice[]` array indexes correspond 1-to-1 with the input `cells` order
6. All cells **must** collectively fill the entire grid — a partially filled grid throws an `\Exception`

---

## 🔍 Return Data Format

Returns an array of `Slice` objects:

```php
/** @var ReallifeKip\GridMap\Objects\DTOs\Slice[] $result */
$result[0]->x;      // int — left edge in pixels
$result[0]->y;      // int — top edge in pixels
$result[0]->width;  // int — width in pixels
$result[0]->height; // int — height in pixels
```

---

## 🧪 Testing Suggestions

You may write PHPUnit tests to verify:

- Slice count matches returned array count
- No overlapping rectangles (check cell intersections)
- When all cells fit, occupied grid count = `columns * rows`
- Oversized cells trigger `\Exception`
- Partially filled grids trigger `\Exception`

---

## 📄 License

This package uses the [MIT License](./LICENSE) – Free for commercial use / modification / distribution, just keep copyright notice.

---

## 👤 Developer Info

Author: Kip ([bill402099@gmail.com](mailto:bill402099@gmail.com))
GitHub: [@ReallifeKip](https://github.com/ReallifeKip)

If this project helps you: feel free to Star, share, or suggest improvements!
