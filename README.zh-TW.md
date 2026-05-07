# GridMap

**語言:** 繁體中文 | [English](README.md)

一個輕量的「格狀區域切片 (grid slicing)」PHP 函式庫，用來在固定尺寸的平面（例如：1920×1080 畫布、看板牆、影音合成畫面、Dashboard 或佈局系統）上，依照指定的網格劃分，將多個矩形區塊依序「填入」可用的格子，並回傳實際像素座標與尺寸。

`GridMap` 透過列優先掃描演算法（row-major first-fit）尋找第一個可容納指定欄數/列數的空區域；成功即標記佔用並繼續下一個切片。若切片無法放入、或所有切片放完後網格仍未完全填滿，則拋出例外。

---

## ✨ 特性總覽

- ✅ 單一靜態類別，極易理解與整合
- ✅ 純 PHP ≥ 8.0，搭配型別安全的不可變 DTO
- ✅ 以「格子數」描述切片，自動換算為實際像素 `x, y, width, height`
- ✅ 先填先放（deterministic），可重現結果
- ✅ 採整數除法 `intdiv` 避免浮點累積誤差
- ✅ 自動偵測無法放置或未完整填滿的情況，並拋出明確例外
- ✅ 可用於動態排版、視覺拼接、媒體牆、自動版面建議

---

## 📦 安裝

```bash
composer require reallifekip/grid-map
```

---

## 🚀 快速開始

```php
use ReallifeKip\GridMap\GridMap;
use ReallifeKip\GridMap\Objects\DTOs\Config;

// 整體畫布：1920×1080，切成 24×12 的格子（典型 16:9 分割）
$result = GridMap::slice(
    Config::fromArray([
        'imageWidth'  => 1920,
        'imageHeight' => 1080,
        'columns'     => 24,
        'rows'        => 12,
        'cells'       => [
            ['colSpan' => 6,  'rowSpan' => 6],
            ['colSpan' => 6,  'rowSpan' => 6],
            ['colSpan' => 6,  'rowSpan' => 6],
            ['colSpan' => 6,  'rowSpan' => 6],
            ['colSpan' => 12, 'rowSpan' => 6],
            ['colSpan' => 12, 'rowSpan' => 6],
        ],
    ])
);

print_r($result);
```

範例輸出：

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

> 上述寬高為「實際像素」，係由 `intdiv` 整數除法換算。
> 若 `1920/24 = 80`、`1080/12 = 90`，則 6 欄寬 = 6 × 80 = 480 px；6 列高 = 6 × 90 = 540 px。

---

## 🧠 核心概念

| 名稱                          | 說明                                                  |
| ----------------------------- | ----------------------------------------------------- |
| `imageWidth`, `imageHeight`   | 整體畫布尺寸（像素）                                  |
| `columns`, `rows`             | 欲將畫布切成的網格總數（橫 / 縱）                     |
| `Cell` `colSpan`, `rowSpan`   | 要放入之矩形切片，使用「格子數」而非像素              |
| 回傳 `Slice[]`                | 每個已放置切片物件，含 `x`、`y`、`width`、`height`（像素） |

### Config 預設值

| 屬性          | 預設值 |
| ------------- | ------ |
| `imageWidth`  | `2500` |
| `imageHeight` | `1686` |
| `columns`     | `24`   |
| `rows`        | `12`   |

步驟（簡化）：

1. 計算所有格線座標：`cols[x] = intdiv(x * imageWidth, columns)`、`rows[y] = intdiv(y * imageHeight, rows)`
2. 使用一維陣列標記每個 cell 是否被佔用
3. 對每個 `Cell`：
   - 依列優先（row-major）掃描第一個可放下的位置
   - 確認其覆蓋區塊內所有 cell 均未佔用
   - 標記佔用並計算實際像素 `Slice`
4. 若無法放入則拋出 `\Exception`
5. 所有 cell 放置完後若網格未完全填滿，同樣拋出 `\Exception`

---

## ✅ 使用情境範例

| 情境            | 描述                             |
| --------------- | -------------------------------- |
| 媒體牆 / 監控牆 | 自動將多個視訊來源排入拼接畫面   |
| 影片合成        | 將多軌素材映射到固定輸出畫布座標 |
| 即時 Dashboard  | 模組化卡片版面自動配置初稿       |
| 遊戲 / 關卡編輯 | 地圖或場景框格初始擺放規劃       |
| 廣告排程畫面    | 將多個廣告素材放置於格狀佈局     |

---

## 🛠️ 進階範例：混合大小與錯誤處理

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
                ['colSpan' => 8,  'rowSpan' => 4], // C — 須完整填滿網格
                ['colSpan' => 20, 'rowSpan' => 6], // D
            ],
        ])
    );
} catch (\Exception $e) {
    // 若某切片放不下，或網格未完整填滿，皆會在此拋出
    echo 'Slice failed: ' . $e->getMessage();
}
```

---

## ⚠️ 注意事項

1. 每個 `Cell` 須包含兩個正整數：`colSpan` 與 `rowSpan`
2. `colSpan` 不可大於 `columns`；`rowSpan` 不可大於 `rows`
3. 目前策略為「第一個可行位置即放置」：非最佳化空間利用，只求 deterministic
4. 若需要「最佳填充 / 旋轉 / 重新排序」請自行前處理（例如排序切片由大到小）
5. 回傳的 `Slice[]` 陣列索引順序與輸入 `cells` 順序一一對應
6. 所有 cell 必須**完整填滿**整個網格，否則拋出 `\Exception`

---

## 🔍 回傳資料格式

回傳 `Slice` 物件陣列：

```php
/** @var ReallifeKip\GridMap\Objects\DTOs\Slice[] $result */
$result[0]->x;      // int — 左邊緣像素座標
$result[0]->y;      // int — 上邊緣像素座標
$result[0]->width;  // int — 寬度（像素）
$result[0]->height; // int — 高度（像素）
```

---

## 🧪 測試建議

可自行撰寫 PHPUnit 測試驗證：

- 切片數量與回傳陣列數量一致
- 所有矩形不互相重疊（轉換回原始 cell 交集檢查）
- 全部成功放置且完整填滿時，佔用格數 = `columns * rows`
- 超出限制的切片觸發 `\Exception`
- 未完整填滿的配置觸發 `\Exception`

---

## 📄 授權 License

本套件採用 [MIT License](./LICENSE) – 可商用 / 修改 / 散佈，請保留版權聲明。

---

## 👤 開發者資訊

作者：Kip ([bill402099@gmail.com](mailto:bill402099@gmail.com))
GitHub：[@ReallifeKip](https://github.com/ReallifeKip)

如果本專案對你有幫助：歡迎 Star、分享或提出改進建議！
