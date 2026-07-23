<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesCerrado extends Model
{
    protected $table = 'meses_cerrados';

    protected $fillable = [
        'idEmpresa',
        'anio',
        'mes',
        'cerrado_en'
    ];
}

