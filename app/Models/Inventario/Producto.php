<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';            // tabla en minúsculas
    protected $primaryKey = 'idproductos';     // pk en minúsculas
    public $timestamps = false;

    protected $fillable = [
        'id_familia',
        'codigo',
        'descripcion',
        'unidad_medida',
        'milimetros',
        'pulgadas',
        'tolerancia',
        'peso_lb_mts',
        'precio_venta_sin_iva',
        'precio_fijo',
        'eliminado'
    ];

    /* =========================================
       RELACIÓN CON FAMILIA
    ========================================== */
    public function familia()
    {
        return $this->belongsTo(Familia::class, 'id_familia');
    }

    /* =========================================
       Obtener familias activas para selects
    ========================================== */
    public static function obtenerFamiliasActivas()
    {
        return Familia::where('activo', 1)
            ->orderBy('nombre')
            ->get(['idfamilia', 'nombre']);
    }

    /* =========================================
       INSERTAR
    ========================================== */
    public static function insertar(array $d)
    {
        return self::create([
            'id_familia'           => $d['id_familia'] ?? null,
            'codigo'               => $d['codigo'] ?? '',
            'descripcion'          => $d['descripcion'] ?? '',
            'unidad_medida'        => $d['unidad_medida'] ?? '',
            'milimetros'           => $d['milimetros'] ?? 0,
            'pulgadas'             => $d['pulgadas'] ?? 0,
            'tolerancia'           => $d['tolerancia'] ?? 0,
            'peso_lb_mts'          => $d['peso_lb_mts'] ?? 0,
            'precio_venta_sin_iva' => $d['precio_venta_sin_iva'] ?? 0,
            'precio_fijo'          => $d['precio_fijo'] ?? 0,
            'eliminado'            => 0,
        ]);
    }

    /* =========================================
       ACTUALIZAR INLINE
    ========================================== */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('idproductos', $id)
            ->where('eliminado', 0)
            ->update([
                'id_familia'           => $d['id_familia'] ?? null,
                'codigo'               => $d['codigo'] ?? '',
                'descripcion'          => $d['descripcion'] ?? '',
                'unidad_medida'        => $d['unidad_medida'] ?? '',
                'milimetros'           => $d['milimetros'] ?? 0,
                'pulgadas'             => $d['pulgadas'] ?? 0,
                'tolerancia'           => $d['tolerancia'] ?? 0,
                'peso_lb_mts'          => $d['peso_lb_mts'] ?? 0,
                'precio_venta_sin_iva' => $d['precio_venta_sin_iva'] ?? 0,
                'precio_fijo'          => $d['precio_fijo'] ?? 0,
            ]);
    }

    /* =========================================
       ELIMINAR LÓGICO
    ========================================== */
    public static function eliminarLogico(int $id)
    {
        return self::where('idproductos', $id)
            ->update(['eliminado' => 1]);
    }

    /* =========================================
       BÚSQUEDA + PAGINACIÓN
    ========================================== */
    public static function buscarConPaginacion(
        string $codigo = '',
        string $texto = '',
        ?int $idFamilia = null,
        int $limit = 10
    ) {
        return self::select('productos.*', 'familia.nombre as familia_nombre')
            ->join('familia', 'familia.idfamilia', '=', 'productos.id_familia')
            ->where('productos.eliminado', 0)
            ->when($codigo !== '', fn($q) => $q->where('productos.codigo', 'LIKE', "%{$codigo}%"))
            ->when($texto !== '', fn($q) => $q->where('productos.descripcion', 'LIKE', "%{$texto}%"))
            ->when($idFamilia && $idFamilia > 0, fn($q)
                => $q->where('productos.id_familia', $idFamilia)
            )
            ->orderBy('productos.codigo')
            ->orderBy('productos.descripcion')
            ->paginate($limit);
    }

    /* =========================================
       SELECT SIMPLE
    ========================================== */
    public static function catalogoSelect()
    {
        return self::where('eliminado', 0)
            ->orderBy('codigo')
            ->orderBy('descripcion')
            ->get(['idproductos as id', 'descripcion as nombre']);
    }

    /* =========================================
       TODOS (incluye eliminados)
    ========================================== */
    public static function obtenerTodosIncluyendoEliminados()
    {
        return self::select('productos.*', 'familia.nombre as familia_nombre')
            ->join('familia', 'familia.idfamilia', '=', 'productos.id_familia')
            ->orderBy('productos.eliminado')
            ->orderBy('productos.codigo')
            ->orderBy('productos.descripcion')
            ->get();
    }
}
