<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false; // 👈 importante

    protected $fillable = [
        'username',
        'password',
        'nombre',
        'apellidos',
        'email',
        'celular',
        'inactivo',
    ];

    protected $hidden = ['password'];

    // Roles asignados (incluye pivot inactivo)
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'roles_usuarios', 'id_usuario', 'id_rol')
            ->withPivot('inactivo');
    }

    // Solo roles activos (pivot y rol activos)
    public function rolesActivos()
    {
        return $this->roles()
            ->wherePivot('inactivo', 0)
            ->where('roles.inactivo', 0);
    }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('inactivo', 0);
    }
}