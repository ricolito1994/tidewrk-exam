<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Student extends Model
{
    //
    use SoftDeletes;

    protected $table = "students";

    protected $primaryKey = "student_id";

    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'student_code',
        'first_name',
        'last_name',
        'date_of_birth',
        'school_code'
    ];
}
