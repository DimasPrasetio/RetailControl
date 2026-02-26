<?php

namespace App\Services\Product;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Uom;
use App\Services\Audit\AuditLogger;
use App\Enums\AuditActionEnum;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;

/**
 * Excel import service per MANAGEMENT_PRODUCT.txt spec.
 *
 * Handles all 11 sheets from 'MASTER BOOK TOKO 250126 (1).xlsx':
 * RUCIKA, RUCIKA FITTING, HCL, MASPION, FITTING MASPION,
 * PIPA TRILLIUN, EXTRANA, MILLIARD, MU, NO DROP, POWER PRALON.
 *
 * Output: ImportResult with created/skipped/failed counts + error list.
 */
class ExcelImportService
{
    private array $errors  = [];
    private int   $created = 0;
    private int   $skipped = 0;
    private int   $failed  = 0;

    /** Sheet config: [header_row (1-based), brand_code, category_code, base_uom_code, handler_method] */
    private array $sheetConfig = [
        'RUCIKA '         => [8,  'RUCIKA',      'PIPA_PVC',   'BATANG', 'importRucika'],
        'RUCIKA'          => [8,  'RUCIKA',      'PIPA_PVC',   'BATANG', 'importRucika'],
        'RUCIKA FITTING'  => [11, 'RUCIKA',      'FITTING_PVC','PCS',    'importRucikaFitting'],
        'HCL'             => [10, 'HCL',         'POMPA',      'PCS',    'importHcl'],
        'MASPION'         => [8,  'MASPION',     'PIPA_PVC',   'BATANG', 'importMaspion'],
        'FITTING MASPION' => [6,  'MASPION',     'FITTING_PVC','PCS',    'importFittingMaspion'],
        'PIPA TRILLIUN'   => [6,  'TRILLIUN',    'PIPA_PVC',   'BATANG', 'importPipaTrilliun'],
        'EXTRANA'         => [6,  'EXTRANA',     'KABEL',      'METER',  'importExtrana'],
        'MILLIARD'        => [9,  'MILLIARD',    'LAINNYA',    'ROLL',   'importMilliard'],
        'MU'              => [8,  'MU',          'MORTAR',     'PACK',   'importMu'],
        'NO DROP'         => [8,  'NO DROP',     'CHEM',       'PCS',    'importNoDrop'],
        'POWER PRALON'    => [8,  'POWER PRALON','PIPA_PVC',   'BATANG', 'importPowerPralon'],
    ];

    // ─── Cache ──────────────────────────────────────────────────────────────
    private array $brandCache    = [];
    private array $categoryCache = [];
    private array $uomCache      = [];
    private array $priceListCache = [];

    // ─── Public API ─────────────────────────────────────────────────────────

    public function import(string $filePath): array
    {
        $this->errors  = [];
        $this->created = 0;
        $this->skipped = 0;
        $this->failed  = 0;

        $xlsx = SimpleXLSX::parse($filePath);
        if (! $xlsx) {
            return $this->result(['file' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()]);
        }

        foreach ($xlsx->sheetNames() as $idx => $sheetName) {
            $normalName = trim($sheetName);
            $config = $this->sheetConfig[$normalName] ?? $this->sheetConfig[$sheetName] ?? null;
            if (! $config) {
                continue; // sheet tidak dikenali, skip
            }

            [$headerRow, $brandCode, $catCode, $uomCode, $method] = $config;
            $rows = $xlsx->rows($idx);

            if (empty($rows)) {
                continue;
            }

            $this->$method($rows, $headerRow, $brandCode, $catCode, $uomCode, $sheetName);
        }

        return $this->result();
    }

    // ─── Sheet handlers ──────────────────────────────────────────────────────

