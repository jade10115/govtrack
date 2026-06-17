<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'request_id',
        'action',
        'remarks',
        'performed_by'
    ];
}