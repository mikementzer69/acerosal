<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Costos extends Model
{
    protected $table = 'Costos';
    protected $primaryKey = 'idCosto';
    protected $fillable = [
        'Nombre',
        'Descripcion',
        'Activo',
        'deleted_at'
    ];
    //
}
