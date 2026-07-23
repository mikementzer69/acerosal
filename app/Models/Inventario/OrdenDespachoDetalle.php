<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class OrdenDespachoDetalle extends Model
{
    protected $table = 'ordenes_despacho_detalle';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_orden_despacho',
        'id_familia',
        'id_producto',
        'id_lote',
        'id_pieza',
        'cantidad_metros',
        'cantidad_libras',
        'id_empresa'
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenDespacho::class, 'id_orden_despacho');
    }

    public function familia()
    {
        return $this->belongsTo(Familia::class, 'id_familia');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function pieza()
    {
        return $this->belongsTo(Pieza::class, 'id_pieza');
    }


}
