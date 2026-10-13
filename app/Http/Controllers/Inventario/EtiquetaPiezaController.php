<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Pieza;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Producto;

class EtiquetaPiezaController extends Controller
{
    /**
     * PANTALLA PRINCIPAL DE GENERACIÓN Y SELECCIÓN DE ETIQUETAS
     */
    public function index(Request $request)
    {
        $idEmpresa = session('idEmpresa');

        // Calidades activas
        $familias = DB::table('familias')
            ->where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        // Parámetros opcionales para pre-selección desde atajos (Calidades o Detalle de Lote)
        $idFamiliaSeleccionada  = $request->get('id_familia');
        $idProductoSeleccionado = $request->get('id_producto');
        $idLoteSeleccionado     = $request->get('id_lote');

        return view('inventario.etiquetas.index', compact(
            'familias',
            'idFamiliaSeleccionada',
            'idProductoSeleccionado',
            'idLoteSeleccionado'
        ));
    }

    /**
     * AJAX: PRODUCTOS POR FAMILIA / CALIDAD (O TODOS)
     */
    public function ajaxProductos(Request $request, $id_familia = null)
    {
        $id_familia = $id_familia ?? $request->get('id_familia');
        $idEmpresa  = session('idEmpresa');

        $query = DB::table('productos as p')
            ->leftJoin('familias as f', 'p.id_familia', '=', 'f.id_familia')
            ->where('p.eliminado', 0);

        if ($id_familia && $id_familia !== 'todas') {
            $query->where('p.id_familia', $id_familia);
        }

        if ($idEmpresa) {
            $query->where('p.id_empresa', $idEmpresa);
        }

        $productos = $query->select(
            'p.id_producto',
            'p.codigo',
            'p.descripcion',
            'p.milimetros',
            'p.pulgadas',
            'p.stock_metros',
            'f.nombre as familia_nombre'
        )
        ->orderBy('p.descripcion')
        ->get();

        return response()->json($productos);
    }

    /**
     * AJAX: LOTES POR PRODUCTO O POR FAMILIA (O TODOS)
     */
    public function ajaxLotes(Request $request, $id_producto = null)
    {
        $id_producto = $id_producto ?? $request->get('id_producto');
        $id_familia  = $request->get('id_familia');
        $idEmpresa   = session('idEmpresa');

        $query = DB::table('lotes as l')
            ->join('productos as p', 'l.id_producto', '=', 'p.id_producto')
            ->where('l.eliminado', 0);

        if ($id_producto && $id_producto !== 'todos') {
            $query->where('l.id_producto', $id_producto);
        } elseif ($id_familia && $id_familia !== 'todas') {
            $query->where('p.id_familia', $id_familia);
        }

        if ($idEmpresa) {
            $query->where('l.id_empresa', $idEmpresa);
        }

        $lotes = $query->select(
            'l.id_lote',
            'l.codigo',
            'l.fecha_ingreso',
            'l.cantidad_total_metros',
            'l.peso_total_libras',
            'l.total_piezas',
            'p.descripcion as producto_descripcion'
        )
        ->orderByDesc('l.id_lote')
        ->get();

        return response()->json($lotes);
    }

    /**
     * AJAX: PIEZAS POR LOTE, POR PRODUCTO O POR CALIDAD (O TODAS)
     */
    public function ajaxPiezas(Request $request, $id_lote = null)
    {
        $id_lote     = $id_lote ?? $request->get('id_lote');
        $id_producto = $request->get('id_producto');
        $id_familia  = $request->get('id_familia');
        $idEmpresa   = session('idEmpresa');

        $query = DB::table('piezas as pz')
            ->join('productos as pr', 'pz.id_producto', '=', 'pr.id_producto')
            ->leftJoin('familias as f', 'pr.id_familia', '=', 'f.id_familia')
            ->leftJoin('lotes as l', 'pz.id_lote', '=', 'l.id_lote')
            ->where('pz.eliminado', 0);

        if ($idEmpresa) {
            $query->where('pz.id_empresa', $idEmpresa);
        }

        if ($id_lote && $id_lote !== 'todos') {
            $query->where('pz.id_lote', $id_lote);
        }

        if ($id_producto && $id_producto !== 'todos') {
            $query->where('pz.id_producto', $id_producto);
        }

        if ($id_familia && $id_familia !== 'todas') {
            $query->where('pr.id_familia', $id_familia);
        }

        $piezas = $query->select(
                'pz.id_pieza',
                'pz.codigo',
                'pz.cantidad_metros_actual',
                'pz.peso_libras_actual',
                'pz.cantidad_metros_inicial',
                'pz.peso_libras_inicial',
                'pz.estado',
                'pr.descripcion as producto_descripcion',
                'pr.milimetros',
                'pr.pulgadas',
                'f.nombre as familia_nombre',
                'l.codigo as lote_codigo'
            )
            ->orderBy('pz.codigo')
            ->get();

        return response()->json($piezas);
    }

    /**
     * VISTA DE IMPRESIÓN DE ETIQUETAS (VIÑETAS)
     */
    public function imprimir(Request $request)
    {
        $idEmpresa = session('idEmpresa');

        // Determinar IDs de piezas a imprimir
        $idsPiezas = [];

        if ($request->has('piezas')) {
            $piezasInput = $request->piezas;
            if (is_array($piezasInput)) {
                $idsPiezas = array_filter(array_map('intval', $piezasInput));
            } elseif (is_string($piezasInput)) {
                $idsPiezas = array_filter(array_map('intval', explode(',', $piezasInput)));
            }
        }

        // Si no se pasaron IDs explícitos, consultar según los filtros (soporta "todos" / "todas")
        if (empty($idsPiezas)) {
            $query = Pieza::where('eliminado', 0);

            if ($idEmpresa) {
                $query->where('id_empresa', $idEmpresa);
            }

            if ($request->filled('id_lote') && $request->id_lote !== 'todos') {
                $query->where('id_lote', $request->id_lote);
            }

            if ($request->filled('id_producto') && $request->id_producto !== 'todos') {
                $query->where('id_producto', $request->id_producto);
            }

            if ($request->filled('id_familia') && $request->id_familia !== 'todas') {
                $idFam = $request->id_familia;
                $query->whereHas('producto', function($qp) use ($idFam) {
                    $qp->where('id_familia', $idFam);
                });
            }

            $idsPiezas = $query->pluck('id_pieza')->toArray();
        }

        if (empty($idsPiezas)) {
            return back()->with('error', 'No se encontraron piezas activas para imprimir con los filtros seleccionados.');
        }

        // Consultar piezas con producto, familia (calidad), lote y ubicación
        $piezas = Pieza::with(['producto.familia', 'producto.ubicacion', 'lote'])
            ->whereIn('id_pieza', $idsPiezas)
            ->where('eliminado', 0)
            ->orderBy('codigo')
            ->get();

        if ($piezas->isEmpty()) {
            return back()->with('error', 'No se encontraron piezas activas para los identificadores seleccionados.');
        }

        // Datos de la empresa
        $empresa = null;
        if ($idEmpresa) {
            $empresa = DB::table('empresas')->where('id_empresa', $idEmpresa)->first();
        }

        // Formato de impresión ('rollo' / 'cuadricula')
        $formato = $request->get('formato', 'rollo');

        return view('inventario.etiquetas.imprimir', compact('piezas', 'empresa', 'formato'));
    }
}
