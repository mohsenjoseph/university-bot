<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestFile extends Model
{
    protected $table = 'request_files';

    protected $fillable = [
        'request_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];
}