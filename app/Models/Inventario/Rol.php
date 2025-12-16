<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'inactivo'
    ];

    public function tareas()
    {
        return $this->belongsToMany(
            Tarea::class,
            'roles_tareas',
            'id_rol',
            'id_tarea'
        )->withPivot('inactivo');
    }
}