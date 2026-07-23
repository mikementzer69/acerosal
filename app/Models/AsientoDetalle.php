<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalle extends Model
{
protected $fillable = [
    'empresa_id',
    'asiento_id',
    'cuenta_id',
    'descripcion',
    'debe',
    'haber',
];


public function cuenta()
{
    return $this->belongsTo(\App\Models\Cuenta::class, 'cuenta_id');
}

public function asiento()
{
    return $this->belongsTo(\App\Models\Asiento::class, 'asiento_id');
}

}
