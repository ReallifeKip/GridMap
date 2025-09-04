# GridMap

**Language:** English | [繁體中文](README.md)

A lightweight, zero-dependency PHP micro-library for **grid slicing**. It divides a fixed-size plane (e.g., 1920x1080 canvas, video wall, media composition, dashboard, or layout system) into a grid according to specified proportions, then sequentially “fills” rectangular slices into available cells and returns their pixel coordinates and sizes.

`GridMap` uses a simple row-major first-fit scanning algorithm to find the first available area that can fit a slice of the requested grid width/height. If successful, the area is marked as occupied and the next slice is processed. If a slice cannot fit, an exception is thrown.

---

## ✨ Features Overview

- ✅ Single class, easy to understand and integrate
- ✅ No extensions required: pure PHP ≥ 8.0
- ✅ Define slices in “grid units” and automatically convert to pixel coordinates `x,y,width,height`
- ✅ Deterministic first-fit placement, reproducible results
- ✅ Uses integer division `intdiv` to avoid floating-point rounding errors
- ✅ Automatically detects unplaceable slices and throws clear exceptions
- ✅ Suitable for dynamic layouts, video walls, auto-layout suggestions
- ⚠️ If not fully filled, triggers an `E_USER_NOTICE` about remaining empty cells

---

## 📦 Installation

```bash
composer require reallifekip/grid-map
```

---

## 🚀 Quick Start

```php
use ReallifeKip\GridMap\GridMap;

// Canvas size: 1920x1080; divided into 24 x 12 grid (typical 16:9 split)
$gm = new GridMap(
	area_w: 1920,
	area_h: 1080,
	grids_w: 24,
	grids_h: 12,
);

// Define slices: each element is [grid_width, grid_height]
// Example: [6,6] means 6x6 grid cells
$slices = [
	[6, 6],
	[6, 6],
	[6, 6],
	[6, 6],
	[12, 6], // width 12, height 6
	[12, 6],
];

$areas = $gm->slice($slices);

print_r($areas);
```

Example output (actual may vary depending on allocation):

```php
Array
(
	[0] => Array ( [x] => 0    [y] => 0    [width] => 480  [height] => 540 )
	[1] => Array ( [x] => 480  [y] => 0    [width] => 480  [height] => 540 )
	[2] => Array ( [x] => 960  [y] => 0    [width] => 480  [height] => 540 )
	[3] => Array ( [x] => 1440 [y] => 0    [width] => 480  [height] => 540 )
	[4] => Array ( [x] => 0    [y] => 540  [width] => 960  [height] => 540 )
	[5] => Array ( [x] => 960  [y] => 540 [width] => 960  [height] => 540 )
)
```

> The above width/height are actual pixels, calculated by dividing `(total_canvas / grid_count)`.
> For example: `1920/24=80`, `1080/12=90`.
> A 6-grid width = 6 × 80 = 480 pixels; a 6-grid height = 6 × 90 = 540 pixels.

---

## 🧠 Core Concept

| Term                 | Description                                           |
| -------------------- | ----------------------------------------------------- |
| `area_w`, `area_h`   | Total canvas size (pixels)                            |
| `grids_w`, `grids_h` | Number of grid divisions (horizontal / vertical)      |
| slice `[cw, ch]`     | Slice rectangle defined in grid units, not pixels     |
| return `areas[]`     | Each placed slice, with `x`,`y`,`width`,`height` (px) |

Steps (simplified):

1. Compute all grid line coordinates:
   `cols[x] = intdiv(x * area_w, grids_w)`, `rows[y] = intdiv(y * area_h, grids_h)`
2. Use a 1D array to mark whether each cell is occupied
3. For each slice:

   - Scan row-major for the first fitting location
   - Check if all covered cells are free
   - Mark occupied and convert to pixel rectangle

4. Throw exception if slice cannot be placed
5. If leftover space exists, trigger an `E_USER_NOTICE` (informational, can be ignored or handled)

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

$gm = new GridMap(1200, 800, 20, 10);

$slices = [
	[4, 4], // A
	[8, 4], // B
	[4, 2], // C
	[6, 6], // D may not fit later
];

try {
	$areas = $gm->slice($slices);
} catch (\Exception $e) {
	// Thrown if a slice cannot be placed
	echo 'Slice placement failed: ' . $e->getMessage();
}
```

---

## ⚠️ Notes

1. Each slice must be `[cw, ch]` of two positive integers
2. `cw` cannot exceed `grids_w`, `ch` cannot exceed `grids_h`
3. Strategy is “first feasible placement”: not optimized for space, just deterministic
4. For “optimal packing / rotation / reordering”, preprocess slices (e.g., sort by size)
5. Returned array indexes correspond 1-to-1 with input slice order
6. To ignore the unfilled notice, define a custom error handler or suppress `E_USER_NOTICE`

---

## 🔍 Return Data Format

```php
[
	[ 'x' => int, 'y' => int, 'width' => int, 'height' => int ],
	...
]
```

---

## 🧪 Testing Suggestions (example)

You may write PHPUnit tests to verify:

- Slice count matches returned array count
- No overlapping rectangles (check original cell intersections)
- When all slices fit, occupied grid count = `grids_w * grids_h`
- Oversized slices trigger `Exception`

---

## 📄 License

This package uses the [MIT License](./LICENSE) – Free for commercial use / modification / distribution, just keep copyright notice.

---

## 👤 Developer Info

Author: Kip ([bill402099@gmail.com](mailto:bill402099@gmail.com))
GitHub: [@ReallifeKip](https://github.com/ReallifeKip)

If this project helps you: feel free to Star, share, or suggest improvements!
