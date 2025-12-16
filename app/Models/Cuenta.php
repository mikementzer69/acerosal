<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'cuentas';  // tu tabla real

protected $fillable = [
    'empresa_id',
    'codigo',
    'nombre',
    'tipo',
    'parent_id',
    'es_movimiento',
    'activo',
];

    public function parent()
    {
        return $this->belongsTo(Cuenta::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Cuenta::class, 'parent_id');
    }
    protected static function booted()
{
    static::addGlobalScope('empresa', function ($query) {
        $query->where('idEmpresa', session('idEmpresa'));
    });
}

}
