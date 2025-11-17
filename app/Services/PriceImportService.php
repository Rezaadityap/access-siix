<?php

namespace App\Services;

use App\Models\Prices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PriceImportService
{
    public function importFromFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);

        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $norm = fn(string $s) => str_replace([' ', '_', '-'], '', strtolower(trim($s)));

        // Map header
        $headerMap = [];
        for ($c = 1; $c <= $highestColIndex; $c++) {
            $colL  = Coordinate::stringFromColumnIndex($c);
            $label = (string)$sheet->getCell($colL . '1')->getValue();
            if ($label !== '') $headerMap[$norm($label)] = $c;
        }

        $materialKeys  = ['material', 'materialno', 'materialid', 'materialcode'];
        $unitPriceKeys = ['UnitPrice', 'unit_price', 'price', 'unitharga', 'unitprc'];

        $colMaterial = null;
        $colUnitPrice = null;
        foreach ($materialKeys as $k)  if (isset($headerMap[$k])) {
            $colMaterial = $headerMap[$k];
            break;
        }
        foreach ($unitPriceKeys as $k) if (isset($headerMap[$k])) {
            $colUnitPrice = $headerMap[$k];
            break;
        }

        Log::info('DEBUG headerMap', $headerMap);
        Log::info('Cols detected', ['material' => $colMaterial, 'unit_price' => $colUnitPrice]);

        if (!$colMaterial || !$colUnitPrice) {
            return ['status' => 'bad_header', 'message' => 'Header “Material”/“UnitPrice” tidak ditemukan', 'inserted' => 0, 'updated' => 0, 'details' => []];
        }

        // Normalisasi angka
        $toNumber = function ($raw) {
            if ($raw === null) return null;
            $s = trim((string)$raw);
            if ($s === '' || $s === '-') return null;

            if (strpos($s, '.') !== false && strpos($s, ',') !== false) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
                return is_numeric($s) ? (float)$s : null;
            }
            if (strpos($s, ',') !== false) {
                $s = str_replace(',', '.', $s);
                return is_numeric($s) ? (float)$s : null;
            }
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) $s = str_replace('.', '', $s);
            return is_numeric($s) ? (float)$s : null;
        };

        // Ambil rows
        $highestRow = $sheet->getHighestDataRow();
        $colMatL    = Coordinate::stringFromColumnIndex($colMaterial);
        $colUnitPrL = Coordinate::stringFromColumnIndex($colUnitPrice);

        $items = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $mat = trim((string)$sheet->getCell($colMatL . $r)->getValue());
            if ($mat === '') continue;

            $cell  = $sheet->getCell($colUnitPrL . $r);
            $value = method_exists($cell, 'getCalculatedValue') ? $cell->getCalculatedValue() : $cell->getValue();
            $num   = $toNumber($value);

            $items[] = [
                'material'   => $mat,
                'unit_price' => is_null($num) ? null : (float)$num,
            ];
        }

        if (empty($items)) return ['status' => 'empty_file', 'inserted' => 0, 'updated' => 0, 'details' => []];

        // Kelompokkan per material, pilih unit_price tertinggi
        $grouped = collect($items)
            ->filter(fn($r) => $r['material'] !== '')
            ->map(fn($r) => [
                'material'      => trim($r['material']),
                'material_norm' => strtolower(trim($r['material'])),
                'unit_price'    => $r['unit_price'],
            ])
            ->groupBy('material_norm')
            ->map(function ($rows) {
                // Ambil yang angka saja; pilih tertinggi
                $numeric = $rows->filter(fn($r) => is_numeric($r['unit_price']));
                return $numeric->isNotEmpty()
                    ? $numeric->sortByDesc('unit_price')->first()
                    : $rows->first(); // fallback kalau semua N/A
            })
            ->values();

        if ($grouped->isEmpty()) return ['status' => 'empty', 'inserted' => 0, 'updated' => 0, 'details' => []];

        // Siapkan upsert
        $now = now();
        $payload = $grouped->map(function ($r) use ($now) {
            return [
                'material'   => $r['material'],
                'unit_price' => is_numeric($r['unit_price']) ? round($r['unit_price'], 2) : null,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        })->all();

        // Hitung existing untuk metrik inserted/updated
        $materials = array_map(fn($p) => strtolower($p['material']), $payload);
        $existing = Prices::whereIn(DB::raw('LOWER(material)'), $materials)->pluck('material')->map(fn($m) => strtolower($m))->flip();

        // Upsert by 'material'
        DB::transaction(function () use ($payload) {
            Prices::upsert($payload, ['material'], ['unit_price', 'updated_at']);
        });

        $inserted = 0;
        $updated = 0;
        $details = [];
        foreach ($payload as $row) {
            $isUpdate = $existing->has(strtolower($row['material']));
            $isUpdate ? $updated++ : $inserted++;
            $details[] = [
                'material'   => $row['material'],
                'unit_price' => $row['unit_price'],
                'status'     => $isUpdate ? 'updated' : 'inserted',
            ];
        }

        return ['status' => 'ok', 'inserted' => $inserted, 'updated' => $updated, 'details' => $details];
    }
}
