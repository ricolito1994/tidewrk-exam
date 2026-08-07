<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    //
    use SoftDeletes;

    protected $table = "schools";

    protected $primaryKey = "school_code";

    public $incrementing = false;

    protected $keyType = "string";

    public $fillable = [
        'school_code',
        'school_name',
    ];
}
