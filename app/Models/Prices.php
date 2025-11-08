<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    protected $table = 'prices';

    protected $fillable = [
        'record_material_lines_id',
        'unit_pice'
    ];

    public function recordMaterialLines()
    {
        return $this->belongsTo(RecordMaterialLines::class, 'record_material_lines_id');
    }
}
