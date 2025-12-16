<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';          // tabla real
    protected $primaryKey = 'idempresa';   // no lo cambiaste

    public $timestamps = false;            // tu tabla no usa timestamps laravel

    protected $fillable = [
        'nombre',
        'nit',
        'nrc',
        'razon_social',
        'direccion',
        'telefono',
        'correo_contacto',
        'activo',
        'deleted_at'
    ];
}
