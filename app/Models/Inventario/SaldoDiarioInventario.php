<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaldoDiarioInventario extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla definido en la base de datos.
     *
     */
    protected $table = 'saldos_diarios_inventario';

    /**
     * Campos habilitados para asignación masiva.
     * Coinciden con la estructura de metros, libras y piezas.
     */
    protected $fillable = [
        'id_empresa',
        'id_producto',
        'fecha',
        'saldo_metros',
        'saldo_libras',
        'saldo_piezas',
        'costo_unitario', // Nuevo
    'valor_total'     // Nuevo
    ];

    /**
     * Se desactiva timestamps si solo utilizas la columna 'creado_en'.
     *
     */
    public $timestamps = false;

    /**
     * Relación con el modelo de Producto (ajusta el namespace si es necesario).
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    /* ============================================================
       LÓGICA DE AUDITORÍA PARA KARDEX
       ============================================================ */

    /**
     * Obtiene el registro de cierre más reciente antes de una fecha dada.
     * Esto define el $Saldo_{inicial}$ para el reporte.
     */
    public static function obtenerSaldoApertura($id_producto, $fecha_inicio)
    {
        return self::where('id_producto', $id_producto)
            ->where('fecha', '<', $fecha_inicio)
            ->orderBy('fecha', 'desc')
            ->first();
    }
}
