<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'id_empresa',
        'numero_factura',
        'fecha_ingreso',
        'fecha_emision_factura',
        'tasa_cambio',
        'peso_total_libras',
        'peso_total_kg',
        'total_costos_adicionales',
        'costos_adicionales_libra',
        'importe_total_factura',
        'total_factura',
        'nueva_compra',
        'eliminado',
    ];

    // PROVEEDOR
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    // EMPRESA
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    // DETALLE: PRODUCTOS
    public function compraProductos()
    {
        return $this->hasMany(CompraProducto::class, 'id_compra', 'id_compra');
    }

    // DETALLE: COSTOS ADICIONALES
    public function compraCostos()
    {
        return $this->hasMany(CompraCosto::class, 'id_compra', 'id_compra');
    }

    // RESUMEN POR FAMILIA
    public function compraFamilias()
    {
        return $this->hasMany(CompraFamilia::class, 'id_compra', 'id_compra');
    }
public function lotes()
{
    // Una compra tiene muchos lotes.
    // Asegúrate de que los nombres de las llaves coincidan con tu base de datos
    return $this->hasMany(Lote::class, 'id_compra', 'id_compra');
}
    public function detalles() {
    return $this->hasMany(CompraProducto::class, 'id_compra', 'id_compra');
}
}
