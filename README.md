# GridMap

**Languages:** English | [繁體中文](README.zh-TW.md)

A lightweight, zero-dependency PHP library for deterministic grid-based rectangular placement. It helps you subdivide a fixed-size canvas (e.g., 1920x1080 layout, video wall, dashboard, compositing surface) into a logical grid and sequentially place rectangular slices defined by grid cell counts, returning precise pixel coordinates and sizes.

`GridMap` uses a simple row-major first-fit scanning algorithm: for each requested slice (defined in grid units), it searches for the first contiguous free block large enough to hold it. Once placed, cells are marked as occupied. If no space can be found for a slice, an exception is thrown.

---

## ✨ Features

- ✅ Single-class, easy to grasp and integrate
- ✅ Pure PHP ≥ 8.0 (no extensions needed)
- ✅ Define regions by grid counts; returns pixel-based `x, y, width, height`
- ✅ Deterministic first-fit ordering (repeatable output)
- ✅ Uses integer division (`intdiv`) to avoid floating-point drift
- ✅ Explicit exception if a slice cannot be placed
- ✅ Suitable for layout prototyping, compositing, video walls, dashboards
- ⚠️ Emits an `E_USER_NOTICE` if the full grid is not completely occupied (informational)

---

## 📦 Installation

```bash
composer require reallifekip/grid-map
```

---

## 🚀 Quick Start

```php
use ReallifeKip\GridMap\GridMap;

// Canvas size: 1920x1080, divided into a 24 x 12 logical grid
$gm = new GridMap(
    area_w: 1920,
    area_h: 1080,
    grids_w: 24,
    grids_h: 12,
);

// Define slices as [gridWidth, gridHeight]
$slices = [
    [6, 6],
    [6, 6],
    [6, 6],
    [6, 6],
    [12, 6],
    [12, 6],
];

$areas = $gm->slice($slices);
print_r($areas);
```

Example output (values depend on grid placement order):

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

> Pixel width/height are derived from proportional integer partitioning.
> Given `1920/24 = 80` and `1080/12 = 90`, a 6x6 slice → width = 6 _ 80 = 480, height = 6 _ 90 = 540.

---

## 🧠 Core Concepts

| Term                 | Description                                             |
| -------------------- | ------------------------------------------------------- |
| `area_w`, `area_h`   | Final canvas size in pixels                             |
| `grids_w`, `grids_h` | Logical grid subdivision counts (horizontal / vertical) |
| slice `[cw, ch]`     | Requested rectangle measured in grid cells              |
| return `areas[]`     | Each placed region with pixel `x,y,width,height`        |

Algorithm (simplified):

1. Pre-compute grid line coordinates via integer division.
2. Maintain a 1D occupancy array for all cells.
3. For each slice: scan row-major for a free block; verify all cells free.
4. Mark cells occupied; convert to pixel rectangle.
5. If placement impossible → throw `Exception`.
6. If leftover free cells remain → emit `E_USER_NOTICE`.

---

## ✅ Example Use Cases

| Scenario             | Description                                 |
| -------------------- | ------------------------------------------- |
| Media / Monitor Wall | Arrange multi-source feeds into a composite |
| Video Compositing    | Map multi-track sources onto a final canvas |
| Real-time Dashboard  | Auto-generate initial card layout           |
| Game / Level Editing | Initial tile-based region prototyping       |
| Ad Scheduling Layout | Placing multi-size creatives on a grid      |

---

## 🛠️ Advanced Example (Mixed Sizes + Error Handling)

```php
use ReallifeKip\GridMap\GridMap;

$gm = new GridMap(1200, 800, 20, 10);

$slices = [
    [4, 4], // A
    [8, 4], // B
    [4, 2], // C
    [6, 6], // D (may fail depending on prior placement)
];

try {
    $areas = $gm->slice($slices);
} catch (\Exception $e) {
    echo 'Slice placement failed: ' . $e->getMessage();
}
```

---

## ⚠️ Notes & Constraints

1. Each slice must be a two-integer array `[cw, ch]` (positive values)
2. `cw` ≤ `grids_w`, `ch` ≤ `grids_h`
3. Strategy is first-fit, not globally optimized packing
4. For better packing, pre-sort slices (e.g., descending area) yourself
5. Returned array index order matches input slice order
6. Suppress or customize the partial-fill notice if desired

---

## 🔄 Optimization Tips

| Goal            | Approach                                                     |
| --------------- | ------------------------------------------------------------ |
| Reduce failures | Sort slices by descending `cw*ch` before placement           |
| Allow rotation  | Try `[cw,ch]` then `[ch,cw]` manually before calling `slice` |
| Better packing  | Implement a custom strategy (best-fit / heuristic)           |
| Fill gaps later | Write a helper to enumerate remaining free cells             |

---

## 🔍 Return Structure

```php
[
    [ 'x' => int, 'y' => int, 'width' => int, 'height' => int ],
    // ...
]
```

> The current PHPDoc suggests `array{height:int,width:int,x:int,y:int[]}`; consider updating to
> `array<int, array{x:int,y:int,width:int,height:int}>` for clarity.

---

## 🧪 Testing Suggestions

Recommended assertions (e.g., via PHPUnit):

- Count of returned areas equals slice count
- No overlaps (reconstruct occupied cells for each and check intersections)
- When fully filled: sum of slice cell areas == `grids_w * grids_h`
- Oversized or unplaceable slice triggers `Exception`

---

## 🧮 Complexity

Let:

- `G = grids_w * grids_h` (total cells)
- `S = number of slices`
- `A = average slice cell area (cw * ch)`

Worst-case (scanning nearly entire grid each time):

```
Time  ~ O(S * G + S * A) ≈ O(S * G)
Space ~ O(G)
```

Efficient for moderate grids (e.g., ≤ 100x100) and typical slice counts (< 200).

---

## 🗺️ Roadmap (Potential)

| Status | Item                                      |
| ------ | ----------------------------------------- |
| Idea   | Optional auto-rotation                    |
| Idea   | Pluggable placement strategy hooks        |
| Idea   | Free-space enumeration / gap analysis API |
| Idea   | SVG / HTML visualization output           |
| Idea   | PSR-12 coding standard + CI workflow      |

Contributions & suggestions welcome!

---

## 📄 License

MIT License (see [LICENSE](./LICENSE)).

---

## 👤 Author

Kip (bill402099@gmail.com)  
GitHub: [@ReallifeKip](https://github.com/ReallifeKip)

If this project helps you, please star it and share feedback.

---

Made with ❤️ for practical layout automation.
