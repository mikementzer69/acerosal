<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Corte extends Model
{
    protected $table = 'cortes';
    protected $primaryKey = 'id_corte';
    public $timestamps = false;

    protected $fillable = [
        'id_pieza',
        'codigo',
        'peso_libras',
        'cantidad_metros',
        'fecha_creacion',
        'eliminado'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'eliminado' => 'boolean'
    ];

    // RELACIONES
    public function pieza()
    {
        return $this->belongsTo(Pieza::class, 'id_pieza');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_corte');
    }
}