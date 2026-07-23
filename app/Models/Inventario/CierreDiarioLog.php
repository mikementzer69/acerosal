<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class CierreDiarioLog extends Model
{
    // Nombre de la tabla definido en el controlador
    protected $table = 'cierre_diario_log';

    // Campos que se guardan al ejecutar el cierre
    protected $fillable = [
        'id_empresa',
        'fecha',
        'registros_generados',
        'ejecutado_por',
        'estado'
    ];

    // Desactivamos timestamps si la base de datos no tiene 'updated_at'
    public $timestamps = false;
}
