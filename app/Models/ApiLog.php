<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'method',
        'raw_response',
        'module_name',
        'api_type',
        'scope',
    ];
}
