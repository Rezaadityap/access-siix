<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordBatchSmd extends Model
{
    protected $table = 'record_batch_smd';

    protected $fillable = [
        'batch_smd',
        'batch_smd_desc',
        'qty_batch_smd'
    ];
}
