<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordBatch extends Model
{
    protected $table = ['record_batch'];

    protected $fillable =  [
        'batch_wh',
        'batch_wh_desc',
        'qty_batch_wh'
    ];
}
