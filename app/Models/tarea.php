<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $table = 'tareas';
    protected $primaryKey = 'idtareas';

    public $timestamps = false;

    protected $fillable = [
        'id_modulos',
        'nombre',
        'descripcion',
        'ruta',
        'icono',
        'orden',
        'visible',
        'activo',
    ];

    /* ============================
       RELACIÓN: pertenece a módulo
    ============================ */
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulos');
    }

    /* ============================
       Crear tarea
    ============================ */
    public static function insertar(array $d)
    {
        return self::create([
            'id_modulos' => $d['id_modulos'],
            'nombre'     => $d['nombre'],
            'descripcion'=> $d['descripcion'] ?? null,
            'ruta'       => $d['ruta'] ?? null,
            'icono'      => $d['icono'] ?? null,
            'orden'      => $d['orden'] ?? 0,
            'visible'    => $d['visible'] ?? 1,
            'activo'     => 1,
        ]);
    }

    /* ============================
       Actualizar tarea
    ============================ */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('idtareas', $id)
            ->update([
                'id_modulos' => $d['id_modulos'],
                'nombre'     => $d['nombre'],
                'descripcion'=> $d['descripcion'] ?? null,
                'ruta'       => $d['ruta'] ?? null,
                'icono'      => $d['icono'] ?? null,
                'orden'      => $d['orden'] ?? 0,
                'visible'    => $d['visible'] ?? 1,
            ]);
    }

    /* ============================
       Eliminar lógico
    ============================ */
    public static function eliminarLogico(int $id)
    {
        return self::where('idtareas', $id)->update(['activo' => 0]);
    }

    /* ============================
       Buscar tareas activas
    ============================ */
    public static function buscarConPaginacion(string $nombre = '', int $limit = 10)
    {
        return self::where('activo', 1)
            ->when($nombre !== '', fn($q) =>
                $q->where('nombre', 'LIKE', "%{$nombre}%")
            )
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($limit);
    }
}
