<?php

namespace App\Models;

use App\Http\Controllers\PriceController;
use Illuminate\Database\Eloquent\Model;

class RecordMaterialLines extends Model
{
    protected $table = 'record_material_lines';

    protected $fillable = [
        'record_material_trans_id',
        'po_item',
        'material',
        'material_desc',
        'rec_qty',
        'act_qty',
        'lcr',
        'satuan',
        'status'
    ];

    public function recordMaterialTrans()
    {
        return $this->belongsTo(RecordMaterialTrans::class, 'record_material_trans_id');
    }

    public function prices()
    {
        return $this->hasMany(PriceController::class, 'record_material_lines_id');
    }

    public function batchWh()
    {
        return $this->hasMany(RecordBatch::class, 'record_material_lines_id');
    }

    public function batchSmd()
    {
        return $this->hasMany(RecordBatchSmd::class, 'record_material_lines_id');
    }

    public function batchSto()
    {
        return $this->hasMany(RecordBatchSto::class, 'record_material_lines_id');
    }

    public function batchMar()
    {
        return $this->hasMany(RecordBatchMar::class, 'record_material_lines_id');
    }

    public function batchMismatch()
    {
        return $this->hasMany(RecordBatchMismatch::class, 'record_material_lines_id');
    }
}
