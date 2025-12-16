<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'codigo',
        'fecha_ingreso',
        'peso_total_libras',
        'cantidad_total_metros',
        'relacion_cantidad_peso',
        'total_piezas',
        'eliminado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'eliminado' => 'boolean'
    ];

    // RELACIONES
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function piezas()
    {
        return $this->hasMany(Pieza::class, 'id_lote');
    }
}