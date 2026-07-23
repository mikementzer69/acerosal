<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    protected $table = 'familias';
    protected $primaryKey = 'id_familia';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'detalle_color',
        'ubicacion',
        'inactivo'
    ];

    /* =============================
       INSERTAR
    ============================= */
    public static function insertar(array $d)
    {
        return self::create([
            'codigo'        => $d['codigo'],
            'nombre'        => $d['nombre'],
            'descripcion'   => $d['descripcion'] ?? null,
            'detalle_color' => $d['detalle_color'] ?? null,
            'ubicacion'     => $d['ubicacion'] ?? null,
            'inactivo'      => 0
        ]);
    }

    /* =============================
       ACTUALIZAR
    ============================= */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('id_familia', $id)
            ->update([
                'codigo'        => $d['codigo'],
                'nombre'        => $d['nombre'],
                'descripcion'   => $d['descripcion'] ?? null,
                'detalle_color' => $d['detalle_color'] ?? null,
                'ubicacion'     => $d['ubicacion'] ?? null
            ]);
    }

    /* =============================
       BORRADO LÓGICO
    ============================= */
    public static function eliminarLogico(int $id)
    {
        return self::where('id_familia', $id)
            ->update(['inactivo' => 1]);
    }

    /* =============================
       LISTAR CON BÚSQUEDA Y PÁGINA
    ============================= */
public static function buscarConPaginacion(string $nombre = null, int $limit = 10)
{
    $nombre = $nombre ?? ''; // fuerza string

    return self::where('inactivo', 0)
        ->when($nombre !== '', function ($q) use ($nombre) {
            $q->where('nombre', 'LIKE', "%{$nombre}%");
        })
        ->orderBy('nombre', 'ASC')
        ->paginate($limit);
}


    /* =============================
       TODOS (incluye inactivos)
    ============================= */
    public static function obtenerTodosIncluyendoEliminados()
    {
        return self::orderBy('inactivo', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->get();
    }
}
