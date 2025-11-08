<?php

namespace App\Http\Controllers;

use App\Models\GroupRecords;
use App\Models\RecordBatch;
use App\Models\RecordMaterialLines;
use App\Models\RecordMaterialTrans;
use Carbon\Carbon;
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
        $lastGroupId = RecordMaterialTrans::max('group_id') ?? 0;
        $newGroupId = $lastGroupId + 1;

        foreach ($forms as $form) {
            if (empty($form['po_number']) || empty($form['model'])) {
                continue;
            }

            $record = RecordMaterialTrans::create([
                'user_id'       => Auth::id(),
                'group_id'      => $newGroupId,
                'area'          => Auth::user()->employee->department,
                'line'          => $form['line'] ?? '-',
                'date'          => now()->toDateString(),
                'po_number'     => $form['po_number'] ?? '-',
                'model'         => $form['model'] ?? '-',
                'lot_size'      => $form['lot_size'] ?? '-',
                'act_lot_size'  => $form['act_lot_size'] ?? null
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

                        if (preg_match('/([\d., ]+)\s*(PCS|KG)?/i', $qtyRaw, $matches)) {
                            $rawQty = trim($matches[1]);
                            $unit = strtoupper(trim($matches[2] ?? 'PCS'));

                            // Format khusus: 445.500 -> 445.5
                            if (preg_match('/^\d+\.\d{3}$/', $rawQty)) {
                                $parts = explode('.', $rawQty);
                                $qtyValue = $parts[0] . '.' . $parts[1][0]; // ambil digit pertama setelah titik
                            }
                            // Format ribuan dengan koma: 1,782 -> 1782
                            else if (strpos($rawQty, ',') !== false) {
                                $qtyValue = str_replace(',', '', $rawQty); // hapus koma saja
                            }
                            // Format normal: hapus titik/spasi ribuan
                            else {
                                $qtyValue = str_replace(['.', ' '], '', $rawQty);
                            }
                        } else {
                            $qtyValue = '0';
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

            // Simpan ke tabel record_material_lines
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
            ->leftJoin('prices', 'prices.material', '=', 'record_material_lines.material')
            ->leftJoin(DB::raw('(
            SELECT record_material_lines_id,
                   SUM(qty_batch_wh) AS total_wh,
                   COUNT(*) AS count_wh
            FROM record_batch
            GROUP BY record_material_lines_id
        ) rb'), 'rb.record_material_lines_id', '=', 'record_material_lines.id')
            ->leftJoin(DB::raw('(
            SELECT record_material_lines_id,
                   SUM(qty_batch_smd) AS total_smd,
                   COUNT(*) AS count_smd
            FROM record_batch_smd
            GROUP BY record_material_lines_id
        ) rs'), 'rs.record_material_lines_id', '=', 'record_material_lines.id')
            ->leftJoin(DB::raw('(
            SELECT record_material_lines_id,
                   SUM(qty_batch_sto) AS total_sto,
                   COUNT(*) AS count_sto
            FROM record_batch_sto
            GROUP BY record_material_lines_id
        ) rsto'), 'rsto.record_material_lines_id', '=', 'record_material_lines.id')
            ->leftJoin(DB::raw('(
            SELECT record_material_lines_id,
                   SUM(qty_batch_mar) AS total_mar
            FROM record_batch_mar
            GROUP BY record_material_lines_id
        ) rma'), 'rma.record_material_lines_id', '=', 'record_material_lines.id')
            ->leftJoin(DB::raw('(
            SELECT record_material_lines_id,
                   SUM(qty_batch_mismatch) AS total_mismatch
            FROM record_batch_mismatch
            GROUP BY record_material_lines_id
        ) rmm'), 'rmm.record_material_lines_id', '=', 'record_material_lines.id')

            ->select(
                'record_material_lines.material',
                'record_material_lines.material_desc',
                DB::raw('SUM(record_material_lines.rec_qty) AS total_qty'),
                DB::raw('COALESCE(rb.total_wh, 0) AS receive_qty'),
                DB::raw('COALESCE(rs.total_smd, 0) AS smd_qty'),
                DB::raw('COALESCE(rsto.total_sto, 0) AS sto_qty'),
                DB::raw('COALESCE(rma.total_mar, 0) AS mar_qty'),
                DB::raw('COALESCE(rmm.total_mismatch, 0) AS mm_qty'),
                DB::raw('COALESCE(rb.count_wh, 0) AS wh_scans'),
                DB::raw('COALESCE(rs.count_smd, 0) AS smd_scans'),
                DB::raw('COALESCE(rsto.count_sto, 0) AS sto_scans'),
                'record_material_lines.satuan',
                'record_material_trans.model',
                'record_material_trans.po_number',
                'record_material_trans.date',
                'record_material_trans.cavity',
                DB::raw('ROUND(COALESCE(prices.unit_price, 0), 2) AS unit_price')
            )

            ->where('record_material_trans.po_number', $po_numbers)
            ->where(function ($query) {
                $query->whereNull('record_material_lines.status')
                    ->orWhere('record_material_lines.status', 1);
            })

            ->groupBy(
                'record_material_lines.material',
                'record_material_lines.material_desc',
                'record_material_lines.satuan',
                'record_material_trans.model',
                'record_material_trans.po_number',
                'record_material_trans.date',
                'record_material_trans.cavity',
                'rb.total_wh',
                'rs.total_smd',
                'rsto.total_sto',
                'rma.total_mar',
                'rmm.total_mismatch',
                'rb.count_wh',
                'rs.count_smd',
                'rsto.count_sto',
                'prices.unit_price'
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
            'group_id',
            'po_number',
            'area',
            'line',
            'model',
            'date',
            'lot_size',
            'act_lot_size'
        );

        $startDate = $request->start_date ?: date('Y-m-d');
        $endDate = $request->end_date ?: date('Y-m-d');

        $query->whereBetween('date', [$startDate, $endDate]);
        $records = $query->get();

        return response()->json($records);
    }

    public function checkMaterial(Request $request)
    {
        $po_numbers = $request->input('po_numbers', []);
        $rows = DB::table('record_material_lines')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $po_numbers)
            ->select('record_material_lines.material')
            ->groupBy('record_material_lines.material')
            ->pluck('material');

        return response()->json([
            'status' => 'success',
            'materials' => $rows,
        ]);
    }

    public function checkBatch(Request $request)
    {
        $batches = (array) $request->input('batches', []);
        $type = strtoupper($request->input('type', ''));

        // cari di tiga tabel sekaligus, tanpa tipe (lebih aman)
        $dups = collect($batches)->unique()->filter(function ($b) {
            return DB::table('record_batch')->where('batch_wh', $b)->exists()
                || DB::table('record_batch_smd')->where('batch_smd', $b)->exists()
                || DB::table('record_batch_sto')->where('batch_sto', $b)->exists();
        })->values();

        return response()->json([
            'status' => 'success',
            'duplicates' => $dups,
        ]);
    }

    public function saveWhMaterial(Request $request)
    {
        $poList = $request->input('po_list', []);
        $scanned = $request->input('scanned', []);

        if (empty($poList) || empty($scanned)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO or scanned data provided.'
            ], 422);
        }

        $savedCount = 0;

        foreach ($scanned as $scan) {
            $batch = $scan['batch'];
            $material = $scan['material'];
            $qty = $scan['qty'];
            $description = $scan['description'];

            $recordLine = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id')
                ->whereIn('record_material_trans.po_number', array_column($poList, 'po_number'))
                ->where('record_material_lines.material', $material)
                ->first();

            if ($recordLine) {
                DB::table('record_batch')->insert([
                    'record_material_lines_id' => $recordLine->rml_id,
                    'batch_wh' => $batch,
                    'qty_batch_wh' => $qty,
                    'batch_wh_desc' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $savedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "$savedCount batch record(s) successfully saved.",
        ]);
    }

    public function saveRackSmd(Request $request)
    {
        $poList = $request->input('po_list', []);
        $scanned = $request->input('scanned', []);

        if (empty($poList) || empty($scanned)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO or scanned data provided.'
            ], 422);
        }

        $savedCount = 0;

        foreach ($scanned as $scan) {
            $batch = $scan['batch'];
            $material = $scan['material'];
            $qty = $scan['qty'];
            $description = $scan['description'];

            $recordLine = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id')
                ->whereIn('record_material_trans.po_number', array_column($poList, 'po_number'))
                ->where('record_material_lines.material', $material)
                ->first();

            if ($recordLine) {
                DB::table('record_batch_smd')->insert([
                    'record_material_lines_id' => $recordLine->rml_id,
                    'batch_smd' => $batch,
                    'qty_batch_smd' => $qty,
                    'batch_smd_desc' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $savedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "$savedCount batch record(s) successfully saved.",
        ]);
    }

    public function saveRackSto(Request $request)
    {
        $poList = $request->input('po_list', []);
        $scanned = $request->input('scanned', []);

        if (empty($poList) || empty($scanned)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO or scanned data provided.'
            ], 422);
        }

        $savedCount = 0;

        foreach ($scanned as $scan) {
            $batch = $scan['batch'];
            $material = $scan['material'];
            $qty = $scan['qty'];
            $description = $scan['description'];

            $recordLine = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id')
                ->whereIn('record_material_trans.po_number', array_column($poList, 'po_number'))
                ->where('record_material_lines.material', $material)
                ->first();

            if ($recordLine) {
                DB::table('record_batch_sto')->insert([
                    'record_material_lines_id' => $recordLine->rml_id,
                    'batch_sto' => $batch,
                    'qty_batch_sto' => $qty,
                    'batch_sto_desc' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $savedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "$savedCount batch record(s) successfully saved.",
        ]);
    }

    public function saveAfter(Request $request)
    {
        $poList = $request->input('po_list', []);
        $scanned = $request->input('scanned', []);
        $actualLotSize = $request->input('actual_lot_size');
        $cavity = $request->input('cavity');
        $change_model = $request->input('changeModel');

        // === LOG STEP 1: Input awal ===
        Log::info('=== saveAfter() called ===', [
            'poList' => $poList,
            'scanned' => $scanned,
            'actualLotSize' => $actualLotSize,
            'cavity' => $cavity,
            'change_model' => $change_model
        ]);

        if (empty($poList) || empty($scanned)) {
            Log::warning('saveAfter(): No PO or scanned data provided', [
                'poList' => $poList,
                'scanned' => $scanned,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No PO or scanned data provided.'
            ], 422);
        }

        $savedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($scanned as $scan) {
                $batch = $scan['batch'];
                $material = $scan['material'];
                $qty = $scan['qty'];
                $description = $scan['description'];

                Log::info('saveAfter(): Processing scan', [
                    'batch' => $batch,
                    'material' => $material,
                    'qty' => $qty,
                    'description' => $description,
                ]);

                $recordLine = DB::table('record_material_lines')
                    ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                    ->select('record_material_lines.id as rml_id', 'record_material_trans.id as trans_id', 'record_material_trans.po_number')
                    ->whereIn('record_material_trans.po_number', array_column($poList, 'po_number'))
                    ->where('record_material_lines.material', $material)
                    ->first();

                if ($recordLine) {
                    Log::info('saveAfter(): Found record line', [
                        'rml_id' => $recordLine->rml_id,
                        'trans_id' => $recordLine->trans_id,
                        'po_number' => $recordLine->po_number,
                    ]);

                    // Insert ke record_batch_mar
                    DB::table('record_batch_mar')->insert([
                        'record_material_lines_id' => $recordLine->rml_id,
                        'batch_mar' => $batch,
                        'qty_batch_mar' => $qty,
                        'batch_mar_desc' => $description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update act_lot_size & cavity di record_material_trans
                    if ($actualLotSize !== null && $actualLotSize !== '') {
                        $poNumbers = collect($poList)->pluck('po_number')->toArray();

                        $updateData = [
                            'act_lot_size' => $actualLotSize,
                            'cavity' => $cavity,
                            'change_model' => $change_model,
                            'updated_at' => now()
                        ];

                        $affected = DB::table('record_material_trans')
                            ->whereIn('po_number', $poNumbers)
                            ->update($updateData);

                        Log::info('saveAfter(): act_lot_size updated in record_material_trans', [
                            'po_numbers' => $poNumbers,
                            'affected_rows' => $affected,
                            'act_lot_size' => $actualLotSize,
                            'cavity' => $cavity,
                            'change_model' => $change_model
                        ]);

                        if ($affected === 0) {
                            $exists = DB::table('record_material_trans')
                                ->whereIn('po_number', $poNumbers)
                                ->count();

                            Log::warning('saveAfter(): act_lot_size not updated in record_material_trans', [
                                'po_numbers' => $poNumbers,
                                'found_count' => $exists,
                            ]);
                        }
                    }

                    $savedCount++;
                } else {
                    Log::warning('saveAfter(): No matching record line found for material', [
                        'material' => $material,
                        'po_numbers' => array_column($poList, 'po_number')
                    ]);
                }
            }

            DB::commit();

            Log::info('=== saveAfter() completed successfully ===', [
                'savedCount' => $savedCount
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "$savedCount batch record(s) successfully saved.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('saveAfter(): Error saving data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error saving data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveMismatch(Request $request)
    {
        $poList = $request->input('po_list', []);
        $scanned = $request->input('scanned', []);

        if (empty($poList) || empty($scanned)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO or scanned data provided.'
            ], 422);
        }

        $savedCount = 0;

        foreach ($scanned as $scan) {
            $batch = $scan['batch'];
            $material = $scan['material'];
            $qty = $scan['qty'];
            $description = $scan['description'];

            $recordLine = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id')
                ->whereIn('record_material_trans.po_number', array_column($poList, 'po_number'))
                ->where('record_material_lines.material', $material)
                ->first();

            if ($recordLine) {
                DB::table('record_batch_mismatch')->insert([
                    'record_material_lines_id' => $recordLine->rml_id,
                    'batch_mismatch' => $batch,
                    'qty_batch_mismatch' => $qty,
                    'batch_mismatch_desc' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $savedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "$savedCount batch record(s) successfully saved.",
        ]);
    }

    public function getBatchHistory(Request $request)
    {
        $poNumbers = $request->input('po');
        if (!$poNumbers) {
            return response()->json(['status' => 'error', 'message' => 'No PO provided']);
        }

        // pastikan poNumbers array
        $poList = is_array($poNumbers) ? $poNumbers : explode(',', $poNumbers);

        // ambil record SMD
        $smd = DB::table('record_batch_smd')
            ->join('record_material_lines', 'record_batch_smd.record_material_lines_id', '=', 'record_material_lines.id')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $poList)
            ->select(
                'record_material_trans.po_number',
                'record_batch_smd.batch_smd as scan_code',
                'record_material_lines.material',
                'record_batch_smd.qty_batch_smd as qty',
                'record_batch_smd.batch_smd_desc as batch_description',
            )
            ->get();

        // ambil record WH
        $wh = DB::table('record_batch')
            ->join('record_material_lines', 'record_batch.record_material_lines_id', '=', 'record_material_lines.id')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $poList)
            ->select(
                'record_material_trans.po_number',
                'record_batch.batch_wh as scan_code',
                'record_material_lines.material',
                'record_batch.qty_batch_wh as qty',
                'record_batch.batch_wh_desc as batch_description'
            )
            ->get();

        // ambil record Sto
        $sto = DB::table('record_batch_sto')
            ->join('record_material_lines', 'record_batch_sto.record_material_lines_id', '=', 'record_material_lines.id')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $poList)
            ->select(
                'record_material_trans.po_number',
                'record_batch_sto.batch_sto as scan_code',
                'record_material_lines.material',
                'record_batch_sto.qty_batch_sto as qty',
                'record_batch_sto.batch_sto_desc as batch_description'
            )
            ->get();

        // ambil record mar
        $mar = DB::table('record_batch_mar')
            ->join('record_material_lines', 'record_batch_mar.record_material_lines_id', '=', 'record_material_lines.id')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $poList)
            ->select(
                'record_material_trans.po_number',
                'record_batch_mar.batch_mar as scan_code',
                'record_material_lines.material',
                'record_batch_mar.qty_batch_mar as qty',
                'record_batch_mar.batch_mar_desc as batch_description'
            )
            ->get();

        // ambil record mismatch
        $mm = DB::table('record_batch_mismatch')
            ->join('record_material_lines', 'record_batch_mismatch.record_material_lines_id', '=', 'record_material_lines.id')
            ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
            ->whereIn('record_material_trans.po_number', $poList)
            ->select(
                'record_material_trans.po_number',
                'record_batch_mismatch.batch_mismatch as scan_code',
                'record_material_lines.material',
                'record_batch_mismatch.qty_batch_mismatch as qty',
                'record_batch_mismatch.batch_mismatch_desc as batch_description'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'smd' => $smd,
            'wh' => $wh,
            'sto' => $sto,
            'mar' => $mar,
            'mm' => $mm
        ]);
    }

    public function deletePO(Request $request)
    {
        $poNumbers = $request->input('po_numbers', []);

        if (empty($poNumbers)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO numbers provided.'
            ], 400);
        }

        // Ambil ID dari record_material_trans berdasarkan po_number
        $transIds = DB::table('record_material_trans')
            ->whereIn('po_number', $poNumbers)
            ->pluck('id');

        if ($transIds->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching PO found in database.'
            ], 404);
        }

        // Ambil ID record_material_lines yang berelasi
        $lineIds = DB::table('record_material_lines')
            ->whereIn('record_material_trans_id', $transIds)
            ->pluck('id');

        if ($lineIds->isNotEmpty()) {
            // Hapus record di batch table yang terkait dengan record_material_lines_id
            DB::table('record_batch')->whereIn('record_material_lines_id', $lineIds)->delete();
            DB::table('record_batch_smd')->whereIn('record_material_lines_id', $lineIds)->delete();
            DB::table('record_batch_sto')->whereIn('record_material_lines_id', $lineIds)->delete();
            DB::table('record_batch_mar')->whereIn('record_material_lines_id', $lineIds)->delete();

            // Hapus record detail material
            DB::table('record_material_lines')->whereIn('id', $lineIds)->delete();
        }

        DB::table('record_material_trans')
            ->whereIn('id', $transIds)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'PO(s) and all related records successfully deleted.',
            'deleted_po' => $poNumbers
        ]);
    }

    public function history(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $selectedModel = $request->get('model');

        $query = RecordMaterialTrans::whereDate('created_at', $date);

        $models = $query->distinct()->pluck('model');

        $record = collect();
        if ($selectedModel) {
            $record = RecordMaterialLines::whereHas('recordMaterialTrans', function ($q) use ($date, $selectedModel) {
                $q->whereDate('created_at', $date)
                    ->where('model', $selectedModel);
            })->with('recordMaterialTrans')->get();
        }

        return view('history-record', compact('record', 'date', 'models', 'selectedModel'));
    }

    public function editHistory($id)
    {
        $record = RecordMaterialLines::with('recordMaterialTrans')->findOrFail($id);
        return view('edit-history-record', compact('record'));
    }

    public function replace(Request $request, $id)
    {
        $request->validate([
            'po_item' => 'required|string|max:50',
            'material' => 'required|string|max:100',
            'material_desc' => 'required|string|max:255',
            'rec_qty' => 'required|numeric|min:1',
            'remarks' => 'required'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $old = RecordMaterialLines::findOrFail($id);
            $old->update(['status' => 2]);

            $newId = RecordMaterialLines::insertGetId([
                'record_material_trans_id' => $old->record_material_trans_id,
                'po_item' => $request->po_item,
                'material' => $request->material,
                'material_desc' => $request->material_desc,
                'rec_qty' => $request->rec_qty,
                'remarks' => $request->remarks,
                'status' => 1,
                'satuan' => 'PCS',
                'created_at' => $old->created_at,
                'updated_at' => now(),
            ]);

            $new = RecordMaterialLines::with('recordMaterialTrans')->find($newId);

            return response()->json([
                'success' => true,
                'message' => 'The old record has been deactivated and new data has been successfully added.',
                'data' => $new,
            ]);
        });
    }
}
