<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'Empresa';   // ← TU TABLA REAL
    protected $primaryKey = 'idEmpresa'; // ← CLAVE PRIMARIA REAL

    public $timestamps = false;     // ← porque NO tienes created_at / updated_at

    protected $fillable = [
        'Nombre',
        'NIT',
        'NRC',
        'Razon_Social',
        'Direccion',
        'Telefono',
        'Correo_Contacto',
        'Activo',
        'deleted_at'
    ];
}
