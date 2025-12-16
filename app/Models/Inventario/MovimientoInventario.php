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
        'id_corte',
        'id_compra',
        'origen',
        'tipo',
        'cantidad',
        'peso',
        'fecha',
        'id_usuario',
        'comentario',
        'eliminado'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'eliminado' => 'boolean'
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