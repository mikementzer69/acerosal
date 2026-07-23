<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';              // OK
    protected $primaryKey = 'id_proveedor';        // CORREGIDO
    public $timestamps = false;                    // Sin created_at/updated_at

    protected $fillable = [
        'nombre',
        'origen',
        'direccion',
        'eliminado',
    ];

    protected $casts = [
        'eliminado' => 'boolean',
    ];

    /* Scope: solo activos */
    public function scopeActivos($query)
    {
        return $query->where('eliminado', 0);
    }
}
