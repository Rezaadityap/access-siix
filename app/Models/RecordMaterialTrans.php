<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordMaterialTrans extends Model
{
    protected $table = 'record_material_trans';

    protected $fillable = [
        'user_id',
        'area',
        'line',
        'date',
        'po_number',
        'model',
        'lot_size',
        'act_lot_size'
    ];
}
