<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = [ 'country_name' ];
}
