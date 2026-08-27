<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class InventarioBorrador extends Model
{
    protected $table = 'inventario_borradores';
    protected $primaryKey = 'id_borrador';
    
    protected $fillable = [
        'id_compra',
        'id_usuario',
        'id_empresa',
        'datos_json'
    ];
}
