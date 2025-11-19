<?php

namespace App\Http\Controllers;

use App\Models\RecordBatch;
use App\Models\RecordBatchMar;
use App\Models\RecordBatchMismatch;
use App\Models\RecordBatchSmd;
use App\Models\RecordBatchSto;
use App\Models\RecordMaterialLines;
use App\Models\RecordMaterialTrans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OpKittingController extends Controller
{
    public function kitting_prod1()
    {
        return view('kitting.prod-1');
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

        $saveRecords = [];
        $lastGroupId = RecordMaterialTrans::max('group_id') ?? 0;
        $newGroupId = $lastGroupId + 1;

        foreach ($forms as $form) {
            $poNumber = isset($form['po_number']) ? trim((string) $form['po_number']) : '';
            $model = isset($form['model']) ? trim((string) $form['model']) : '';

            if ($poNumber === '' || $model === '') {
                // skip jika data tidak lengkap
                continue;
            }

            // buat record baru (PO belum ada)
            $record = RecordMaterialTrans::create([
                'user_id'       => Auth::id(),
                'group_id'      => $newGroupId,
                'area'          => Auth::user()->employee->department,
                'line'          => $form['line'] ?? '-',
                'date'          => now()->toDateString(),
                'po_number'     => $poNumber,
                'model'         => $model,
                'lot_size'      => $form['lot_size'] ?? '-',
                'act_lot_size'  => $form['act_lot_size'] ?? null
            ]);

            $record->checker_name = Auth::user()->name ?? '-';
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

            if (!empty($data)) {
                DB::table('record_material_lines')->insert($data);
                Log::info("Imported PO {$poNumber} - total lines: " . count($data));
            } else {
                Log::warning("No valid lines parsed for PO {$poNumber}.");
            }

            Log::info("Preview first lines of file {$poNumber}: ", array_slice($lines, 0, 10));
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

    public function getRecord(Request $request, $po_numbers = null)
    {
        $poList = [];
        if (is_array($po_numbers)) {
            $poList = $po_numbers;
        } elseif ($po_numbers !== null && $po_numbers !== '') {
            $poList = array_filter(array_map('trim', explode(',', (string) $po_numbers)));
        }

        $groupParam = $request->query('group_id', $request->input('group_id', null));
        $groupIds = [];
        if (is_array($groupParam)) {
            $groupIds = $groupParam;
        } elseif ($groupParam !== null && $groupParam !== '') {
            $groupIds = array_filter(array_map('trim', explode(',', (string) $groupParam)));
        }

        // base query
        $query = DB::table('record_material_lines')
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
                'record_material_trans.group_id',
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
                'record_material_trans.change_model',
                DB::raw('ROUND(COALESCE(prices.unit_price, 0), 2) AS unit_price')
            );

        if (!empty($groupIds)) {
            $query->whereIn('record_material_trans.group_id', $groupIds);
        } else {
            if (!empty($poList)) {
                if (count($poList) === 1) {
                    $query->where('record_material_trans.po_number', $poList[0]);
                } else {
                    $query->whereIn('record_material_trans.po_number', $poList);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No PO number or group_id provided.'
                ], 422);
            }
        }

        $query->where(function ($q) {
            $q->whereNull('record_material_lines.status')
                ->orWhere('record_material_lines.status', 1);
        });

        $query->groupBy(
            'record_material_trans.group_id',
            'record_material_lines.material',
            'record_material_lines.material_desc',
            'record_material_lines.satuan',
            'record_material_trans.model',
            'record_material_trans.po_number',
            'record_material_trans.date',
            'record_material_trans.cavity',
            'record_material_trans.change_model',
            'rb.total_wh',
            'rs.total_smd',
            'rsto.total_sto',
            'rma.total_mar',
            'rmm.total_mismatch',
            'rb.count_wh',
            'rs.count_smd',
            'rsto.count_sto',
            'prices.unit_price'
        );

        $records = $query->get();

        if ($records->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "No data found for PO/group: " . (is_array($poList) ? implode(',', $poList) : (string)$po_numbers)
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $records
        ]);
    }

    public function getRecordSearch(Request $request)
    {
        $query = RecordMaterialTrans::query()
            ->leftJoin('users', 'users.id', '=', 'record_material_trans.user_id')
            ->select(
                'record_material_trans.id',
                'record_material_trans.group_id',
                'record_material_trans.po_number',
                'record_material_trans.area',
                'record_material_trans.line',
                'record_material_trans.model',
                'record_material_trans.date',
                'record_material_trans.lot_size',
                'record_material_trans.act_lot_size',
                'record_material_trans.status',
                'users.id as checker_id',
                'users.name as checker_name'
            );

        if (Auth::user()->level_id === null) {
            $query->where('record_material_trans.user_id', Auth::id());
        }

        $startDate = $request->start_date ?: date('Y-m-d');
        $endDate = $request->end_date ?: date('Y-m-d');

        $query->whereBetween('record_material_trans.date', [$startDate, $endDate]);
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

    public function updateInfo(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'line' => 'nullable|string|max:100',
            'lot_size' => 'nullable|numeric'
        ]);

        $groupId = $request->input('group_id');
        $line = $request->input('line');
        $lotSize = $request->input('lot_size');

        try {
            $updated = DB::table('record_material_trans')
                ->where('group_id', $groupId)
                ->update([
                    'line' => $line,
                    'lot_size' => $lotSize,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => "Updated {$updated} record(s)."
            ]);
        } catch (\Throwable $e) {
            Log::error('UpdateInfo error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    public function checkBatch(Request $request)
    {
        $batches = (array) $request->input('batches', []);
        $type = strtoupper((string) $request->input('type', ''));
        $poNumbers = (array) $request->input('po_numbers', []);

        $normalizedBatches = collect($batches)
            ->map(fn($b) => strtolower(trim((string)$b)))
            ->filter(fn($b) => $b !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedBatches)) {
            return response()->json(['status' => 'success', 'duplicates' => []]);
        }

        $poNumbers = collect($poNumbers)
            ->map(fn($p) => trim((string)$p))
            ->filter(fn($p) => $p !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($poNumbers)) {
            return response()->json(['status' => 'success', 'duplicates' => []]);
        }

        $currentGroup = $request->input('group_id');

        if (!$currentGroup) {
            return response()->json([
                'status' => 'success',
                'duplicates' => []
            ]);
        }

        // cek duplikat hanya di group yang sedang aktif
        $groupIds = [$currentGroup];

        if (empty($groupIds)) {
            return response()->json(['status' => 'success', 'duplicates' => []]);
        }

        $found = collect();

        // helper: build placeholders & bindings for normalized values
        $placeholders = implode(',', array_fill(0, count($normalizedBatches), '?'));
        $bindings = $normalizedBatches;

        $checkTableInGroups = function (string $table, string $col) use (&$found, $placeholders, $bindings, $groupIds) {
            $sql = "
            SELECT DISTINCT {$table}.{$col} as raw_val
            FROM {$table}
            JOIN record_material_lines ON {$table}.record_material_lines_id = record_material_lines.id
            JOIN record_material_trans ON record_material_lines.record_material_trans_id = record_material_trans.id
            WHERE record_material_trans.group_id IN (" . implode(',', array_map('intval', $groupIds)) . ")
              AND LOWER(TRIM({$table}.{$col})) IN ({$placeholders})
        ";
            $rows = DB::select($sql, $bindings);
            foreach ($rows as $r) {
                $found->push($r->raw_val);
            }
        };

        // check relevant tables
        $checkTableInGroups('record_batch', 'batch_wh');
        $checkTableInGroups('record_batch_smd', 'batch_smd');
        $checkTableInGroups('record_batch_sto', 'batch_sto');

        $dups = $found->unique()->values()->all();

        return response()->json(['status' => 'success', 'duplicates' => $dups]);
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

            $po_numbers = array_values(array_filter(array_column($poList, 'po_number')));
            $group_ids = array_values(array_filter(array_column($poList, 'group_id')));

            $rlQuery = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id');

            if (!empty($group_ids)) {
                $rlQuery->whereIn('record_material_trans.group_id', $group_ids);
            } elseif (!empty($po_numbers)) {
                $rlQuery->whereIn('record_material_trans.po_number', $po_numbers);
            }

            $rlQuery->where('record_material_lines.material', $material);

            // ambil first
            $recordLine = $rlQuery->first();

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

            $po_numbers = array_values(array_filter(array_column($poList, 'po_number')));
            $group_ids = array_values(array_filter(array_column($poList, 'group_id')));

            // builder awal
            $rlQuery = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id');

            if (!empty($group_ids)) {
                $rlQuery->whereIn('record_material_trans.group_id', $group_ids);
            } elseif (!empty($po_numbers)) {
                $rlQuery->whereIn('record_material_trans.po_number', $po_numbers);
            }

            $rlQuery->where('record_material_lines.material', $material);

            // ambil first
            $recordLine = $rlQuery->first();

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

            $po_numbers = array_values(array_filter(array_column($poList, 'po_number')));
            $group_ids = array_values(array_filter(array_column($poList, 'group_id')));

            $rlQuery = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id');

            if (!empty($group_ids)) {
                $rlQuery->whereIn('record_material_trans.group_id', $group_ids);
            } elseif (!empty($po_numbers)) {
                $rlQuery->whereIn('record_material_trans.po_number', $po_numbers);
            }

            $rlQuery->where('record_material_lines.material', $material);

            // ambil first
            $recordLine = $rlQuery->first();

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

                // ambil po_numbers dan group_ids dari payload (safe)
                $po_numbers = array_values(array_filter(array_column($poList, 'po_number')));
                $group_ids = array_values(array_filter(array_column($poList, 'group_id')));

                // builder awal
                $rlQuery = DB::table('record_material_lines')
                    ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                    ->select(
                        'record_material_lines.id as rml_id',
                        'record_material_trans.id as trans_id',
                        'record_material_trans.po_number as po_number'
                    );

                if (!empty($group_ids)) {
                    $rlQuery->whereIn('record_material_trans.group_id', $group_ids);
                } elseif (!empty($po_numbers)) {
                    $rlQuery->whereIn('record_material_trans.po_number', $po_numbers);
                }

                // filter material
                $rlQuery->where('record_material_lines.material', $material);

                // ambil first
                $recordLine = $rlQuery->first();

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

                        if (!empty($group_ids)) {
                            $affected = DB::table('record_material_trans')
                                ->whereIn('group_id', $group_ids)
                                ->update($updateData);
                        } else {
                            $affected = DB::table('record_material_trans')
                                ->whereIn('po_number', $poNumbers)
                                ->update($updateData);
                        }

                        Log::info('saveAfter(): act_lot_size updated in record_material_trans', [
                            'po_numbers' => $poNumbers,
                            'affected_rows' => $affected,
                            'act_lot_size' => $actualLotSize,
                            'cavity' => $cavity,
                            'change_model' => $change_model
                        ]);

                        if ($affected === 0) {
                            $existsQuery = DB::table('record_material_trans');
                            if (!empty($group_ids)) {
                                $existsQuery->whereIn('group_id', $group_ids);
                            } else {
                                $existsQuery->whereIn('po_number', $poNumbers);
                            }
                            $exists = $existsQuery->count();

                            Log::warning('saveAfter(): act_lot_size not updated in record_material_trans', [
                                'po_numbers' => $poNumbers,
                                'group_ids'  => $group_ids,
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

            $po_numbers = array_values(array_filter(array_column($poList, 'po_number')));
            $group_ids = array_values(array_filter(array_column($poList, 'group_id')));

            $rlQuery = DB::table('record_material_lines')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->select('record_material_lines.id as rml_id');

            if (!empty($group_ids)) {
                $rlQuery->whereIn('record_material_trans.group_id', $group_ids);
            } elseif (!empty($po_numbers)) {
                $rlQuery->whereIn('record_material_trans.po_number', $po_numbers);
            }

            // filter material
            $rlQuery->where('record_material_lines.material', $material);

            // ambil first
            $recordLine = $rlQuery->first();

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
        $groupIds  = $request->input('group_id');

        if (!$poNumbers) {
            return response()->json(['status' => 'error', 'message' => 'No PO provided']);
        }

        $poList = is_array($poNumbers) ? $poNumbers : explode(',', $poNumbers);

        $groupList = [];
        if ($groupIds !== null && $groupIds !== '') {
            $groupList = is_array($groupIds) ? $groupIds : explode(',', $groupIds);
        }

        $base = function ($table, $selectMap) use ($poList, $groupList) {
            $q = DB::table($table)
                ->join('record_material_lines', "$table.record_material_lines_id", '=', 'record_material_lines.id')
                ->join('record_material_trans', 'record_material_lines.record_material_trans_id', '=', 'record_material_trans.id')
                ->whereIn('record_material_trans.po_number', $poList);

            if (!empty($groupList)) {
                $q->whereIn('record_material_trans.group_id', $groupList);
            }

            return $q->select($selectMap)->get();
        };

        // SMD
        $smd = $base('record_batch_smd', [
            'record_material_trans.po_number',
            'record_batch_smd.batch_smd as scan_code',
            'record_material_lines.material',
            'record_batch_smd.qty_batch_smd as qty',
            'record_batch_smd.batch_smd_desc as batch_description'
        ]);

        // WH
        $wh = $base('record_batch', [
            'record_material_trans.po_number',
            'record_batch.batch_wh as scan_code',
            'record_material_lines.material',
            'record_batch.qty_batch_wh as qty',
            'record_batch.batch_wh_desc as batch_description'
        ]);

        // STO
        $sto = $base('record_batch_sto', [
            'record_material_trans.po_number',
            'record_batch_sto.batch_sto as scan_code',
            'record_material_lines.material',
            'record_batch_sto.qty_batch_sto as qty',
            'record_batch_sto.batch_sto_desc as batch_description'
        ]);

        // MAR
        $mar = $base('record_batch_mar', [
            'record_material_trans.po_number',
            'record_batch_mar.batch_mar as scan_code',
            'record_material_lines.material',
            'record_batch_mar.qty_batch_mar as qty',
            'record_batch_mar.batch_mar_desc as batch_description'
        ]);

        // MISMATCH
        $mm = $base('record_batch_mismatch', [
            'record_material_trans.po_number',
            'record_batch_mismatch.batch_mismatch as scan_code',
            'record_material_lines.material',
            'record_batch_mismatch.qty_batch_mismatch as qty',
            'record_batch_mismatch.batch_mismatch_desc as batch_description'
        ]);

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
        $poNumbers = (array) $request->input('po_numbers', []);
        $groupIds = (array) $request->input('group_ids', []);

        $poNumbers = array_values(array_filter(array_map('trim', $poNumbers), fn($v) => $v !== ''));
        $groupIds = array_values(array_filter(array_map('trim', $groupIds), fn($v) => $v !== ''));

        if (empty($poNumbers) && empty($groupIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No PO numbers or group_ids provided.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $transQuery = DB::table('record_material_trans')->select('id', 'po_number', 'group_id');

            if (!empty($groupIds)) {
                $transQuery->whereIn('group_id', $groupIds);
            }

            if (!empty($poNumbers)) {
                if (!empty($groupIds)) {
                    $transQuery->orWhereIn('po_number', $poNumbers);
                } else {
                    $transQuery->whereIn('po_number', $poNumbers);
                }
            }

            $transRows = $transQuery->get();

            if ($transRows->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'No matching record_material_trans found for given group_ids / po_numbers.'
                ], 404);
            }

            $transIds = $transRows->pluck('id')->toArray();

            $lineIds = DB::table('record_material_lines')
                ->whereIn('record_material_trans_id', $transIds)
                ->pluck('id')
                ->toArray();

            if (!empty($lineIds)) {
                DB::table('record_batch')->whereIn('record_material_lines_id', $lineIds)->delete();
                DB::table('record_batch_smd')->whereIn('record_material_lines_id', $lineIds)->delete();
                DB::table('record_batch_sto')->whereIn('record_material_lines_id', $lineIds)->delete();
                DB::table('record_batch_mar')->whereIn('record_material_lines_id', $lineIds)->delete();
                if (Schema::hasTable('record_batch_mismatch')) {
                    DB::table('record_batch_mismatch')->whereIn('record_material_lines_id', $lineIds)->delete();
                }

                // Hapus record detail material
                DB::table('record_material_lines')->whereIn('id', $lineIds)->delete();
            }

            DB::table('record_material_trans')->whereIn('id', $transIds)->delete();

            DB::commit();

            $deletedPOs = $transRows->pluck('po_number')->unique()->values()->all();
            $deletedGroups = $transRows->pluck('group_id')->unique()->values()->all();

            return response()->json([
                'status' => 'success',
                'message' => 'PO(s) and related records successfully deleted.',
                'deleted_po' => $deletedPOs,
                'deleted_group_ids' => $deletedGroups,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('deletePO error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $selectedModel = $request->get('model');
        $selectedPo = $request->get('po');

        $query = RecordMaterialTrans::whereDate('created_at', $date);
        $models = $query->distinct()->pluck('model');

        $poNumbers = collect();
        if ($selectedModel) {
            $poNumbers = RecordMaterialTrans::whereDate('created_at', $date)
                ->where('model', $selectedModel)
                ->distinct()
                ->pluck('po_number');
        }

        $record = collect();
        $batches = collect();

        if ($selectedModel) {
            $recordQuery = RecordMaterialLines::whereHas('recordMaterialTrans', function ($q) use ($date, $selectedModel, $selectedPo) {
                $q->whereDate('created_at', $date)
                    ->where('model', $selectedModel);

                if ($selectedPo) {
                    $q->where('po_number', $selectedPo);
                }
            });

            $record = $recordQuery->with([
                'recordMaterialTrans',
                'batchWh',
                'batchSmd',
                'batchSto',
                'batchMar',
                'batchMismatch'
            ])->get();

            $record->each(function ($line) use (&$batches) {
                $po = optional($line->recordMaterialTrans)->po_number;
                $common = [
                    'record_line_id' => $line->id,
                    'po_number' => $po,
                    'material' => $line->material,
                    'material_desc' => $line->material_desc,
                    'po_item' => $line->po_item,
                ];

                // WH
                if ($line->batchWh && $line->batchWh->isNotEmpty()) {
                    $line->batchWh->each(function ($b) use (&$batches, $common) {
                        $b->source = 'WH';
                        foreach ($common as $k => $v) $b->{$k} = $v;
                        $batches->push($b);
                    });
                }

                // SMD
                if ($line->batchSmd && $line->batchSmd->isNotEmpty()) {
                    $line->batchSmd->each(function ($b) use (&$batches, $common) {
                        $b->source = 'SMD';
                        foreach ($common as $k => $v) $b->{$k} = $v;
                        $batches->push($b);
                    });
                }

                // STO
                if ($line->batchSto && $line->batchSto->isNotEmpty()) {
                    $line->batchSto->each(function ($b) use (&$batches, $common) {
                        $b->source = 'STO';
                        foreach ($common as $k => $v) $b->{$k} = $v;
                        $batches->push($b);
                    });
                }

                // MAR
                if ($line->batchMar && $line->batchMar->isNotEmpty()) {
                    $line->batchMar->each(function ($b) use (&$batches, $common) {
                        $b->source = 'MAR';
                        foreach ($common as $k => $v) $b->{$k} = $v;
                        $batches->push($b);
                    });
                }

                // Mismatch
                if ($line->batchMismatch && $line->batchMismatch->isNotEmpty()) {
                    $line->batchMismatch->each(function ($b) use (&$batches, $common) {
                        $b->source = 'Mismatch';
                        foreach ($common as $k => $v) $b->{$k} = $v;
                        $batches->push($b);
                    });
                }
            });

            $batches = $batches->sortByDesc('created_at')->values();
        }

        return view('history-record', compact('record', 'date', 'models', 'selectedModel', 'poNumbers', 'selectedPo', 'batches'));
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
    public function deleteBatch(Request $request)
    {
        $v = Validator::make($request->all(), [
            'batch_id'   => 'nullable|integer',
            'batch_code' => 'nullable|string',
            'source'     => 'required|string',
            'remarks'    => 'required|string|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        try {
            $batchId   = $request->input('batch_id');
            $batchCode = $request->input('batch_code');
            $source    = strtoupper($request->input('source'));
            $remarks   = $request->input('remarks');

            $map = [
                'WH'       => ['table' => 'record_batch',       'code_col' => 'batch_wh'],
                'SMD'      => ['table' => 'record_batch_smd',      'code_col' => 'batch_smd'],
                'STO'      => ['table' => 'record_batch_sto',      'code_col' => 'batch_sto'],
                'MAR'      => ['table' => 'record_batch_mar',      'code_col' => 'batch_mar'],
                'MISMATCH' => ['table' => 'record_batch_mismatch', 'code_col' => 'batch_mismatch'],
            ];

            if (! isset($map[$source])) {
                return response()->json(['success' => false, 'message' => 'Invalid source'], 400);
            }

            $table = $map[$source]['table'];
            $codeCol = $map[$source]['code_col'];

            return DB::transaction(function () use ($batchId, $batchCode, $table, $codeCol, $source, $remarks) {
                if ($batchId) {
                    $existing = DB::table($table)->where('id', $batchId)->first();
                } else {
                    $existing = DB::table($table)
                        ->where($codeCol, $batchCode)
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if (! $existing) {
                    return response()->json(['success' => false, 'message' => 'Batch not found'], 404);
                }

                $oldRemarks = $existing->remarks ?? '';
                $newRemarks = trim(($oldRemarks ? $oldRemarks . ' | ' : '') . "Deleted ({$source}: " . ($existing->{$codeCol} ?? $batchCode) . ") - {$remarks}");

                $affected = DB::table($table)
                    ->where('id', $existing->id)
                    ->update([
                        'status' => 2,
                        'remarks' => $newRemarks,
                        'updated_at' => now(),
                    ]);

                if (! $affected) {
                    return response()->json(['success' => false, 'message' => 'Failed to update batch'], 500);
                }

                $updated = DB::table($table)->where('id', $existing->id)->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Batch deleted successfully (batch marked as deleted).',
                    'data' => [
                        'batch_id' => $updated->id,
                        'batch_code' => $updated->{$codeCol} ?? $batchCode,
                        'source' => $source,
                        'record_line_id' => $updated->record_material_lines_id ?? null,
                        'status' => $updated->status ?? 2,
                        'remarks' => $updated->remarks ?? $newRemarks,
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('deleteBatch error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error. See logs for details.',
            ], 500);
        }
    }

    public function saveRecord(Request $request)
    {
        $groupIds = (array) $request->input('group_ids', []);
        $groupIds = array_filter(array_map('trim', $groupIds), fn($v) => $v !== '');

        if (empty($groupIds)) {
            return response()->json(['status' => 'error', 'message' => 'No group_ids provided'], 422);
        }

        try {
            $updated = DB::table('record_material_trans')
                ->whereIn('group_id', $groupIds)
                ->update(['status' => 1, 'updated_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => "Status updated for " . intval($updated) . " record(s).",
                'updated' => $updated
            ]);
        } catch (\Throwable $e) {
            Log::error('markNext error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}
