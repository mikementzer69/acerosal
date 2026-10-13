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
     * AJAX: PRODUCTOS POR FAMILIA / CALIDAD
     */
    public function ajaxProductos($id_familia)
    {
        $idEmpresa = session('idEmpresa');

        $query = DB::table('productos')
            ->where('id_familia', $id_familia)
            ->where('eliminado', 0);

        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        $productos = $query->select('id_producto', 'codigo', 'descripcion', 'milimetros', 'pulgadas', 'stock_metros')
            ->orderBy('descripcion')
            ->get();

        return response()->json($productos);
    }

    /**
     * AJAX: LOTES POR PRODUCTO
     */
    public function ajaxLotes($id_producto)
    {
        $idEmpresa = session('idEmpresa');

        $query = DB::table('lotes')
            ->where('id_producto', $id_producto)
            ->where('eliminado', 0);

        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        $lotes = $query->select('id_lote', 'codigo', 'fecha_ingreso', 'cantidad_total_metros', 'peso_total_libras', 'total_piezas')
            ->orderByDesc('id_lote')
            ->get();

        return response()->json($lotes);
    }

    /**
     * AJAX: PIEZAS POR LOTE
     */
    public function ajaxPiezas($id_lote)
    {
        $idEmpresa = session('idEmpresa');

        $query = DB::table('piezas')
            ->where('id_lote', $id_lote)
            ->where('eliminado', 0);

        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        $piezas = $query->select(
                'id_pieza',
                'codigo',
                'cantidad_metros_actual',
                'peso_libras_actual',
                'cantidad_metros_inicial',
                'peso_libras_inicial',
                'estado'
            )
            ->orderBy('codigo')
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
        } elseif ($request->filled('id_lote')) {
            // Imprimir todas las piezas activas del lote
            $query = Pieza::where('id_lote', $request->id_lote)->where('eliminado', 0);
            if ($idEmpresa) {
                $query->where('id_empresa', $idEmpresa);
            }
            $idsPiezas = $query->pluck('id_pieza')->toArray();
        }

        if (empty($idsPiezas)) {
            return back()->with('error', 'No seleccionaste ninguna pieza para imprimir.');
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
