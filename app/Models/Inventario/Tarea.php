<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $table = 'tareas';
    protected $primaryKey = 'idtareas';

    public $timestamps = false;

    protected $fillable = [
        'id_modulos',
        'nombre',
        'descripcion',
        'ruta',
        'icono',
        'orden',
        'visible',
        'activo'
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulos');
    }
}
