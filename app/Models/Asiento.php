<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
protected $fillable = [
    'empresa_id',
    'fecha',
    'descripcion',
    'total_debe',
    'total_haber',
    'activo',
];


public function detalles()
{
    return $this->hasMany(\App\Models\AsientoDetalle::class, 'asiento_id');
}

protected static function booted()
{
    static::addGlobalScope('empresa', function ($query) {
        $query->where('idEmpresa', session('idEmpresa'));
    });
}


}
