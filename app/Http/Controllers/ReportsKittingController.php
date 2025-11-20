<?php

namespace App\Http\Controllers;

use App\Models\RecordMaterialLines;
use App\Models\RecordMaterialTrans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class ReportsKittingController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start_date', Carbon::today()->toDateString());
        $end   = $request->input('end_date',   Carbon::today()->toDateString());

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $records = RecordMaterialTrans::query()
            ->whereBetween('date', [$start, $end])
            ->orderBy('group_id')
            ->orderBy('date')
            ->get([
                'id',
                'user_id',
                'group_id',
                'area',
                'line',
                'date',
                'po_number',
                'model',
                'lot_size',
                'act_lot_size',
                'cavity',
                'change_model',
                'created_at'
            ]);

        $grouped = $records->groupBy('group_id');
        $jsPayload = $grouped->map(function ($group) {
            return $group->map(function ($r) {
                return [
                    'id' => $r->id,
                    'group_id' => $r->group_id,
                    'date' => (string)$r->date,
                    'po_number' => $r->po_number,
                    'model' => $r->model,
                    'area' => $r->area,
                    'line' => $r->line,
                    'lot_size' => $r->lot_size,
                    'act_lot_size' => $r->act_lot_size,
                    'cavity' => $r->cavity,
                    'change_model' => $r->change_model,
                    'created_at' => optional($r->created_at)->toDateTimeString(),
                ];
            })->values();
        });

        if ($request->ajax()) {
            $rowsHtml = view('reports.kitting-rows', ['records' => $records])->render();
            return response()->json([
                'rows'  => $rowsHtml,
                'count' => $records->count(),
            ]);
        }

        return view('reports.kitting', [
            'records'   => $records,
            'grouped'   => $grouped,
            'jsPayload' => $jsPayload,
            'start'     => $start,
            'end'       => $end,
        ]);
    }
    public function materials(Request $request)
    {
        $validated = $request->validate([
            'group_ids'   => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer'],
        ]);

        $groupIds = $validated['group_ids'];

        $qWh = DB::table('record_batch')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_wh) AS sum_wh'))
            ->groupBy('record_material_lines_id');

        $qtyBatchWh = DB::table('record_batch')
            ->select('record_material_lines_id', 'qty_batch_wh');

        $qSmd = DB::table('record_batch_smd')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_smd) AS sum_smd'))
            ->groupBy('record_material_lines_id');

        $qtyBatchSmd = DB::table('record_batch_smd')
            ->select('record_material_lines_id', 'qty_batch_smd');

        $qSto = DB::table('record_batch_sto')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_sto) AS sum_sto'))
            ->groupBy('record_material_lines_id');

        $qtyBatchSto = DB::table('record_batch_sto')
            ->select('record_material_lines_id', 'qty_batch_sto');

        $rows = DB::table('record_material_lines AS rml')
            ->join('record_material_trans AS rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->leftJoinSub($qWh,  'wh',  'wh.record_material_lines_id',  '=', 'rml.id')
            ->leftJoinSub($qSmd, 'smd', 'smd.record_material_lines_id', '=', 'rml.id')
            ->leftJoinSub($qSto, 'sto', 'sto.record_material_lines_id', '=', 'rml.id')
            ->leftJoinsub($qtyBatchSmd, 'smd_qty', 'smd_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoinsub($qtyBatchWh, 'wh_qty', 'wh_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoinSub($qtyBatchSto, 'sto_qty', 'sto_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('prices as p', DB::raw('TRIM(rml.material)'), '=', DB::raw('TRIM(p.material)'))
            ->whereIn('rmt.group_id', $groupIds)
            ->orderBy('rmt.date')->orderBy('rmt.line')->orderBy('rmt.model')->orderBy('rmt.po_number')
            ->get([
                'rmt.date',
                'rmt.line',
                'rmt.model',
                'rmt.po_number',
                'rmt.lot_size',
                'rmt.cavity',
                'rmt.change_model',
                'rml.material AS item',
                'rml.material_desc AS description',
                'smd_qty.qty_batch_smd AS qty_smd',
                'sto_qty.qty_batch_sto AS qty_sto',
                'wh_qty.qty_batch_wh AS qty_wh',
                DB::raw('COALESCE(wh.sum_wh,0)+COALESCE(smd.sum_smd,0)+COALESCE(sto.sum_sto,0) AS usage_total'),
                DB::raw('COALESCE(p.unit_price,0) AS unit_price'),
                DB::raw('( (COALESCE(wh.sum_wh,0)+COALESCE(smd.sum_smd,0)+COALESCE(sto.sum_sto,0)) * COALESCE(p.unit_price,0) ) AS amount'),
                DB::raw('LEFT(TRIM(rml.material),4) AS material_prefix'),
                DB::raw('rml.rec_qty AS rec_qty'),
                DB::raw("
            CASE
              WHEN rmt.lot_size IS NULL OR rmt.lot_size = 0 THEN 0
              ELSE ROUND((rml.rec_qty / rmt.lot_size) * (rmt.cavity * rmt.change_model), 2)
            END AS qty_lcr
            "),
                DB::raw("
            CASE
              WHEN LEFT(TRIM(rml.material),4)='1187' THEN 'Mitsuba'
              WHEN LEFT(TRIM(rml.material),4)='1123' THEN 'Ichikoh'
              WHEN LEFT(TRIM(rml.material),4)='1347' THEN 'TRI'
              WHEN LEFT(TRIM(rml.material),4)='1359' THEN 'TOYO DENSO'
              WHEN LEFT(TRIM(rml.material),4)='1019' THEN 'AVI'
              WHEN LEFT(TRIM(rml.material),4)='1153' THEN 'KOITO'
              WHEN LEFT(TRIM(rml.material),4)='1112' THEN 'MAS-I'
              WHEN LEFT(TRIM(rml.material),4)='1018' THEN 'AJI'
              WHEN LEFT(TRIM(rml.material),4)='1405' THEN 'HINO'
              WHEN LEFT(TRIM(rml.material),4)='1156' THEN 'KOJIMA'
              ELSE NULL
            END AS supplier
        "),
            ]);

        $html = view('reports.kitting-material-rows', ['rows' => $rows])->render();

        return response()->json([
            'rows'  => $html,
            'count' => $rows->count(),
        ]);
    }
    public function export(Request $request)
    {
        $validated = $request->validate([
            'group_ids'   => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer'],
        ]);

        $groupIds = $validated['group_ids'];

        $qWh = DB::table('record_batch')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_wh) AS sum_wh'))
            ->groupBy('record_material_lines_id');

        $qtyBatchWh = DB::table('record_batch')
            ->select('record_material_lines_id', 'qty_batch_wh');

        $qSmd = DB::table('record_batch_smd')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_smd) AS sum_smd'))
            ->groupBy('record_material_lines_id');

        $qtyBatchSmd = DB::table('record_batch_smd')
            ->select('record_material_lines_id', 'qty_batch_smd');

        $qSto = DB::table('record_batch_sto')
            ->select('record_material_lines_id', DB::raw('SUM(qty_batch_sto) AS sum_sto'))
            ->groupBy('record_material_lines_id');

        $qtyBatchSto = DB::table('record_batch_sto')
            ->select('record_material_lines_id', 'qty_batch_sto');

        $rows = DB::table('record_material_lines AS rml')
            ->join('record_material_trans AS rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->leftJoinSub($qWh,  'wh',  'wh.record_material_lines_id',  '=', 'rml.id')
            ->leftJoinSub($qSmd, 'smd', 'smd.record_material_lines_id', '=', 'rml.id')
            ->leftJoinSub($qSto, 'sto', 'sto.record_material_lines_id', '=', 'rml.id')
            ->leftJoinsub($qtyBatchSmd, 'smd_qty', 'smd_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoinsub($qtyBatchWh, 'wh_qty', 'wh_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoinSub($qtyBatchSto, 'sto_qty', 'sto_qty.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('prices as p', DB::raw('TRIM(rml.material)'), '=', DB::raw('TRIM(p.material)'))
            ->whereIn('rmt.group_id', $groupIds)
            ->orderBy('rmt.date')->orderBy('rmt.line')->orderBy('rmt.model')->orderBy('rmt.po_number')
            ->get([
                'rmt.date',
                'rmt.line',
                'rmt.model',
                'rmt.po_number',
                'rmt.lot_size',
                'rmt.cavity',
                'rmt.change_model',
                'rml.material AS item',
                'rml.material_desc AS description',
                'smd_qty.qty_batch_smd AS qty_smd',
                'sto_qty.qty_batch_sto AS qty_sto',
                'wh_qty.qty_batch_wh AS qty_wh',
                DB::raw('COALESCE(wh.sum_wh,0)+COALESCE(smd.sum_smd,0)+COALESCE(sto.sum_sto,0) AS usage_total'),
                DB::raw('COALESCE(p.unit_price,0) AS unit_price'),
                DB::raw('rml.rec_qty AS rec_qty'),
                DB::raw("
                CASE
                  WHEN rmt.lot_size IS NULL OR rmt.lot_size = 0 THEN 0
                  ELSE ROUND((rml.rec_qty / rmt.lot_size) * (rmt.cavity * rmt.change_model), 2)
                END AS qty_lcr
            "),
                DB::raw("
                CASE
                  WHEN LEFT(TRIM(rml.material),4)='1187' THEN 'Mitsuba'
                  WHEN LEFT(TRIM(rml.material),4)='1123' THEN 'Ichikoh'
                  WHEN LEFT(TRIM(rml.material),4)='1347' THEN 'TRI'
                  WHEN LEFT(TRIM(rml.material),4)='1359' THEN 'TOYO DENSO'
                  WHEN LEFT(TRIM(rml.material),4)='1019' THEN 'AVI'
                  WHEN LEFT(TRIM(rml.material),4)='1153' THEN 'KOITO'
                  WHEN LEFT(TRIM(rml.material),4)='1112' THEN 'MAS-I'
                  WHEN LEFT(TRIM(rml.material),4)='1018' THEN 'AJI'
                  WHEN LEFT(TRIM(rml.material),4)='1405' THEN 'HINO'
                  WHEN LEFT(TRIM(rml.material),4)='1156' THEN 'KOJIMA'
                  ELSE NULL
                END AS supplier
            "),
            ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reports Kitting');

        $headers = [
            'A1' => 'Date',
            'B1' => 'Line',
            'C1' => 'Supplier',
            'D1' => 'Model',
            'E1' => 'PO',
            'F1' => 'Lot',
            'G1' => 'Item',
            'H1' => 'Description',
            'I1' => 'Usage',
            'J1' => 'Unit Price',
            'K1' => 'Qty Total',
            'L1' => 'Qty Lcr',
            'M1' => 'Amount Lcr',
            'N1' => 'Qty Loss',
            'O1' => 'Amount Loss',
            'P1' => 'Percentage',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet->getStyle('A1:P1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:P1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->freezePane('A2');

        $rowIdx = 2;
        foreach ($rows as $r) {
            $dateStr = \Illuminate\Support\Carbon::parse($r->date)->toDateString();
            $lot     = (int)($r->lot_size ?? 0);
            $usage   = (float)$r->usage_total;
            $unit    = (float)$r->unit_price;
            $recQty  = (float)$r->rec_qty;
            $qtyLcr  = (float)$r->qty_lcr;
            $amountLcr = $qtyLcr * $unit;
            $qtyLoss = $recQty - $qtyLcr;
            $totalQty = ($recQty - $r->qty_smd - $r->qty_wh) * -1 + $r->qty_sto;
            $amountLoss = ($totalQty - $r->qty_lcr) * $unit;
            $percentage = $usage > 0 ? number_format((($totalQty - $r->qty_lcr) / $usage) * 100, 2) . '%' : '0%';

            // Set values
            $sheet->setCellValueExplicit("A{$rowIdx}", $dateStr, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$rowIdx}", (string)($r->line ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowIdx}", (string)($r->supplier ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$rowIdx}", (string)($r->model ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$rowIdx}", (string)($r->po_number ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue("F{$rowIdx}", $lot);
            $sheet->setCellValueExplicit("G{$rowIdx}", (string)$r->item, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$rowIdx}", (string)$r->description, DataType::TYPE_STRING);
            $sheet->setCellValue("I{$rowIdx}", $usage);
            $sheet->setCellValue("J{$rowIdx}", $unit);
            $sheet->setCellValue("K{$rowIdx}", $recQty);
            $sheet->setCellValue("L{$rowIdx}", $qtyLcr);
            $sheet->setCellValue("M{$rowIdx}", $amountLcr);
            $sheet->setCellValue("N{$rowIdx}", $qtyLoss);
            $sheet->setCellValue("O{$rowIdx}", $amountLoss);
            $sheet->setCellValue("P{$rowIdx}", $percentage);

            // Wrap description
            $sheet->getStyle("H{$rowIdx}")->getAlignment()->setWrapText(true);

            $rowIdx++;
        }

        // Number formats
        $sheet->getStyle("F2:F{$rowIdx}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle("I2:I{$rowIdx}")->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle("K2:K{$rowIdx}")->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle("L2:L{$rowIdx}")->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle("N2:N{$rowIdx}")->getNumberFormat()->setFormatCode('0');

        $usd = '"$"#,##0.00';
        $sheet->getStyle("J2:J{$rowIdx}")->getNumberFormat()->setFormatCode($usd);
        $sheet->getStyle("M2:M{$rowIdx}")->getNumberFormat()->setFormatCode($usd);


        // Autosize columns
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("A1:N" . ($rowIdx - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Output
        $fileName = 'Reports_Kitting_' . date('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    public function batches(Request $request)
    {
        $po    = $request->query('po_number');
        $model = $request->query('model');
        $line  = $request->query('line');
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $batch = $request->query('batch');

        $sources = $request->query('sources', []);

        if (!is_array($sources) && $sources) {
            $sources = [$sources];
        }

        $allSources = ['wh', 'smd', 'sto', 'mar', 'mismatch'];
        if (empty($sources)) {
            $sources = $allSources;
        }

        $applyDate = function ($q) use ($start, $end) {
            if ($start && $end) {
                $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }
        };

        $qWh = DB::table('record_batch')
            ->leftJoin('record_material_lines as rml', 'record_batch.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch.id',
                'record_batch.batch_wh as batch',
                'record_batch.batch_wh_desc as description',
                'record_batch.qty_batch_wh as qty',
                'record_batch.created_at',
                'record_batch.updated_at',
                DB::raw("'wh' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch.batch_wh', 'like', "%{$batch}%");
            });

        $qSmd = DB::table('record_batch_smd')
            ->leftJoin('record_material_lines as rml', 'record_batch_smd.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_smd.id',
                'record_batch_smd.batch_smd as batch',
                'record_batch_smd.batch_smd_desc as description',
                'record_batch_smd.qty_batch_smd as qty',
                'record_batch_smd.created_at',
                'record_batch_smd.updated_at',
                DB::raw("'smd' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_smd.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_smd.batch_smd', 'like', "%{$batch}%");
            });

        $qSto = DB::table('record_batch_sto')
            ->leftJoin('record_material_lines as rml', 'record_batch_sto.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_sto.id',
                'record_batch_sto.batch_sto as batch',
                'record_batch_sto.batch_sto_desc as description',
                'record_batch_sto.qty_batch_sto as qty',
                'record_batch_sto.created_at',
                'record_batch_sto.updated_at',
                DB::raw("'sto' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_sto.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_sto.batch_sto', 'like', "%{$batch}%");
            });

        $qMar = DB::table('record_batch_mar')
            ->leftJoin('record_material_lines as rml', 'record_batch_mar.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_mar.id',
                'record_batch_mar.batch_mar as batch',
                'record_batch_mar.batch_mar_desc as description',
                'record_batch_mar.qty_batch_mar as qty',
                'record_batch_mar.created_at',
                'record_batch_mar.updated_at',
                DB::raw("'mar' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_mar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_mar.batch_mar', 'like', "%{$batch}%");
            });

        $qMismatch = DB::table('record_batch_mismatch')
            ->leftJoin('record_material_lines as rml', 'record_batch_mismatch.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_mismatch.id',
                'record_batch_mismatch.batch_mismatch as batch',
                'record_batch_mismatch.batch_mismatch_desc as description',
                'record_batch_mismatch.qty_batch_mismatch as qty',
                'record_batch_mismatch.created_at',
                'record_batch_mismatch.updated_at',
                DB::raw("'mismatch' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_mismatch.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_mismatch.batch_mismatch', 'like', "%{$batch}%");
            });

        // pilih queries sesuai filter sumber
        $queries = [];
        if (in_array('wh', $sources)) $queries[] = $qWh;
        if (in_array('smd', $sources)) $queries[] = $qSmd;
        if (in_array('sto', $sources)) $queries[] = $qSto;
        if (in_array('mar', $sources)) $queries[] = $qMar;
        if (in_array('mismatch', $sources)) $queries[] = $qMismatch;

        if (empty($queries)) {
            $batches = collect();
        } else {
            if (count($queries) === 1) {
                $batches = $queries[0]
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $union = array_shift($queries);
                foreach ($queries as $q) {
                    $union = $union->unionAll($q);
                }

                $batches = DB::table(DB::raw("({$union->toSql()}) as t"))
                    ->mergeBindings($union)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        if ($request->ajax()) {
            return view('partials.kitting-batch-rows', compact('batches'))->render();
        }

        return view('reports.kitting-batch', compact('batches'));
    }

    public function exportBatches(Request $request)
    {
        $po    = $request->query('po_number');
        $model = $request->query('model');
        $line  = $request->query('line');
        $start = $request->query('start_date');
        $end   = $request->query('end_date');
        $batch = $request->query('batch');

        $sources = $request->query('sources', []);
        if (!is_array($sources) && $sources) {
            $sources = [$sources];
        }
        $allSources = ['wh', 'smd', 'sto', 'mar', 'mismatch'];
        if (empty($sources)) $sources = $allSources;

        $qWh = DB::table('record_batch')
            ->leftJoin('record_material_lines as rml', 'record_batch.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch.id',
                'record_batch.batch_wh as batch',
                'record_batch.batch_wh_desc as description',
                'record_batch.qty_batch_wh as qty',
                'record_batch.created_at',
                'record_batch.updated_at',
                DB::raw("'wh' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch.batch_wh', 'like', "%{$batch}%");
            });

        $qSmd = DB::table('record_batch_smd')
            ->leftJoin('record_material_lines as rml', 'record_batch_smd.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_smd.id',
                'record_batch_smd.batch_smd as batch',
                'record_batch_smd.batch_smd_desc as description',
                'record_batch_smd.qty_batch_smd as qty',
                'record_batch_smd.created_at',
                'record_batch_smd.updated_at',
                DB::raw("'smd' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_smd.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_smd.batch_smd', 'like', "%{$batch}%");
            });

        $qSto = DB::table('record_batch_sto')
            ->leftJoin('record_material_lines as rml', 'record_batch_sto.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_sto.id',
                'record_batch_sto.batch_sto as batch',
                'record_batch_sto.batch_sto_desc as description',
                'record_batch_sto.qty_batch_sto as qty',
                'record_batch_sto.created_at',
                'record_batch_sto.updated_at',
                DB::raw("'sto' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_sto.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_sto.batch_sto', 'like', "%{$batch}%");
            });

        $qMar = DB::table('record_batch_mar')
            ->leftJoin('record_material_lines as rml', 'record_batch_mar.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_mar.id',
                'record_batch_mar.batch_mar as batch',
                'record_batch_mar.batch_mar_desc as description',
                'record_batch_mar.qty_batch_mar as qty',
                'record_batch_mar.created_at',
                'record_batch_mar.updated_at',
                DB::raw("'mar' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_mar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_mar.batch_mar', 'like', "%{$batch}%");
            });

        $qMismatch = DB::table('record_batch_mismatch')
            ->leftJoin('record_material_lines as rml', 'record_batch_mismatch.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select(
                'record_batch_mismatch.id',
                'record_batch_mismatch.batch_mismatch as batch',
                'record_batch_mismatch.batch_mismatch_desc as description',
                'record_batch_mismatch.qty_batch_mismatch as qty',
                'record_batch_mismatch.created_at',
                'record_batch_mismatch.updated_at',
                DB::raw("'mismatch' as source"),
                'rmt.po_number as po_number',
                'rmt.model as model',
                'rmt.line as line'
            )
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('record_batch_mismatch.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            })
            ->when($po, function ($q) use ($po) {
                $q->where('rmt.po_number', 'like', "%{$po}%");
            })
            ->when($model, function ($q) use ($model) {
                $q->where('rmt.model', 'like', "%{$model}%");
            })
            ->when($line, function ($q) use ($line) {
                $q->where('rmt.line', 'like', "%{$line}%");
            })
            ->when($batch, function ($q) use ($batch) {
                $q->where('record_batch_mismatch.batch_mismatch', 'like', "%{$batch}%");
            });

        // collect selected queries
        $queries = [];
        if (in_array('wh', $sources)) $queries[] = $qWh;
        if (in_array('smd', $sources)) $queries[] = $qSmd;
        if (in_array('sto', $sources)) $queries[] = $qSto;
        if (in_array('mar', $sources)) $queries[] = $qMar;
        if (in_array('mismatch', $sources)) $queries[] = $qMismatch;

        if (empty($queries)) {
            $rows = collect();
        } else {
            if (count($queries) === 1) {
                $rows = $queries[0]->orderBy('created_at', 'desc')->get();
            } else {
                $union = array_shift($queries);
                foreach ($queries as $q) {
                    $union = $union->unionAll($q);
                }
                $rows = DB::table(DB::raw("({$union->toSql()}) as t"))
                    ->mergeBindings($union)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        // Build spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headings
        $headings = ['Po Number', 'Model', 'Line', 'Batch', 'Description', 'Qty', 'Source', 'Created At'];
        $col = 'A';
        foreach ($headings as $i => $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }

        // Rows
        $rowNum = 2;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowNum, $r->po_number);
            $sheet->setCellValue('B' . $rowNum, $r->model ?? '');
            $sheet->setCellValue('C' . $rowNum, $r->line ?? '');
            $sheet->setCellValue('D' . $rowNum, $r->batch);
            $sheet->setCellValue('E' . $rowNum, $r->description);
            $sheet->setCellValue('F' . $rowNum, $r->qty);
            $sheet->setCellValue('G' . $rowNum, strtoupper($r->source));
            $sheet->setCellValue('H' . $rowNum, (string) $r->created_at);
            $rowNum++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Prepare writer and output
        $writer = new Xlsx($spreadsheet);
        $fileName = 'batches_' . now()->format('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function suggestPo(Request $request)
    {
        $q = $request->query('q');
        $limit = 20;

        $q1 = DB::table('record_batch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.po_number')
            ->when($q, fn($qq) => $qq->where('rmt.po_number', 'like', "%{$q}%"));

        $q2 = DB::table('record_batch_smd as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.po_number')
            ->when($q, fn($qq) => $qq->where('rmt.po_number', 'like', "%{$q}%"));

        $q3 = DB::table('record_batch_sto as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.po_number')
            ->when($q, fn($qq) => $qq->where('rmt.po_number', 'like', "%{$q}%"));

        $q4 = DB::table('record_batch_mar as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.po_number')
            ->when($q, fn($qq) => $qq->where('rmt.po_number', 'like', "%{$q}%"));

        $q5 = DB::table('record_batch_mismatch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.po_number')
            ->when($q, fn($qq) => $qq->where('rmt.po_number', 'like', "%{$q}%"));

        $union = $q1->unionAll($q2)->unionAll($q3)->unionAll($q4)->unionAll($q5);

        $rows = DB::table(DB::raw("({$union->toSql()}) as t"))
            ->mergeBindings($union)
            ->select('po_number')
            ->whereNotNull('po_number')
            ->groupBy('po_number')
            ->limit($limit)
            ->get();

        $result = $rows->map(fn($r) => ['po_number' => $r->po_number, 'label' => $r->po_number]);

        return response()->json($result);
    }

    public function suggestModel(Request $request)
    {
        $q = $request->query('q');
        $limit = 20;

        $q1 = DB::table('record_batch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.model')
            ->when($q, fn($qq) => $qq->where('rmt.model', 'like', "%{$q}%"));

        $q2 = DB::table('record_batch_smd as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.model')
            ->when($q, fn($qq) => $qq->where('rmt.model', 'like', "%{$q}%"));

        $q3 = DB::table('record_batch_sto as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.model')
            ->when($q, fn($qq) => $qq->where('rmt.model', 'like', "%{$q}%"));

        $q4 = DB::table('record_batch_mar as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.model')
            ->when($q, fn($qq) => $qq->where('rmt.model', 'like', "%{$q}%"));

        $q5 = DB::table('record_batch_mismatch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.model')
            ->when($q, fn($qq) => $qq->where('rmt.model', 'like', "%{$q}%"));

        $union = $q1->unionAll($q2)->unionAll($q3)->unionAll($q4)->unionAll($q5);

        $rows = DB::table(DB::raw("({$union->toSql()}) as t"))
            ->mergeBindings($union)
            ->select('model')
            ->whereNotNull('model')
            ->groupBy('model')
            ->limit($limit)
            ->get();

        $result = $rows->map(fn($r) => ['model' => $r->model, 'label' => $r->model]);

        return response()->json($result);
    }

    public function suggestLine(Request $request)
    {
        $q = $request->query('q');
        $limit = 20;

        $q1 = DB::table('record_batch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.line')
            ->when($q, fn($qq) => $qq->where('rmt.line', 'like', "%{$q}%"));

        $q2 = DB::table('record_batch_smd as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.line')
            ->when($q, fn($qq) => $qq->where('rmt.line', 'like', "%{$q}%"));

        $q3 = DB::table('record_batch_sto as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.line')
            ->when($q, fn($qq) => $qq->where('rmt.line', 'like', "%{$q}%"));

        $q4 = DB::table('record_batch_mar as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.line')
            ->when($q, fn($qq) => $qq->where('rmt.line', 'like', "%{$q}%"));

        $q5 = DB::table('record_batch_mismatch as rb')
            ->leftJoin('record_material_lines as rml', 'rb.record_material_lines_id', '=', 'rml.id')
            ->leftJoin('record_material_trans as rmt', 'rml.record_material_trans_id', '=', 'rmt.id')
            ->select('rmt.line')
            ->when($q, fn($qq) => $qq->where('rmt.line', 'like', "%{$q}%"));

        $union = $q1->unionAll($q2)->unionAll($q3)->unionAll($q4)->unionAll($q5);

        $rows = DB::table(DB::raw("({$union->toSql()}) as t"))
            ->mergeBindings($union)
            ->select('line')
            ->whereNotNull('line')
            ->groupBy('line')
            ->limit($limit)
            ->get();

        $result = $rows->map(fn($r) => ['line' => $r->line, 'label' => $r->line]);

        return response()->json($result);
    }

    public function suggestBatch(Request $request)
    {
        $q = $request->query('q');
        $limit = 30;

        $q1 = DB::table('record_batch')->select(DB::raw('batch_wh as batch'))
            ->when($q, fn($qq) => $qq->where('batch_wh', 'like', "%{$q}%"));

        $q2 = DB::table('record_batch_smd')->select(DB::raw('batch_smd as batch'))
            ->when($q, fn($qq) => $qq->where('batch_smd', 'like', "%{$q}%"));

        $q3 = DB::table('record_batch_sto')->select(DB::raw('batch_sto as batch'))
            ->when($q, fn($qq) => $qq->where('batch_sto', 'like', "%{$q}%"));

        $q4 = DB::table('record_batch_mar')->select(DB::raw('batch_mar as batch'))
            ->when($q, fn($qq) => $qq->where('batch_mar', 'like', "%{$q}%"));

        $q5 = DB::table('record_batch_mismatch')->select(DB::raw('batch_mismatch as batch'))
            ->when($q, fn($qq) => $qq->where('batch_mismatch', 'like', "%{$q}%"));

        $union = $q1->unionAll($q2)->unionAll($q3)->unionAll($q4)->unionAll($q5);

        $rows = DB::table(DB::raw("({$union->toSql()}) as t"))
            ->mergeBindings($union)
            ->select(DB::raw('DISTINCT batch'))
            ->whereNotNull('batch')
            ->limit($limit)
            ->get();

        $result = $rows->map(fn($r) => ['batch' => $r->batch, 'label' => $r->batch]);

        return response()->json($result);
    }
}
