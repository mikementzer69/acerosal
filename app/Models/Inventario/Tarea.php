<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $table = 'tareas';
    protected $primaryKey = 'id_tarea';

    public $timestamps = false;

    protected $fillable = [
        'id_modulo',
        'nombre',
        'descripcion',
        'ruta',
        'icono',
        'orden',
        'visible',
        'inactivo'
    ];

    /* ================================
       RELACIÓN CON MÓDULO
    ================================== */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    /* ================================
       INSERTAR
    ================================== */
    public static function insertar(array $d)
    {
        return self::create([
            'id_modulo'   => $d['id_modulo'],
            'nombre'      => $d['nombre'],
            'descripcion' => $d['descripcion'] ?? null,
            'ruta'        => $d['ruta'] ?? null,
            'icono'       => $d['icono'] ?? null,
            'orden'       => $d['orden'] ?? 0,
            'visible'     => $d['visible'] ?? 1,
            'inactivo'    => 0
        ]);
    }

    /* ================================
       ACTUALIZAR (inline)
    ================================== */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('id_tarea', $id)
            ->update([
                'id_modulo'   => $d['id_modulo'],
                'nombre'      => $d['nombre'],
                'descripcion' => $d['descripcion'] ?? null,
                'ruta'        => $d['ruta'] ?? null,
                'icono'       => $d['icono'] ?? null,
                'orden'       => $d['orden'] ?? 0,
                'visible'     => $d['visible'] ?? 1,
            ]);
    }

    /* ================================
       ELIMINACIÓN LÓGICA
    ================================== */
    public static function eliminarLogico(int $id)
    {
        return self::where('id_tarea', $id)
            ->update(['inactivo' => 1]);
    }

    /* ================================
       LISTAR CON FILTRO + PAGINACIÓN
    ================================== */
    public static function buscarConPaginacion(string $nombre = '', int $limit = 10)
    {
        return self::where('tareas.inactivo', 0)
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('tareas.nombre', 'LIKE', "%{$nombre}%");
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($limit);
    }

    /* ================================
       TODAS LAS TAREAS (incluye inactivas)
    ================================== */
    public static function obtenerTodasIncluyendoEliminadas()
    {
        return self::orderBy('inactivo', 'ASC')
                   ->orderBy('orden')
                   ->orderBy('nombre')
                   ->get();
    }

    /* ================================
       ACTIVAS PARA SELECTS
    ================================== */
    public static function obtenerActivasParaSelect()
    {
        return self::where('inactivo', 0)
                   ->orderBy('nombre')
                   ->get(['id_tarea', 'nombre']);
    }
}

