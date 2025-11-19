<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordBatchMar extends Model
{
    protected $table = 'record_batch_mar';

    protected $fillable = [
        'batch_mar',
        'batch_mar_desc',
        'qty_batch_mar',
        'status',
        'remarks'
    ];

    public function recordMaterialLine()
    {
        return $this->belongsTo(RecordMaterialLines::class, 'record_material_lines_id');
    }
}
