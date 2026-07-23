<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class CompraFamilia extends Model
{
    protected $table = 'compra_familia';
    protected $primaryKey = 'id_compra_familia';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_familia',
        'numero_factura', // <-- ¡Asegurate de que esta línea exista!
        'cantidad_total',
        'peso_total_kg',
        'peso_total_libras',
        'importe_total_eu',
        'importe_total_dolares',
        'precio_cif',
        'precio_unitario_bodega',
        'total_familia',
        'id_empresa'
    ];
    public function familia()
{
    return $this->belongsTo(Familia::class, 'id_familia', 'id_familia');
}

}
