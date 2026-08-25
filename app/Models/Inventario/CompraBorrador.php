<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class CompraBorrador extends Model
{
    protected $table = 'compra_borradores';
    protected $primaryKey = 'id_borrador';
    protected $fillable = ['id_usuario', 'id_empresa', 'datos_json'];
}
