<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';          // tabla real
    protected $primaryKey = 'id_empresa';   // no lo cambiaste
    public $incrementing = false;           // permitir asignar id_empresa manualmente
    public $timestamps = false;             // tu tabla no usa timestamps laravel

    protected $fillable = [
        'id_empresa',
        'nombre',
        'nit',
        'nrc',
        'razon_social',
        'direccion',
        'telefono',
        'correo_contacto',
        'logo',
        'inactivo',
        'activo',
        'deleted_at'
    ];
}
