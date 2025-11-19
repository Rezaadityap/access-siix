<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordBatchSto extends Model
{
    protected $table = 'record_batch_sto';

    protected $fillable = [
        'batch_sto',
        'batch_sto_desc',
        'qty_batch_sto',
        'status',
        'remarks'
    ];

    public function recordMaterialLine()
    {
        return $this->belongsTo(RecordMaterialLines::class, 'record_material_lines_id');
    }
}
