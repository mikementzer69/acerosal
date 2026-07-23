<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';              // tabla real
    protected $primaryKey = 'id_modulo';       // PK real
    public $timestamps = false;                // tu tabla no usa created_at/updated_at

    protected $fillable = [
        'nombre',
        'descripcion',
        'inactivo',
        'id_modulo_padre'
    ];

    /* =============================
       INSERTAR
    ============================= */
    public static function insertar(array $d)
    {
        return self::create([
            'nombre'          => $d['nombre'],
            'descripcion'     => $d['descripcion'] ?? null,
            'id_modulo_padre' => $d['id_modulo_padre'] ?? null,
            'inactivo'        => 0
        ]);
    }

    /* =============================
       ACTUALIZAR
    ============================= */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('id_modulo', $id)
            ->update([
                'nombre'          => $d['nombre'],
                'descripcion'     => $d['descripcion'] ?? null,
                'id_modulo_padre' => $d['id_modulo_padre'] ?? null
            ]);
    }

    /* =============================
       ELIMINAR LÓGICO
    ============================= */
    public static function eliminarLogico(int $id)
    {
        return self::where('id_modulo', $id)
            ->update(['inactivo' => 1]);
    }

    /* =============================
       LISTA CON FILTRO + PÁGINA
    ============================= */
    public static function buscarConPaginacion(?string $nombre = '', int $limit = 10)
    {
        return self::where('inactivo', 0)
            ->when($nombre, function ($q) use ($nombre) {
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

    /* =============================
       SOLO ACTIVOS PARA SELECTS
    ============================= */
    public static function obtenerActivos()
    {
        return self::where('inactivo', 0)
            ->orderBy('nombre', 'ASC')
            ->get(['id_modulo', 'nombre']);
    }

    /* =============================
       RELACIONES
    ============================= */
    public function padre()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo_padre');
    }

    public function hijos()
    {
        return $this->hasMany(Modulo::class, 'id_modulo_padre');
    }
}
