<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class CompraProducto extends Model
{
    protected $table = 'compra_producto';
    protected $primaryKey = 'id_compra_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'id_compra',
        'cantidad',
        'precio_kg_eu',
        'precio_kg_usd',
        'peso_kg',
        'peso_libra',
        'importe_eu',
        'importe_dolares',
        'eliminado',
        'id_empresa'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
public function piezas()
{
    // 'id_lote' es la columna en la tabla PIEZAS
    // 'id_lote' es también la columna en tu tabla de LOTES/DETALLE
    return $this->hasMany(Pieza::class, 'id_lote', 'id_lote');
}
}
