<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordBatchMismatch extends Model
{
    protected $table = 'record_batch_mismatch';

    protected $fillable = [
        'batch_mismatch',
        'batch_mismatch_desc',
        'qty_batch_mismatch',
        'status',
        'remarks'
    ];

    public function recordMaterialLine()
    {
        return $this->belongsTo(RecordMaterialLines::class, 'record_material_lines_id');
    }
}
