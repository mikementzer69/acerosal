<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    // tabla real
    protected $table = 'modulos';

    // PK real (ya renombrada)
    protected $primaryKey = 'idmodulos';

    public $timestamps = false;

    // columnas reales en minúsculas
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'id_modulopadre'
    ];

    /* =============================
       INSERTAR
    ============================= */
    public static function insertar(array $d)
    {
        return self::create([
            'nombre'          => $d['nombre'],
            'descripcion'     => $d['descripcion'],
            'activo'          => 1,
            'id_modulopadre'  => $d['id_modulopadre'] ?? null
        ]);
    }

    /* =============================
       ACTUALIZACIÓN INLINE (solo activos)
    ============================= */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('idmodulos', $id)
            ->where('activo', 1)
            ->update([
                'nombre'          => $d['nombre'],
                'descripcion'     => $d['descripcion'],
                'id_modulopadre'  => $d['id_modulopadre'] ?? null,
            ]);
    }

    /* =============================
       ELIMINACIÓN LÓGICA
    ============================= */
    public static function eliminarLogico(int $id)
    {
        return self::where('idmodulos', $id)
            ->update(['activo' => 0]);
    }

    /* =============================
       LISTA + FILTRO + PAGINACIÓN
    ============================= */
    public static function buscarConPaginacion(string $nombre = '', int $limit = 10)
    {
        return self::where('activo', 1)
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'LIKE', "%{$nombre}%");
            })
            ->orderBy('nombre', 'ASC')
            ->paginate($limit);
    }

    /* =============================
       TODOS (incluye eliminados)
    ============================= */
    public static function obtenerTodosIncluyendoEliminados()
    {
        return self::orderBy('activo', 'DESC')
                   ->orderBy('nombre', 'ASC')
                   ->get();
    }

    /* =============================
       ACTIVOS PARA SELECTS
    ============================= */
    public static function obtenerActivos()
    {
        return self::where('activo', 1)
                   ->orderBy('nombre', 'ASC')
                   ->get(['idmodulos', 'nombre']);
    }

    /* =============================
       RELACIÓN: Módulo Padre
    ============================= */
    public function padre()
    {
        return $this->belongsTo(Modulo::class, 'id_modulopadre');
    }

    /* =============================
       RELACIÓN: Hijos
    ============================= */
    public function hijos()
    {
        return $this->hasMany(Modulo::class, 'id_modulopadre');
    }
}
