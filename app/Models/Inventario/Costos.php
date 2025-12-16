<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Costos extends Model
{
    protected $table = 'costos';           // ← tabla real en minúsculas
    protected $primaryKey = 'idcostos';    // ← tu PK real

    public $timestamps = true;             // tu tabla SÍ tiene created_at / updated_at

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'deleted_at'
    ];
}
