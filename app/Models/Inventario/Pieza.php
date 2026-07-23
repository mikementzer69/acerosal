<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    protected $table = 'piezas';
    protected $primaryKey = 'id_pieza';
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'id_producto',
        'id_lote',
        'codigo',
        'peso_libras_inicial',
        'cantidad_metros_inicial',
        'peso_libras_actual',
        'cantidad_metros_actual',
        'peso_libras_recortados',
        'cantidad_metros_recortados',
        'estado',
        'retirado',
        'finalizado',
        'eliminado'
    ];

    protected $casts = [
        'retirado' => 'boolean',
        'finalizado' => 'boolean',
        'eliminado' => 'boolean'
    ];

    // RELACIONES
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function cortes()
    {
        return $this->hasMany(Corte::class, 'id_pieza');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_pieza');
    }
}
