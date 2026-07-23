<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'id_familia',
        'id_ubicacion', // ✅ AGREGADO
        'codigo',
        'descripcion',
        'unidad_medida_longitud',
        'unidad_medida_peso',
        'milimetros',
        'pulgadas',
        'pulgadas_decimal',
        'tolerancia',
        'peso_lb_mts',
        'precio_venta_sin_iva',
        'precio_fijo',
        'eliminado',
        'precio_unitario_bodega',
        'stock_actual',
        'stock_metros',
        'peso_total_libras',
    ];

    /* =========================================
       RELACIONES
    ========================================== */
    public function familia()
    {
        return $this->belongsTo(Familia::class, 'id_familia');
    }

    public function ubicacion() // ✅ NUEVA RELACIÓN
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'id_producto', 'id_producto');
    }

    /* =========================================
       INSERTAR
    ========================================== */
    public static function insertar(array $d)
    {
        return self::create([
            'id_empresa'             => $d['id_empresa'],
            'id_familia'             => $d['id_familia'],
            'id_ubicacion'           => $d['id_ubicacion'] ?? null, // ✅ AGREGADO
            'codigo'                 => $d['codigo'],
            'descripcion'            => $d['descripcion'],
            'unidad_medida_longitud' => $d['unidad_medida_longitud'] ?? null,
            'unidad_medida_peso'     => $d['unidad_medida_peso'] ?? null,
            'milimetros'             => $d['milimetros'] ?? null,
            'pulgadas'               => $d['pulgadas'] ?? null,
            'pulgadas_decimal'       => $d['pulgadas_decimal'] ?? null,
            'tolerancia'             => $d['tolerancia'] ?? null,
            'peso_lb_mts'            => $d['peso_lb_mts'] ?? null,
            'precio_venta_sin_iva'   => $d['precio_venta_sin_iva'] ?? null,
            'precio_fijo'            => $d['precio_fijo'] ?? 0,
            'eliminado'              => 0,
            'precio_unitario_bodega' => $d['precio_unitario_bodega'],
            'stock_actual'           => 0,
            'stock_metros'           => 0,
            'peso_total_libras'      => 0,
        ]);
    }

    /* =========================================
       ACTUALIZAR
    ========================================== */
    public static function actualizarInline(int $id, array $d)
    {
        return self::where('id_producto', $id)
            ->update([
                'id_familia'             => $d['id_familia'],
                'id_ubicacion'           => $d['id_ubicacion'] ?? null, // ✅ AGREGADO
                'codigo'                 => $d['codigo'],
                'descripcion'            => $d['descripcion'],
                'unidad_medida_longitud' => $d['unidad_medida_longitud'] ?? null,
                'unidad_medida_peso'     => $d['unidad_medida_peso'] ?? null,
                'milimetros'             => $d['milimetros'] ?? null,
                'pulgadas'               => $d['pulgadas'] ?? null,
                'pulgadas_decimal'       => $d['pulgadas_decimal'] ?? null,
                'tolerancia'             => $d['tolerancia'] ?? null,
                'peso_lb_mts'            => $d['peso_lb_mts'] ?? null,
                'precio_venta_sin_iva'   => $d['precio_venta_sin_iva'] ?? null,
                'precio_fijo'            => $d['precio_fijo'] ?? 0,
                'precio_unitario_bodega' => $d['precio_unitario_bodega']
            ]);
    }

    /* =========================================
       ELIMINAR LÓGICO
    ========================================== */
    public static function eliminarLogico(int $id)
    {
        return self::where('id_producto', $id)
            ->update(['eliminado' => 1]);
    }

    /* =========================================
       BÚSQUEDA + PAGINACIÓN (Actualizado con Ubicación)
    ========================================== */
    public static function buscarConPaginacion($codigo, $texto, $idFamilia, $porPagina = 10, $idEmpresa = null, $idUbicacion = null)
    {
        // Traemos también el nombre de la ubicación usando leftJoin para que no falle si es nula
        $query = self::select('productos.*', 'familias.nombre as familia_nombre', 'ubicaciones.nombre as ubicacion_nombre')
                     ->join('familias', 'productos.id_familia', '=', 'familias.id_familia')
                     ->leftJoin('ubicaciones', 'productos.id_ubicacion', '=', 'ubicaciones.id_ubicacion')
                     ->where('productos.eliminado', 0);

        if ($idEmpresa) {
            $query->where('productos.id_empresa', $idEmpresa);
        }

        if ($codigo !== '' && $codigo !== null) {
            $query->where('productos.codigo', 'LIKE', "%{$codigo}%");
        }

        if ($texto !== '' && $texto !== null) {
            $query->where('productos.descripcion', 'LIKE', "%{$texto}%");
        }

        if ($idFamilia) {
            $query->where('productos.id_familia', $idFamilia);
        }

        // ✅ FILTRO POR BODEGA/UBICACIÓN
        if ($idUbicacion) {
            $query->where('productos.id_ubicacion', $idUbicacion);
        }

        return $query->orderBy('productos.descripcion')->paginate($porPagina);
    }

    /* =========================================
       SELECT SIMPLE PARA COMBOS
    ========================================== */
    public static function catalogoSelect()
    {
        return self::where('eliminado', 0)
            ->orderBy('codigo')
            ->orderBy('descripcion')
            ->get(['id_producto as id', 'descripcion as nombre']);
    }

    /* =========================================
       TODOS (PARA REPORTES O LISTAS LARGAS)
    ========================================== */
    public static function obtenerTodosIncluyendoEliminados()
    {
        return self::select('productos.*', 'familias.nombre as familia_nombre', 'ubicaciones.nombre as ubicacion_nombre')
            ->join('familias', 'familias.id_familia', '=', 'productos.id_familia')
            ->leftJoin('ubicaciones', 'productos.id_ubicacion', '=', 'ubicaciones.id_ubicacion')
            ->orderBy('productos.eliminado')
            ->orderBy('productos.codigo')
            ->orderBy('productos.descripcion')
            ->get();
    }
}
