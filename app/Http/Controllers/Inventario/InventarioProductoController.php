<?php

namespace App\Http\Controllers\Inventario;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Producto;
use Illuminate\Support\Facades\Log;

class InventarioProductoController extends Controller
{
    // 1. MÉTODO PARA EL SELECTOR 2 (FILTRAR PRODUCTOS POR FAMILIA)
    public function productosPorFamilia($id_familia)
    {
        $productos = DB::table('productos')
            ->where('id_familia', $id_familia)
            ->where('id_empresa', session('idEmpresa')) // 🔐 Blindaje Empresa
            ->where('eliminado', 0)
            ->select('id_producto', 'descripcion', 'milimetros', 'pulgadas')
            ->get();

        return response()->json($productos);
    }

    // 2. FILTRADO AJAX (EL QUE LLAMA realizarConsulta() EN EL INDEX)
    public function filtrar(Request $request)
    {
        try {
            $id_familia = $request->id_familia;
            $id_producto = $request->id_producto;

            // IMPORTANTE: No leemos 'stock_metros', lo CALCULAMOS de las piezas
            $query = DB::table('productos as p')
                ->leftJoin('piezas as pi', function($join) {
                    $join->on('p.id_producto', '=', 'pi.id_producto')
                         ->on('p.id_empresa', '=', 'pi.id_empresa') // 🔐 Join por empresa para seguridad total
                         ->where('pi.eliminado', '=', 0);
                })
                ->where('p.id_empresa', session('idEmpresa'))
                ->where('p.eliminado', 0);

            if ($id_familia) { $query->where('p.id_familia', $id_familia); }
            if ($id_producto) { $query->where('p.id_producto', $id_producto); }

            $productos = $query->select(
                'p.id_producto',
                'p.codigo',
                'p.descripcion',
                'p.unidad_medida_longitud',
                'p.milimetros',
                'p.pulgadas',
                DB::raw('IFNULL(SUM(pi.cantidad_metros_actual), 0) as stock_metros'),
                DB::raw('IFNULL(SUM(pi.peso_libras_actual), 0) as peso_total_libras') // ✅ Corregido nombre de columna
            )
            ->groupBy('p.id_producto', 'p.codigo', 'p.descripcion', 'p.unidad_medida_longitud', 'p.milimetros', 'p.pulgadas')
            ->orderBy('p.descripcion')
            ->get();

            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. DETALLE DEL PRODUCTO
    public function ver($id_producto)
    {
        // 🔐 SEGURIDAD: Solo puedes ver el detalle si el producto es de tu empresa
        $producto = Producto::where('id_empresa', session('idEmpresa'))
            ->with(['lotes.piezas' => function($q) {
                $q->where('eliminado', 0); // Traemos solo piezas activas
            }])
            ->findOrFail($id_producto);

        return view('inventario.producto_detalle', compact('producto'));
    }

    // 4. RESUMEN SUMADO
    public function filtrarInventario(Request $request) {
        try {
            $id_prod = $request->id_producto;

            $query = DB::table('productos as p')
                ->join('piezas as pi', function($join) {
                    $join->on('p.id_producto', '=', 'pi.id_producto')
                         ->on('p.id_empresa', '=', 'pi.id_empresa'); // 🔐 Doble candado
                })
                ->where('p.id_empresa', session('idEmpresa'))
                ->where('p.eliminado', 0)
                ->where('pi.eliminado', 0);

            if ($id_prod) { $query->where('p.id_producto', $id_prod); }

            $resultados = $query->select(
                'p.id_producto',
                'p.codigo',
                'p.descripcion',
                'p.unidad_medida_longitud as unidad',
                'p.milimetros',
                'p.pulgadas',
                DB::raw('SUM(pi.cantidad_metros_actual) as m_total'),
                DB::raw('SUM(pi.peso_libras_actual) as lb_total') // ✅ Corregido nombre de columna
            )
            ->groupBy('p.id_producto', 'p.codigo', 'p.descripcion', 'p.unidad_medida_longitud', 'p.milimetros', 'p.pulgadas')
            ->get();

            return response()->json($resultados);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 5. LISTADO DE PIEZAS INDIVIDUALES
    public function piezasPorProducto($id) {
        try {
            $piezas = DB::table('piezas as pi')
                ->join('productos as p', function($join) {
                    $join->on('pi.id_producto', '=', 'p.id_producto')
                         ->on('pi.id_empresa', '=', 'p.id_empresa'); // 🔐 Blindaje en Join
                })
                ->where('pi.id_producto', $id)
                ->where('pi.id_empresa', session('idEmpresa')) // 🔐 Filtro empresa
                ->where('pi.eliminado', 0)
                ->select(
                    'pi.id_pieza',
                    'pi.codigo',
                    'p.descripcion',
                    'p.milimetros',
                    'p.pulgadas',
                    'pi.cantidad_metros_actual',
                    'pi.peso_libras_actual' // ✅ Corregido nombre de columna
                )
                ->get();

            return response()->json($piezas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 6. CARGA INICIAL DEL INDEX
    public function index(Request $request)
    {
        // Familias (Globales, pero podrías filtrarlas si tuvieran id_empresa)
        $familias = DB::table('familias')->where('inactivo', 0)->get();

        $query = DB::table('productos')
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0);

        if ($request->filled('id_familia')) { $query->where('id_familia', $request->id_familia); }
        if ($request->filled('id_producto')) { $query->where('id_producto', $request->id_producto); }

        $productos = $query->select(
            'id_producto', 'codigo', 'descripcion', 'unidad_medida_longitud',
            'stock_actual', 'stock_metros', 'peso_total_libras', 'milimetros', 'pulgadas'
        )
        ->orderBy('descripcion')
        ->get();

        return view('inventario.productos_index', compact('productos', 'familias'));
    }
}