    private function importRucika(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers = $this->getHeaders($rows, $headerRow);
        // Expected: PRODUK, JENIS, SPESIFIKASI, mm, inch, OD (mm), HARGA, DISC 1, DISC 2, DISC 3, HARGA(final)
        $colProduk = $this->findCol($headers, ['PRODUK']);
        $colJenis  = $this->findCol($headers, ['JENIS']);
        $colSpec   = $this->findCol($headers, ['SPESIFIKASI']);
        $colMm     = $this->findCol($headers, ['mm', 'MM']);
        $colInch   = $this->findCol($headers, ['inch', 'INCH']);
        $colOd     = $this->findCol($headers, ['OD', 'OD (mm)']);
        $colPrice  = $this->findLastCol($headers, ['HARGA']);

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row = $rows[$i];
            $produk = trim((string)($row[$colProduk] ?? ''));
            if (! $produk || $produk === $this->cleanVal($headers[$colProduk] ?? '')) {
                continue;
            }

            $jenis = trim((string)($row[$colJenis] ?? ''));
            $spec  = trim((string)($row[$colSpec] ?? ''));
            $mm    = $this->numericVal($row[$colMm] ?? '');
            $inch  = trim((string)($row[$colInch] ?? ''));
            $od    = $this->numericVal($row[$colOd] ?? '');
            $price = $colPrice !== null ? $this->parsePrice($row[$colPrice] ?? '') : null;

            $attrs = array_filter([
                'diameter_mm'   => $mm ?: null,
                'diameter_inch' => $inch ?: null,
                'od_mm'         => $od ?: null,
                'spec'          => $spec ?: null,
                'jenis'         => $jenis ?: null,
                'panjang_m'     => 4,
            ]);

            $name    = $this->buildName('PIPA PVC RUCIKA', $jenis, $spec, $mm ? "{$mm}mm" : null, $inch ? "({$inch}\")" : null, '4m');
            $skuCode = $this->buildSku('RUCIKA', 'PIPA', $spec, $mm ? "{$mm}MM" : null, $this->canonicalInch($inch));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'RUCIKA_DEFAULT');
        }
    }

    private function importRucikaFitting(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers = $this->getHeaders($rows, $headerRow);
        $colProduk = $this->findCol($headers, ['PRODUK']);
        $colJenis  = $this->findCol($headers, ['JENIS']);
        $colMm     = $this->findCol($headers, ['MM', 'mm']);
        $colInch   = $this->findCol($headers, ['inch', 'INCH']);
        $colIsiBox = $this->findCol($headers, ['ISI/BOX', 'ISI BOX']);
        $colPrice  = $this->findCol($headers, ['HARGA / PCS', 'HARGA/PCS', 'HARGA']);

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row  = $rows[$i];
            $jenis = trim((string)($row[$colJenis] ?? ''));
            if (! $jenis) {
                continue;
            }

            $mm    = $this->numericVal($row[$colMm] ?? '');
            $inch  = trim((string)($row[$colInch] ?? ''));
            $packQ = max(1, (int)($row[$colIsiBox] ?? 1));
            $price = $colPrice !== null ? $this->parsePrice($row[$colPrice] ?? '') : null;
            $produk = trim((string)($row[$colProduk] ?? 'FITTING'));

            $attrs = array_filter([
                'size_mm'   => $mm ?: null,
                'size_inch' => $inch ?: null,
                'jenis'     => $jenis,
                'isi_per_box' => $packQ > 1 ? $packQ : null,
            ]);

            $name    = $this->buildName('FITTING RUCIKA', $jenis, $mm ? "{$mm}mm" : null, $inch ? "({$inch}\")" : null);
            $skuCode = $this->buildSku('RUCIKA', 'FITTING', $this->slug($jenis), $mm ? "{$mm}MM" : null);

            $item = $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'RUCIKA_DEFAULT');
            if ($item && $packQ > 1) {
                $item->update(['pack_qty' => $packQ]);
            }
        }
    }

    private function importHcl(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers   = $this->getHeaders($rows, $headerRow);
        $colType   = $this->findCol($headers, ['Type', 'TYPE']);
        $colJenis  = $this->findCol($headers, ['Jenis', 'JENIS']);
        $colOutlet = $this->findCol($headers, ['Outlet', 'OUTLET']);
        $colPower  = $this->findCol($headers, ['Power', 'POWER']);
        $colTempo  = $this->findCol($headers, ['TEMPO']);
        $colTunai  = $this->findCol($headers, ['TUNAI']);

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $type  = trim((string)($row[$colType] ?? ''));
            if (! $type || is_numeric($type)) {
                continue;
            }

            $jenis  = trim((string)($row[$colJenis] ?? ''));
            $outlet = trim((string)($row[$colOutlet] ?? ''));
            $power  = $this->parsePowerWatt($row[$colPower] ?? '');
            $tempo  = $colTempo !== null ? $this->parsePrice($row[$colTempo] ?? '') : null;
            $tunai  = $colTunai !== null ? $this->parsePrice($row[$colTunai] ?? '') : null;

            $attrs = array_filter([
                'type_text'  => $type,
                'jenis'      => $jenis ?: null,
                'outlet'     => $outlet ?: null,
                'power_watt' => $power ?: null,
            ]);

            $name    = $this->buildName('HCL', $type, $jenis, $outlet, $power ? "{$power}W" : null);
            $skuCode = $this->buildSku('HCL', 'POMPA', $this->slug($type), $this->slug($jenis));

            $item = $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, null, null);
            if ($item) {
                if ($tempo)  $this->upsertPrice($item, $tempo, 'HCL_TEMPO');
                if ($tunai)  $this->upsertPrice($item, $tunai, 'HCL_TUNAI');
            }
        }
    }

    private function importMaspion(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers  = $this->getHeaders($rows, $headerRow);
        $colProd  = $this->findCol($headers, ['PRODUK']);
        $colType  = $this->findCol($headers, ['TYPE']);
        $colUkuran= $this->findCol($headers, ['UKURAN']);
        $colNetto = $this->findCol($headers, ['NETTO']);

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $produk = trim((string)($row[$colProd] ?? ''));
            if (! $produk || $this->isLikelyHeader($produk)) {
                continue;
            }
            $type  = trim((string)($row[$colType] ?? ''));
            $ukuran = trim((string)($row[$colUkuran] ?? ''));
            $netto = $colNetto !== null ? $this->parsePrice($row[$colNetto] ?? '') : null;

            $attrs = array_filter(['type' => $type ?: null, 'ukuran_inch' => $ukuran ?: null, 'panjang_m' => 4]);
            $name    = $this->buildName('PIPA MASPION', $type, $ukuran);
            $skuCode = $this->buildSku('MASPION', 'PIPA', $this->slug($type), $this->slug($ukuran));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $netto, 'MASPION_DEFAULT');
        }
    }

    private function importFittingMaspion(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers = $this->getHeaders($rows, $headerRow);
        $colJenis = $this->findCol($headers, ['Jenis', 'JENIS']);
        $colClass = $this->findCol($headers, ['Class', 'CLASS']);
        $colUkuran= $this->findCol($headers, ['Ukuran', 'UKURAN']);
        $colPack  = $this->findCol($headers, ['Pack', 'PACK']);
        $colHarga = $this->findCol($headers, ['Harga', 'HARGA']);

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $jenis = trim((string)($row[$colJenis] ?? ''));
            if (! $jenis || $this->isLikelyHeader($jenis)) {
                continue;
            }
            $class  = trim((string)($row[$colClass] ?? ''));
            $ukuran = trim((string)($row[$colUkuran] ?? ''));
            $packQ  = max(1, (int)($row[$colPack] ?? 1));
            $price  = $colHarga !== null ? $this->parsePrice($row[$colHarga] ?? '') : null;

            $attrs = array_filter([
                'jenis'      => $jenis,
                'class'      => $class ?: null,
                'ukuran_inch'=> $ukuran ?: null,
                'isi_per_box'=> $packQ > 1 ? $packQ : null,
            ]);

            $name    = $this->buildName('FITTING MASPION', $jenis, $class, $ukuran);
            $skuCode = $this->buildSku('MASPION', 'FITTING', $this->slug($jenis), $this->slug($ukuran));

            $item = $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'MASPION_DEFAULT');
            if ($item && $packQ > 1) {
                $item->update(['pack_qty' => $packQ]);
            }
        }
    }

    private function importPipaTrilliun(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers  = $this->getHeaders($rows, $headerRow);
        $colProd  = $this->findCol($headers, ['PRODUK']);
        $colType  = $this->findCol($headers, ['TYPE']);
        $colUkuran= $this->findCol($headers, ['UKURAN']);
        $colPrice = $this->findLastCol($headers, ['HARGA']);

        $brand    = $this->findOrCreateBrand('TRILLIUN');
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $produk = trim((string)($row[$colProd] ?? ''));
            if (! $produk || $this->isLikelyHeader($produk)) {
                continue;
            }
            $type  = trim((string)($row[$colType] ?? ''));
            $ukuran= trim((string)($row[$colUkuran] ?? ''));
            $price = $colPrice !== null ? $this->parsePrice($row[$colPrice] ?? '') : null;

            $attrs   = array_filter(['type' => $type ?: null, 'ukuran_inch' => $ukuran ?: null]);
            $name    = $this->buildName('PIPA TRILLIUN', $type, $ukuran);
            $skuCode = $this->buildSku('TRILLIUN', 'PIPA', $this->slug($type), $this->slug($ukuran));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'TRILLIUN_DEFAULT');
        }
    }

    private function importExtrana(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers    = $this->getHeaders($rows, $headerRow);
        $colProduk  = $this->findCol($headers, ['PRODUK']);
        $colJenis   = $this->findCol($headers, ['JENIS']);
        $colUkuran  = $this->findCol($headers, ['Ukuran', 'UKURAN']);
        $colSatuan  = $this->findCol($headers, ['SATUAN']);
        // Cable type columns: everything after SATUAN
        $cableTypes = [];
        $satuanIdx  = $colSatuan ?? 3;
        foreach ($headers as $idx => $h) {
            if ($idx > $satuanIdx && trim($h) !== '') {
                $cableTypes[$idx] = trim($h);
            }
        }

        $brand    = $this->findOrCreateBrand($brandCode);
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row    = $rows[$i];
            $ukuran = trim((string)($row[$colUkuran] ?? ''));
            if (! $ukuran || $this->isLikelyHeader($ukuran)) {
                continue;
            }
            $satuan = trim((string)($row[$colSatuan] ?? 'mm2'));

            foreach ($cableTypes as $colIdx => $cableType) {
                $price = $this->parsePrice($row[$colIdx] ?? '');
                if ($price === null || $price <= 0) {
                    continue; // skip kosong per spec
                }

                $attrs = [
                    'cable_size'      => $ukuran,
                    'cable_size_unit' => $satuan,
                    'cable_type'      => $cableType,
                ];

                $name    = "KABEL EXTRANA {$cableType} {$ukuran} ({$satuan}) - harga/meter";
                $skuCode = $this->buildSku('EXTRANA', 'KABEL', $this->slug($ukuran), $this->slug($cableType));

                $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'EXTRANA_DEFAULT');
            }
        }
    }

    private function importMilliard(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers   = $this->getHeaders($rows, $headerRow);
        $colProduk = $this->findCol($headers, ['PRODUK']);
        $colJenis  = $this->findCol($headers, ['JENIS']);
        $colDiam   = $this->findCol($headers, ['DIAMETER', 'DIAMETER (INCH)']);
        $colMRoll  = $this->findCol($headers, ['M/ROLL', 'M/ROLL (m)']);
        $colPrice  = $this->findLastCol($headers, ['HARGA']);

        $brand    = $this->findOrCreateBrand('MILLIARD');
        // MILLIARD = selang/pipa flexible; kategori LAINNYA
        $category = $this->findOrCreateCategory('LAINNYA');
        $uom      = $this->findUom($uomCode); // ROLL

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $produk= trim((string)($row[$colProduk] ?? ''));
            if (! $produk || $this->isLikelyHeader($produk)) {
                continue;
            }
            $jenis = trim((string)($row[$colJenis] ?? ''));
            $diam  = trim((string)($row[$colDiam] ?? ''));
            $mRoll = $this->numericVal($row[$colMRoll] ?? '');
            $price = $colPrice !== null ? $this->parsePrice($row[$colPrice] ?? '') : null;

            $attrs = array_filter([
                'diameter_inch'    => $diam ?: null,
                'length_per_roll_m'=> $mRoll ?: null,
                'jenis'            => $jenis ?: null,
                'produk'           => $produk,
            ]);

            $name    = $this->buildName($produk, $jenis, $diam ? "({$diam}\")" : null, $mRoll ? "{$mRoll}m/roll" : null);
            $skuCode = $this->buildSku('MILLIARD', 'LAINNYA', $this->slug($jenis), $this->slug($diam));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $price, 'MILLIARD_DEFAULT');
        }
    }

    private function importMu(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers  = $this->getHeaders($rows, $headerRow);
        $colMerk  = $this->findCol($headers, ['MERK']);
        $colProd  = $this->findCol($headers, ['PRODUK']);
        $colJenis = $this->findCol($headers, ['JENIS']);
        $colNetto = $this->findCol($headers, ['NETTO']);

        $brand    = $this->findOrCreateBrand('MU');
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $produk = trim((string)($row[$colProd] ?? ''));
            if (! $produk || $this->isLikelyHeader($produk)) {
                continue;
            }
            $merk  = trim((string)($row[$colMerk] ?? 'MU'));
            $jenis = trim((string)($row[$colJenis] ?? ''));
            $netto = $colNetto !== null ? $this->parsePrice($row[$colNetto] ?? '') : null;

            $attrs = array_filter([
                'merk'   => $merk ?: null,
                'produk' => $produk,
                'jenis'  => $jenis ?: null,
            ]);

            $name    = $this->buildName('MU', $produk, $jenis);
            $skuCode = $this->buildSku('MU', 'MORTAR', $this->slug($produk), $this->slug($jenis));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $netto, 'MU_DEFAULT');
        }
    }

    private function importNoDrop(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers  = $this->getHeaders($rows, $headerRow);
        $colBrand = $this->findCol($headers, ['BRANDS', 'BRAND']);
        $colItem  = $this->findCol($headers, ['ITEM']);
        $colKemas = $this->findCol($headers, ['KEMASAN']);
        $colNetto = $this->findCol($headers, ['NETTO']);

        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row  = $rows[$i];
            $item = trim((string)($row[$colItem] ?? ''));
            if (! $item || $this->isLikelyHeader($item)) {
                continue;
            }
            $brandName = trim((string)($row[$colBrand] ?? 'NO DROP'));
            $kemasan   = trim((string)($row[$colKemas] ?? ''));
            $netto     = $colNetto !== null ? $this->parsePrice($row[$colNetto] ?? '') : null;

            [$packSize, $packUnit] = $this->parseKemasan($kemasan);
            $brand = $this->findOrCreateBrand($brandName ?: 'NO DROP');

            $attrs = array_filter([
                'item_name'     => $item,
                'pack_size'     => $packSize,
                'pack_size_unit'=> $packUnit ?: null,
            ]);

            $name    = $this->buildName($item, $kemasan);
            $skuCode = $this->buildSku('NODROP', 'CHEM', $this->slug($item), $this->slug($kemasan));

            $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, $netto, 'NODROP_DEFAULT');
        }
    }

    private function importPowerPralon(array $rows, int $headerRow, string $brandCode, string $catCode, string $uomCode, string $sheet): void
    {
        $headers  = $this->getHeaders($rows, $headerRow);
        $colProd  = $this->findCol($headers, ['Produk', 'PRODUK']);
        $colTipe  = $this->findCol($headers, ['TIPE']);
        $colModel = $this->findCol($headers, ['MODEL']);
        $colInch  = $this->findCol($headers, ['Inch', 'INCH']);
        $colMm    = $this->findCol($headers, ['mm', 'MM']);
        $colNett1 = $this->findCol($headers, ['NETT 7,5%', 'NETT 7.5%', 'NETT 7']);
        $colNett2 = $this->findCol($headers, ['NETT 18%', 'NETT 18']);

        $brand    = $this->findOrCreateBrand('POWER PRALON');
        $category = $this->findOrCreateCategory($catCode);
        $uom      = $this->findUom($uomCode);

        for ($i = $headerRow; $i < count($rows); $i++) {
            $row  = $rows[$i];
            $tipe = trim((string)($row[$colTipe] ?? ''));
            if (! $tipe || $this->isLikelyHeader($tipe)) {
                continue;
            }
            $model = trim((string)($row[$colModel] ?? ''));
            $inch  = trim((string)($row[$colInch] ?? ''));
            $mm    = $this->numericVal($row[$colMm] ?? '');
            $nett1 = $colNett1 !== null ? $this->parsePrice($row[$colNett1] ?? '') : null;
            $nett2 = $colNett2 !== null ? $this->parsePrice($row[$colNett2] ?? '') : null;

            $attrs = array_filter([
                'tipe'          => $tipe,
                'model'         => $model ?: null,
                'diameter_inch' => $inch ?: null,
                'diameter_mm'   => $mm ?: null,
            ]);

            $name    = $this->buildName('PIPA POWER PRALON', $tipe, $model, $inch ? "({$inch}\")" : null, $mm ? "{$mm}mm" : null);
            $skuCode = $this->buildSku('POWER', 'PIPA', $this->slug($tipe), $this->slug($model), $this->canonicalInch($inch));

            $item = $this->upsertItem($skuCode, $name, $brand, $category, $uom, $attrs, $row, $sheet, $i, null, null);
            if ($item) {
                if ($nett1) $this->upsertPrice($item, $nett1, 'POWER_PRALON_NETT_7_5');
                if ($nett2) $this->upsertPrice($item, $nett2, 'POWER_PRALON_NETT_18');
            }
        }
    }

    // ─── Core upsert ────────────────────────────────────────────────────────

    private function upsertItem(
        string $skuCode,
        string $name,
        Brand $brand,
        Category $category,
        Uom $uom,
        array $attrs,
        array $rawRow,
        string $sheet,
        int $rowNum,
        ?float $price,
        ?string $priceListName
    ): ?Item {
        if (! $skuCode || ! $name) {
            $this->failed++;
            $this->errors[] = "[{$sheet}#{$rowNum}] SKU code atau nama kosong";
            return null;
        }

        // Suffix untuk deduplication
        $baseSku = strtoupper($skuCode);
        $sku     = $baseSku;
        $attempt = 1;
        while ($attempt <= 5 && Item::where('sku_code', $sku)->exists()) {
            $existing = Item::where('sku_code', $sku)->first();
            if ($existing->name === $name) {
                $this->skipped++;
                return $existing;
            }
            $sku = $baseSku . '-V' . (++$attempt);
        }

        try {
            $item = Item::create([
                'sku_code'          => $sku,
                'name'              => $name,
                'brand_id'          => $brand->id,
                'category_id'       => $category->id,
                'base_uom_id'       => $uom->id,
                'pack_qty'          => 1,
                'tax_included'      => true,
                'is_active'         => true,
                'attributes_json'   => $attrs ?: null,
                'raw_source_json'   => [
                    'sheet'      => $sheet,
                    'row_number' => $rowNum + 1,
                    'raw_values' => array_values($rawRow),
                ],
            ]);

            if ($price !== null && $price > 0 && $priceListName) {
                $this->upsertPrice($item, $price, $priceListName);
            }

            $this->created++;
            return $item;
        } catch (\Throwable $e) {
            $this->failed++;
            $this->errors[] = "[{$sheet}#{$rowNum}] {$skuCode}: {$e->getMessage()}";
            return null;
        }
    }

    private function upsertPrice(Item $item, float $price, string $listName): void
    {
        $pl = $this->getPriceList($listName);
        PriceListItem::updateOrCreate(
            ['price_list_id' => $pl->id, 'item_id' => $item->id],
            ['sell_price' => $price]
        );
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function getHeaders(array $rows, int $headerRow): array
    {
        return array_map('trim', (array)($rows[$headerRow - 1] ?? []));
    }

    private function findCol(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $cand) {
            foreach ($headers as $idx => $h) {
                if (strcasecmp(trim($h), $cand) === 0) {
                    return $idx;
                }
            }
        }
        // partial match
        foreach ($candidates as $cand) {
            foreach ($headers as $idx => $h) {
                if (stripos($h, $cand) !== false) {
                    return $idx;
                }
            }
        }
        return null;
    }

    private function findLastCol(array $headers, array $candidates): ?int
    {
        $last = null;
        foreach ($candidates as $cand) {
            foreach ($headers as $idx => $h) {
                if (stripos($h, $cand) !== false) {
                    $last = $idx;
                }
            }
        }
        return $last;
    }

    private function parsePrice($val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        // Remove Rp, dots as thousand sep, trailing ,- etc.
        $str = preg_replace('/[Rp\s\.,\-]/u', '', (string)$val);
        $str = preg_replace('/[^\d]/', '', $str);
        return $str !== '' ? (float)$str : null;
    }

    private function numericVal($val): float
    {
        return (float)preg_replace('/[^\d\.]/', '', (string)$val);
    }

    private function parsePowerWatt($val): ?float
    {
        if (preg_match('/(\d+[\.,]?\d*)\s*[Ww]/', (string)$val, $m)) {
            return (float)str_replace(',', '.', $m[1]);
        }
        return $this->numericVal($val) ?: null;
    }

    private function parseKemasan(string $kemasan): array
    {
        if (preg_match('/^(\d+[\.,]?\d*)\s*(.+)$/', trim($kemasan), $m)) {
            return [(float)str_replace(',', '.', $m[1]), trim($m[2])];
        }
        return [1, $kemasan];
    }

    private function buildName(string ...$parts): string
    {
        $parts = array_filter(array_map('trim', $parts));
        return implode(' ', $parts);
    }

    private function buildSku(string ...$parts): string
    {
        $parts = array_filter(array_map(function ($p) {
            return preg_replace('/[^A-Z0-9]/', '', strtoupper((string)$p));
        }, $parts));
        return implode('-', $parts);
    }

    private function slug(string $val): string
    {
        $val = strtoupper(trim($val));
        $val = preg_replace('/[^A-Z0-9\/]/', '', $val);
        return str_replace('/', '', $val);
    }

    private function canonicalInch(string $inch): string
    {
        $inch = trim($inch);
        $inch = str_replace(['"', "'", ' '], '', $inch);
        $inch = str_replace('/', '_', $inch);
        return preg_replace('/[^0-9_]/', '', $inch);
    }

    private function cleanVal(string $val): string
    {
        return strtolower(trim($val));
    }

    private function isLikelyHeader(string $val): bool
    {
        $val = strtolower(trim($val));
        return in_array($val, ['produk', 'jenis', 'type', 'tipe', 'merk', 'brand', 'item', 'ukuran', 'no', 'no.']);
    }

    private function findOrCreateBrand(string $name): Brand
    {
        $key = strtolower($name);
        if (! isset($this->brandCache[$key])) {
            $this->brandCache[$key] = Brand::firstOrCreate(['name' => trim($name)], ['is_active' => true]);
        }
        return $this->brandCache[$key];
    }

    private function findOrCreateCategory(string $code): Category
    {
        if (! isset($this->categoryCache[$code])) {
            $this->categoryCache[$code] = Category::firstOrCreate(
                ['code' => $code],
                ['name' => ucwords(strtolower(str_replace('_', ' ', $code))), 'is_active' => true]
            );
        }
        return $this->categoryCache[$code];
    }

    private function findUom(string $code): Uom
    {
        if (! isset($this->uomCache[$code])) {
            $this->uomCache[$code] = Uom::where('code', $code)->firstOrFail();
        }
        return $this->uomCache[$code];
    }

    private function getPriceList(string $name): PriceList
    {
        if (! isset($this->priceListCache[$name])) {
            $this->priceListCache[$name] = PriceList::firstOrCreate(
                ['name' => $name],
                ['scope' => 'GLOBAL', 'effective_from' => now()->toDateString(), 'is_active' => true]
            );
        }
        return $this->priceListCache[$name];
    }

    private function result(array $extraErrors = []): array
    {
        return [
            'created' => $this->created,
            'skipped' => $this->skipped,
            'failed'  => $this->failed,
            'errors'  => array_merge($this->errors, array_values($extraErrors)),
        ];
    }
}
