<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';
    protected $primaryKey = 'id_ubicacion';
    protected $fillable = ['id_empresa', 'nombre', 'descripcion', 'inactivo'];

    // 🪄 El pequeño truco: Siempre filtrar por la empresa de la sesión
    protected static function booted()
    {
        static::addGlobalScope('empresa', function ($query) {
            if (session()->has('idEmpresa')) {
                $query->where('id_empresa', session('idEmpresa'));
            }
        });
    }
}
