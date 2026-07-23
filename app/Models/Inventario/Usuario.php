<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Rol;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $guard_name = 'web';
    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'username',
        'password',
        'nombre',
        'apellidos',
        'email',
        'celular',
        'id_rol', // 👈 ¡ESTE TIENE QUE ESTAR AQUÍ!
        'inactivo',
    ];

    /* Scope: solo activos */
    public function scopeActivos(Builder $query)
    {
        return $query->where('inactivo', 0);
    }

    /* Relación con roles (cuando tengas tabla roles_usuarios) */
public function rol()
    {
        // Enlazamos id_rol de 'usuarios' con id_rol de 'roles'
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
    public function tieneRol($nombreRol)
{
    // Verificamos si existe la relación y comparamos con 'nombre' o 'name'
    if (!$this->rol) {
        return false;
    }

    $rolActual = strtoupper($this->rol->name ?? $this->rol->nombre ?? '');
    return $rolActual === strtoupper($nombreRol);
}
}
