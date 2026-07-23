<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_pieza',
        'id_producto',
        'id_corte',
        'id_compra',
        'origen',
        'tipo',
        'cantidad',
        'cantidad_solicitada',    // <--- NUEVO
        'tolerancia_aplicada',    // <--- NUEVO
        'cantidad_total_retirada', // <--- NUEVO
        'peso_neto_libras', // 👈 ¡AGREGÁ ESTE!
        'merma_libras_grabada',    // 👈 Y ESTE
        'peso',
        'precio_unitario_bodega',
        'saldo_metros',
        'saldo_libras',
        'fecha',
        'id_usuario',
        'comentario',
        'eliminado',
        'no_orden',
        'id_empresa',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'eliminado' => 'boolean',
        'cantidad_solicitada' => 'float',
        'tolerancia_aplicada' => 'float',
        'cantidad_total_retirada' => 'float'
    ];

    // RELACIONES
    public function pieza()
    {
        return $this->belongsTo(Pieza::class, 'id_pieza');
    }

    public function corte()
    {
        return $this->belongsTo(Corte::class, 'id_corte');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
