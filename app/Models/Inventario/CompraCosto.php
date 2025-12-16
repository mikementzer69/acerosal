<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class CompraCosto extends Model
{
    protected $table = 'compra_costo';
    protected $primaryKey = 'id_compra_costo';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_costo',
        'valor_usd',
        'valor_eu',
        'eliminado',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function costo()
    {
        return $this->belongsTo(Costos::class, 'id_costo', 'id_costo');
    }
}