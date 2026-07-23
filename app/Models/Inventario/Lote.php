<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_empresa',
        'id_producto',
        'codigo',
        'correlativo',
        'fecha_ingreso',
        'peso_total_libras',
        'cantidad_total_metros',
        'relacion_cantidad_peso',
        'total_piezas',
        'unidad_medida_peso',
        'unidad_medida_longitud',
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
    // Un lote tiene muchas piezas
    return $this->hasMany(Pieza::class, 'id_lote', 'id_lote');
}
}
