<?php

namespace App\Http\Controllers;

use App\Models\Prices;
use App\Models\RecordMaterialLines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PriceController extends Controller
{
    public function index()
    {
        return view('upload_price');
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());

        /** @var Worksheet $sheet */
        $sheet = $spreadsheet->getSheet(0);

        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $norm = function (string $s) {
            return str_replace([' ', '_', '-'], '', strtolower(trim($s)));
        };

        $headerMap = [];
        for ($c = 1; $c <= $highestColIndex; $c++) {
            $colL  = Coordinate::stringFromColumnIndex($c);
            $label = (string)$sheet->getCell($colL . '1')->getValue();
            if ($label !== '') $headerMap[$norm($label)] = $c;
        }

        $materialKeys  = ['material', 'materialno', 'materialid', 'materialcode'];
        $unitPriceKeys = ['unitprice', 'unit_price', 'price', 'unitharga', 'unitprc'];

        $colMaterial = null;
        $colUnitPrice = null;
        foreach ($materialKeys as $k)   if (isset($headerMap[$k])) {
            $colMaterial  = $headerMap[$k];
            break;
        }
        foreach ($unitPriceKeys as $k)  if (isset($headerMap[$k])) {
            $colUnitPrice = $headerMap[$k];
            break;
        }

        if (!$colMaterial || !$colUnitPrice) {
            return response()->json([
                'status'  => 'bad_header',
                'message' => 'Header “Material” dan/atau “UnitPrice” tidak ditemukan di baris 1.'
            ], 422);
        }

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
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
                $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : null;
        };

        $highestRow   = $sheet->getHighestDataRow();
        $items        = [];
        $colMatL      = Coordinate::stringFromColumnIndex($colMaterial);
        $colUnitPrL   = Coordinate::stringFromColumnIndex($colUnitPrice);

        for ($r = 2; $r <= $highestRow; $r++) {
            $mat = trim((string)$sheet->getCell($colMatL . $r)->getValue());
            if ($mat === '') continue;

            $cell  = $sheet->getCell($colUnitPrL . $r);
            $value = method_exists($cell, 'getCalculatedValue') ? $cell->getCalculatedValue() : $cell->getValue();
            $num   = $toNumber($value);

            $items[] = [
                'material'   => $mat,
                'unit_price' => is_null($num) ? 'N/A' : $num,
            ];
        }

        if (empty($items)) {
            return response()->json(['status' => 'empty_file'], 422);
        }

        $grouped = collect($items)
            ->map(function ($row) {
                $row['material']      = trim($row['material']);
                $row['material_norm'] = strtolower($row['material']);
                return $row;
            })
            ->groupBy('material_norm')
            ->map(function ($rows) {
                $numeric = $rows->filter(fn($r) => is_numeric($r['unit_price']));
                return $numeric->isNotEmpty()
                    ? $numeric->sortByDesc('unit_price')->first()
                    : $rows->first();
            });

        if ($grouped->isEmpty()) {
            return response()->json(['status' => 'empty'], 422);
        }

        $today = today();
        $now   = now();

        $existingToday = Prices::query()
            ->whereDate('created_at', $today)
            ->get()
            ->keyBy(function ($p) {
                return strtolower(trim($p->material));
            });

        $toInsert = [];
        $updated  = 0;
        $details  = [];

        DB::transaction(function () use ($grouped, $existingToday, $now, &$toInsert, &$updated, &$details) {
            foreach ($grouped as $norm => $row) {
                $newPrice = is_numeric($row['unit_price'])
                    ? round($row['unit_price'], 2)
                    : $row['unit_price'];

                if (isset($existingToday[$norm])) {
                    $price = $existingToday[$norm];
                    $price->unit_price = $newPrice;
                    $price->updated_at = $now;
                    $price->save();

                    $updated++;
                    $details[] = [
                        'material'   => $row['material'],
                        'unit_price' => $newPrice,
                        'status'     => 'updated_today'
                    ];
                } else {
                    $toInsert[] = [
                        'material'   => $row['material'],
                        'unit_price' => $newPrice,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $details[] = [
                        'material'   => $row['material'],
                        'unit_price' => $newPrice,
                        'status'     => 'queued_for_insert'
                    ];
                }
            }

            if (!empty($toInsert)) {
                Prices::insert($toInsert);
            }
        });

        return response()->json([
            'status'   => 'ok',
            'inserted' => count($toInsert),
            'updated'  => $updated,
            'details'  => $details,
        ]);
    }
}
