<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    protected $table = 'familia';       // tabla en minúsculas
    protected $primaryKey = 'idfamilia'; // pk real en minúsculas

    public $timestamps = false; // tu tabla NO usa timestamps de Laravel

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    /* =============================
       INSERTAR
    ============================= */
    public static function insertar(array $d)
    {
        return self::create([
            'nombre'      => $d['nombre'],
            'descripcion' => $d['descripcion'],
            'activo'      => 1
        ]);
    }

    /* =============================
       ACTUALIZACIÓN INLINE
    ============================= */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('idfamilia', $id)
            ->where('activo', 1)
            ->update([
                'nombre'      => $d['nombre'],
                'descripcion' => $d['descripcion']
            ]);
    }

    /* =============================
       BORRADO LÓGICO
    ============================= */
    public static function eliminarLogico(int $id)
    {
        return self::where('idfamilia', $id)
            ->update(['activo' => 0]);
    }

    /* =============================
       BUSCAR + PAGINACIÓN
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
       TODOS
    ============================= */
    public static function obtenerTodosIncluyendoEliminados()
    {
        return self::orderBy('activo', 'DESC')
            ->orderBy('nombre', 'ASC')
            ->get();
    }
}
