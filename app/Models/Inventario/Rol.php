<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Tarea;

class Rol extends Model
{
    protected $table = 'roles'; //
    protected $primaryKey = 'id_rol'; //
    protected $guard_name = 'web'; //
    public $timestamps = false; //

    protected $fillable = [
        'name',        // 🔑 Nombre real en la BD
        'guard_name',  // Requerido por la estructura de Spatie
        'inactivo'     // Aunque no exista en la tabla, lo dejamos por si acaso
    ];

    /* =============================
       RELACIÓN N:M con Tareas
    ============================= */
    public function tareas()
    {
        return $this->belongsToMany(
            Tarea::class,
            'roles_tareas',   // Tabla pivote
            'id_rol',         // FK hacia roles
            'id_tarea'        // FK hacia tareas
        )->withPivot('inactivo'); //
    }

    /* =============================
       INSERTAR
    ============================= */
    public static function insertar(array $d)
    {
        return self::create([
            'name'       => $d['name'], //
            'guard_name' => $d['guard_name'] ?? 'web', //
        ]);
    }

    /* =============================
       ACTUALIZAR
    ============================= */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('id_rol', $id) //
            ->update([
                'name'       => $d['name'], //
                'guard_name' => $d['guard_name'] ?? 'web', //
                'inactivo'    => 0 // Aunque no exista en la tabla, lo dejamos por si acaso
            ]);
    }

    /* =============================
       LISTAR + BUSCAR + PAGINAR
    ============================= */
    public static function buscarConPaginacion(?string $nombre = '', int $limit = 10)
    {
        // 🚀 Eliminamos el filtro 'inactivo' porque no existe en la tabla roles
        return self::when($nombre !== '', function($q) use ($nombre) {
                return $q->where('name', 'LIKE', "%{$nombre}%"); //
            })
            ->orderBy('name', 'ASC') //
            ->paginate($limit); //
    }
}
