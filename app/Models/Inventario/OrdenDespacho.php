<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class OrdenDespacho extends Model
{
    protected $table = 'ordenes_despacho';
    protected $primaryKey = 'id_orden_despacho';

    protected $fillable = [
        'numero_orden',
        'fecha',
        'id_cliente',
        'id_usuario',
        'estado',
        'observaciones',
        'id_empresa'
    ];

    // Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    // Vendedor (usuarios)
// app/Models/Inventario/OrdenDespacho.php

public function vendedor() {
    return $this->belongsTo(Usuario::class, 'id_usuario');
}
    // Detalles
    public function detalles()
    {
        return $this->hasMany(OrdenDespachoDetalle::class, 'id_orden_despacho');
    }
}
