<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordMaterialLines extends Model
{
    protected $table = ['record_material_lines'];

    protected $fillable = [
        'record_material_trans_id',
        'po_item',
        'material',
        'material_desc',
        'rec_qty',
        'act_qty',
        'lcr'
    ];
}
