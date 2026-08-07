<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LogStatusEnum;

class Log extends Model
{
    use SoftDeletes;

    protected $table = "logs";

    protected $fillable = [
        'order_id',
        'listener',
        'log_status',
        'message',
        'attempt',
        'processed_at'
    ];

    protected $casts = [
        'log_status' => LogStatusEnum::class,
        'processed_at' => 'datetime'
    ];
}
