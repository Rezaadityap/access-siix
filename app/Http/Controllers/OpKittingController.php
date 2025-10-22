<?php

namespace App\Http\Controllers;

use App\Models\RecordMaterialTrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OpKittingController extends Controller
{
    public function index()
    {
        return view('op-kitting');
    }

    public function store(Request $request)
    {
        $forms = $request->input('forms');

        if (!$forms || !is_array($forms)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No form data was submitted!'
            ], 422);
        }

        $validator = Validator::make(
            ['forms' => $forms],
            [
                'forms.*.po_number' => [
                    'required',
                    Rule::unique('record_material_trans', 'po_number'),
                ],
            ],
            [
                'forms.*.po_number.unique' => 'PO Number :input has already been registered.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'duplicate',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $saveRecords = [];

        foreach ($forms as $form) {
            if (empty($form['po_number']) || empty($form['model'])) {
                continue;
            }

            $record = RecordMaterialTrans::create([
                'user_id'       => Auth::id(),
                'area'          => Auth::user()->employee->department,
                'line'          => $form['line'] ?? '-',
                'date'          => now()->toDateString(),
                'po_number'     => $form['po_number'] ?? '-',
                'model'         => $form['model'] ?? '-',
                'lot_size'      => $form['lot_size'] ?? '-',
                'act_lot_size'  => $form['act_lot_size'] ?? null,
            ]);

            $saveRecords[] = $record;

            $filePath = $form['file_path'] ?? null;

            if (!$filePath || !Storage::exists($filePath)) {
                Log::error("File not found: {$filePath}");
                continue;
            }

            $content = Storage::get($filePath);

            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            }

            $content = preg_replace('/^\xEF\xBB\xBF|\xFF\xFE/', '', $content);

            $lines = preg_split('/\r\n|\r|\n/', $content);
            $data = [];
            $startReading = false;

            foreach ($lines as $rawLine) {
                $line = trim((string) $rawLine);
                if ($line === '') continue;

                // Mulai baca setelah ketemu header
                if (str_contains($line, '|Item|')) {
                    $startReading = true;
                    continue;
                }

                // Baca baris material valid (|0010|, |00020|, dst.)
                if ($startReading && preg_match('/^\|\s*\d{3,5}\s*\|/', $line)) {
                    $columns = array_map('trim', explode('|', $line));

                    if (count($columns) >= 8) {
                        $POItem     = $columns[1] ?? '-';
                        $partNumber = str_replace(' ', '', $columns[3] ?? '-');
                        $partDesc   = $columns[4] ?? '-';
                        $qtyRaw     = $columns[7] ?? '0 PCS';

                        if (preg_match('/([\d, ]+)\s*(PCS|KG)?/i', $qtyRaw, $matches)) {
                            $qtyValue = str_replace([',', ' '], '', $matches[1]);
                            $unit     = strtoupper(trim($matches[2] ?? 'PCS'));
                        } else {
                            $qtyValue = 0;
                            $unit = 'PCS';
                        }

                        if ($unit !== 'KG') {
                            $data[] = [
                                'record_material_trans_id' => $record->id,
                                'po_item'       => $POItem,
                                'material'      => $partNumber,
                                'material_desc' => $partDesc,
                                'rec_qty'       => $qtyValue,
                                'satuan'        => $unit,
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ];
                        }
                    }
                }
            }

            // === Simpan ke tabel record_material_lines ===
            if (!empty($data)) {
                DB::table('record_material_lines')->insert($data);
                Log::info("Imported PO {$form['po_number']} - total lines: " . count($data));
            } else {
                Log::warning("No valid lines parsed for PO {$form['po_number']}.");
            }

            Log::info("Preview first lines of file {$form['po_number']}: ", array_slice($lines, 0, 10));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All data has been successfully saved, including line details.',
            'data' => $saveRecords
        ]);
    }

    public function upload(Request $request)
    {
        $uploaded = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('public/imports');
                $fileName = $file->getClientOriginalName();
                $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
                $parts = explode(';', $nameWithoutExt);
                $model = trim($parts[0] ?? '-');
                $po_number = isset($parts[1]) ? preg_replace('/\D/', '', $parts[1]) : '-';

                $uploaded[] = [
                    'model' => $model,
                    'po_number' => $po_number,
                    'path' => $path,
                ];
            }
        }

        return response()->json(['files' => $uploaded]);
    }

    public function getRecord($po_numbers)
    {
        $records = DB::table('record_material_lines')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->select(
                'record_material_lines.material',
                'record_material_lines.material_desc',
                DB::raw('SUM(record_material_lines.rec_qty) as total_qty'),
                'record_material_lines.satuan',
                'record_material_trans.model',
                'record_material_trans.po_number',
                'record_material_trans.date'
            )
            ->where('record_material_trans.po_number', $po_numbers)
            ->groupBy(
                'record_material_lines.material',
                'record_material_lines.material_desc',
                'record_material_lines.satuan',
                'record_material_trans.model',
                'record_material_trans.po_number',
                'record_material_trans.date'
            )
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "No data found for PO: $po_numbers"
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $records
        ]);
    }

    public function getRecordSearch(Request $request)
    {
        $query = RecordMaterialTrans::select(
            'id',
            'po_number',
            'area',
            'line',
            'model',
            'date',
            'lot_size',
            'act_lot_size'
        );

        $date = $request->date ?: date('Y-m-d');

        $query->where('date', $date);
        $records = $query->get();

        return response()->json($records);
    }
}
