<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

use function Symfony\Component\String\s;

class InventarioAjusteController extends Controller
{
    // 1. Obtener todos los productos (Protegido por empresa)
    public function getProductos() {
        return DB::table('productos')
            ->where('id_empresa', session('idEmpresa'))
            ->select('id_producto', 'descripcion', 'codigo')
            ->get();
    }

    public function storeReingreso(Request $request)
    {
        $request->validate([
            'id_lote'  => 'required|integer',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo'   => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // 1. Obtener datos del Lote y Producto filtrados por empresa
            $lote = DB::table('lotes')
                ->where('id_lote', $request->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->first();

            $producto = DB::table('productos')
                ->where('id_producto', $lote->id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->first();

            $precioUnitario = $producto->precio_unitario_bodega ?? 0;

            // 2. Generar identidad automática (Filtrado por empresa)
            $conteoPiezas = DB::table('piezas')
                ->where('id_lote', $lote->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->count();

            $nuevoCorrelativo = str_pad($conteoPiezas + 1, 3, "0", STR_PAD_LEFT);
            $nuevoCodigoPieza = $producto->codigo . "-" . $lote->codigo . "-" . $nuevoCorrelativo;

            // 3. Cálculo de peso con precisión
            $factorPeso = floatval($producto->peso_lb_mts ?? 0);
            $metrosReingreso = floatval($request->cantidad);
            $pesoReingreso = round($metrosReingreso * $factorPeso, 4);

            // 4. Crear la nueva pieza (Lleva el id_empresa)
            $idNuevaPieza = DB::table('piezas')->insertGetId([
                'id_lote'                 => $lote->id_lote,
                'id_producto'             => $lote->id_producto,
                'codigo'                  => $nuevoCodigoPieza,
                'cantidad_metros_inicial' => $metrosReingreso,
                'cantidad_metros_actual'  => $metrosReingreso,
                'peso_libras_inicial'     => $pesoReingreso,
                'peso_libras_actual'      => $pesoReingreso,
                'cantidad_metros_recortados' => 0,
                'peso_libras_recortados'    => 0,
                'cantidad_comprometida'     => 0,
                'estado'                    => 'ACTIVA',
                'id_empresa'                => session('idEmpresa'),
                'eliminado'                 => 0,
                'finalizado'                => 0,
                'created_at'                => now(),
                'updated_at'                => now()
            ]);

            // 5. Actualizar totales en cascada
            $this->actualizarTotales($lote->id_lote, $lote->id_producto);

            // 6. REGISTRO EN EL KARDEX (Protegido por empresa)
            DB::table('movimientos_inventario')->insert([
                'id_pieza'                => $idNuevaPieza,
                'id_producto'             => $lote->id_producto,
                'id_empresa'              => session('idEmpresa'),
                'tipo'                    => 'reingreso_por_corte',
                'cantidad'                => $metrosReingreso,
                'cantidad_total_retirada' => $metrosReingreso,
                'cantidad_solicitada'     => $metrosReingreso,
                'peso'                    => $pesoReingreso,
                'peso_neto_libras'        => $pesoReingreso,
                'precio_unitario_bodega'  => $precioUnitario,
                'saldo_metros'            => $metrosReingreso,
                'saldo_libras'            => $pesoReingreso,
                'fecha'                   => now(),
                'comentario'              => "Reingreso de retal: " . $request->motivo,
                'origen'                  => 'LOCALIZADOR_REINGRESO',
                'id_usuario'              => session('idUsuario')
            ]);

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => "Pieza {$nuevoCodigoPieza} creada exitosamente con {$metrosReingreso} mts."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Función auxiliar para no repetir código de sumas (Protegida por empresa)
     */
    private function actualizarTotales($id_lote, $id_producto) {
        // Sumar Lote (Solo piezas de esta empresa)
        $totL = DB::table('piezas')
                ->where('id_lote', $id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->selectRaw('SUM(cantidad_metros_actual) as mts, SUM(peso_libras_actual) as lbs')->first();

        DB::table('lotes')
            ->where('id_lote', $id_lote)
            ->where('id_empresa', session('idEmpresa'))
            ->update([
                'cantidad_total_metros' => $totL->mts ?? 0,
                'peso_total_libras'     => max(0, $totL->lbs ?? 0)
            ]);

        // Sumar Producto (Solo lotes de esta empresa)
        $totP = DB::table('lotes')
                ->where('id_producto', $id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->selectRaw('SUM(cantidad_total_metros) as mts, SUM(peso_total_libras) as lbs')->first();

        DB::table('productos')
            ->where('id_producto', $id_producto)
            ->where('id_empresa', session('idEmpresa'))
            ->update([
                'stock_metros'      => $totP->mts ?? 0,
                'peso_total_libras' => max(0, $totP->lbs ?? 0)
            ]);
    }

    public function obtenerKardex($id_pieza)
    {
       return DB::table('movimientos_inventario as m')
        ->join('users as u', 'm.id_usuario', '=', 'u.id')
        ->join('piezas as pi', 'm.id_pieza', '=', 'pi.id_pieza')
        ->join('productos as p', 'pi.id_producto', '=', 'p.id_producto')
        ->join('familias as f', 'p.id_familia', '=', 'f.id_familia')
        ->where('m.id_pieza', $id_pieza)
        ->where('m.id_empresa', session('idEmpresa'))
        ->where('pi.id_empresa', session('idEmpresa'))
        ->where('p.id_empresa', session('idEmpresa'))
        ->select(
            'm.*',
            'u.name as nombre_usuario',
            'f.nombre as familia_nombre',
            'p.descripcion as producto_nombre',
            'p.milimetros',
            'p.pulgadas'
        )
        ->orderBy('m.fecha', 'desc')
        ->get();
    }

    public function getLotes($id_producto) {
        return DB::table('lotes')
            ->where('id_producto', $id_producto)
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0)
            ->select('id_lote', 'codigo', 'fecha_ingreso')
            ->get();
    }

    public function getPiezas($id_lote) {
        return DB::table('piezas')
            ->where('id_lote', $id_lote)
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0)
            ->where('finalizado', 0)
            ->select('id_pieza', 'codigo', 'cantidad_metros_actual')
            ->get();
    }

    public function index()
    {
       $productos = DB::table('productos')
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0)
            ->select('id_producto', 'codigo', 'descripcion', 'milimetros', 'pulgadas')
            ->get();

        return view('inventario.ajustes.ajuste_individual', compact('productos'));
    }

    public function buscarPieza(Request $request)
    {
        $pieza = DB::table('piezas')
            ->join('productos', 'piezas.id_producto', '=', 'productos.id_producto')
            ->where('piezas.id_pieza', $request->id_pieza)
            ->where('piezas.id_empresa', session('idEmpresa'))
            ->where('productos.id_empresa', session('idEmpresa'))
            ->select(
                'piezas.*',
                'productos.descripcion as nombre_producto',
                'productos.peso_lb_mts'
            )
            ->first();

        return response()->json(['status' => 'success', 'data' => $pieza]);
    }

    public function store(Request $request)
    {
        if ($request->tipo_ajuste === 'REINGRESO') {
            return $this->storeReingreso($request);
        }

        $request->validate([
            'id_pieza'    => 'required|integer',
            'tipo_ajuste' => 'required|in:SUMA,RESTA',
            'cantidad'    => 'required|numeric|min:0.01',
            'motivo'      => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $pieza = DB::table('piezas')
                ->where('id_pieza', $request->id_pieza)
                ->where('id_empresa', session('idEmpresa'))
                ->lockForUpdate()->first();

            if (!$pieza) throw new \Exception("La pieza no existe.");

            $lote = DB::table('lotes')
                ->where('id_lote', $pieza->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->first();

            $producto = DB::table('productos')
                ->where('id_producto', $pieza->id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->first();

            $precioUnitario = $producto->precio_unitario_bodega ?? 0;

            // Recarga de lote por lógica Acerosal (Mantengo tus dos llamadas al lote)
            $lote = DB::table('lotes')
                ->where('id_lote', $pieza->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->first();

            $factorPeso = floatval($producto->peso_lb_mts ?? 0);
            $metrosAjuste = floatval($request->cantidad);
            $pesoAjuste = round($metrosAjuste * $factorPeso, 4);

            if ($request->tipo_ajuste == 'RESTA') {
                if ($pieza->cantidad_metros_actual < $metrosAjuste) {
                    throw new \Exception("No hay suficiente metraje en la pieza para restar.");
                }
                $nuevoMetraje = $pieza->cantidad_metros_actual - $metrosAjuste;
                $nuevoPeso = $pieza->peso_libras_actual - $pesoAjuste;
            } else {
                $nuevoMetraje = $pieza->cantidad_metros_actual + $metrosAjuste;
                $nuevoPeso = $pieza->peso_libras_actual + $pesoAjuste;
            }

            // ACTUALIZACIÓN EN CASCADA CON FILTRO DE EMPRESA
            DB::table('piezas')
                ->where('id_pieza', $request->id_pieza)
                ->where('id_empresa', session('idEmpresa'))
                ->update([
                    'cantidad_metros_actual' => $nuevoMetraje,
                    'peso_libras_actual'     => max(0, $nuevoPeso),
                    'updated_at'             => now()
                ]);

            $totalesLote = DB::table('piezas')
                ->where('id_lote', $pieza->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->selectRaw('SUM(cantidad_metros_actual) as mts, SUM(peso_libras_actual) as lbs')
                ->first();

            DB::table('lotes')
                ->where('id_lote', $pieza->id_lote)
                ->where('id_empresa', session('idEmpresa'))
                ->update([
                    'cantidad_total_metros' => $totalesLote->mts ?? 0,
                    'peso_total_libras'     => max(0, $totalesLote->lbs ?? 0)
                ]);

            $totalesProducto = DB::table('lotes')
                ->where('id_producto', $pieza->id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->selectRaw('SUM(cantidad_total_metros) as mts, SUM(peso_total_libras) as lbs')
                ->first();

            DB::table('productos')
                ->where('id_producto', $pieza->id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->update([
                    'stock_metros'      => $totalesProducto->mts ?? 0,
                    'peso_total_libras' => max(0, $totalesProducto->lbs ?? 0)
                ]);

            DB::table('movimientos_inventario')->insert([
                'id_pieza'                => $pieza->id_pieza,
                'id_producto'             => $pieza->id_producto,
                'id_empresa'              => session('idEmpresa'),
                'tipo'                    => ($request->tipo_ajuste == 'RESTA') ? 'ajuste_salida' : 'ajuste_entrada',
                'cantidad'                => $metrosAjuste,
                'cantidad_solicitada'     => $metrosAjuste,
                'cantidad_total_retirada' => $metrosAjuste,
                'peso'                    => $pesoAjuste,
                'peso_neto_libras'        => $pesoAjuste,
                'precio_unitario_bodega'  => $precioUnitario,
                'saldo_metros'            => $nuevoMetraje,
                'saldo_libras'            => max(0, $nuevoPeso),
                'fecha'                   => now(),
                'comentario'              => "Ajuste individual: " . $request->motivo,
                'origen'                  => 'AJUSTE_MANUAL',
                'id_usuario'              => session('idUsuario')
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Longitud, Peso y Kardex sincronizados correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function exportarKardexPdf(Request $request)
    {
        $id_producto = $request->id_producto;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $saldoInicial = 0;
        if ($id_producto && $fechaInicio) {
            $saldoInicial = DB::table('movimientos_inventario')
                ->where('id_producto', $id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->where('fecha', '<', $fechaInicio . ' 00:00:00')
                ->select(DB::raw("
                    SUM(CASE
                        WHEN LOWER(tipo) LIKE '%entrada%'
                          OR LOWER(tipo) LIKE '%inicial%'
                          OR LOWER(tipo) LIKE '%positivo%'
                          OR LOWER(tipo) LIKE '%reingreso%'
                        THEN COALESCE(cantidad_total_retirada, cantidad)
                        ELSE -COALESCE(cantidad_total_retirada, cantidad)
                    END) as saldo_apertura
                "))
                ->value('saldo_apertura') ?? 0;
        }

        $query = DB::table('movimientos_inventario as m')
            ->leftJoin('users as u', 'm.id_usuario', '=', 'u.id')
            ->where('m.id_empresa', session('idEmpresa'))
            ->select('m.*', 'u.name as nombre_usuario');

        if ($id_producto) {
            $query->where('m.id_producto', $id_producto);
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('m.fecha', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        }

        $movimientos = $query->orderBy('m.fecha', 'asc')->orderBy('m.id_movimiento', 'asc')->get();

        $totalEntradas = 0; $totalSalidas = 0; $saldoAcumulado = $saldoInicial;

        foreach ($movimientos as $m) {
            $cant = $m->cantidad_total_retirada ?? $m->cantidad;
            $tipo = strtolower($m->tipo);
            if (str_contains($tipo, 'entrada') || str_contains($tipo, 'inicial') || str_contains($tipo, 'positivo') || str_contains($tipo, 'reingreso')) {
                $totalEntradas += $cant;
                $saldoAcumulado += $cant;
            } else {
                $totalSalidas += $cant;
                $saldoAcumulado -= $cant;
            }
            $m->saldo_dinamico_global = $saldoAcumulado;
        }

        $producto = DB::table('productos')
            ->where('id_producto', $id_producto)
            ->where('id_empresa', session('idEmpresa'))
            ->first();

        return Pdf::loadView('inventario.ajustes.pdf_kardex', [
            'movimientos'   => $movimientos,
            'producto'      => $producto,
            'totalEntradas' => $totalEntradas,
            'totalSalidas'  => $totalSalidas,
            'saldoInicial'  => $saldoInicial,
            'saldoFinal'    => $saldoAcumulado,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin
        ])->setPaper('letter', 'landscape')
          ->download("Kardex_Acerosal_" . date('Ymd') . ".pdf");
    }

    public function indexKardex(Request $request)
    {
        $familias = DB::table('familias')->get();
        $idProducto = $request->id_producto;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $productos = $request->filled('id_familia')
            ? DB::table('productos')
                ->where('id_familia', $request->id_familia)
                ->where('id_empresa', session('idEmpresa'))
                ->get()
            : [];

        $saldoInicial = 0;

        if ($request->filled('id_producto') && $request->filled('fecha_inicio')) {
            $saldoInicial = DB::table('movimientos_inventario')
                ->where('id_producto', $idProducto)
                ->where('id_empresa', session('idEmpresa'))
                ->where('fecha', '<', $fechaInicio . ' 00:00:00')
                ->select(DB::raw("
                    SUM(CASE
                        WHEN LOWER(tipo) LIKE '%entrada%'
                          OR LOWER(tipo) LIKE '%inicial%'
                          OR LOWER(origen) LIKE '%inicial%'
                          OR LOWER(tipo) LIKE '%reingreso%'
                          OR LOWER(tipo) LIKE '%positivo%'
                        THEN COALESCE(NULLIF(cantidad_total_retirada, 0), cantidad)
                        ELSE -COALESCE(NULLIF(cantidad_total_retirada, 0), cantidad)
                    END) as saldo_apertura
                "))
                ->value('saldo_apertura') ?? 0;
        }

        $query = DB::table('movimientos_inventario as m')
            ->leftJoin('users as u', 'm.id_usuario', '=', 'u.id')
            ->leftJoin('productos as p', 'm.id_producto', '=', 'p.id_producto')
            ->leftJoin('familias as f', 'p.id_familia', '=', 'f.id_familia')
            ->where('m.id_empresa', session('idEmpresa'))
            ->where('m.eliminado', 0)
            ->where('p.id_empresa', session('idEmpresa'))
            ->select(
                'm.*',
                'u.name as nombre_usuario',
                'f.nombre as familia_nombre',
                'p.descripcion as producto_nombre',
                'p.milimetros',
                'p.pulgadas'
            )
            ->orderBy('m.fecha', 'asc')
            ->orderBy('m.id_movimiento', 'asc');

        if ($idProducto) {
            $query->where('m.id_producto', $idProducto);
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('m.fecha', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        }

        $movimientos = $query->get();

        $saldoAcumulado = $saldoInicial;
        $totalEntradas = 0; $totalSalidas = 0;

        foreach ($movimientos as $m) {
            $cant = ($m->cantidad_total_retirada > 0) ? $m->cantidad_total_retirada : $m->cantidad;
            $tipo = strtolower($m->tipo);
            $origen = strtolower($m->origen);

            if (str_contains($tipo, 'entrada') || str_contains($tipo, 'inicial') ||
                str_contains($origen, 'inicial') || str_contains($tipo, 'reingreso') ||
                str_contains($tipo, 'positivo')) {
                $saldoAcumulado += $cant;
                $totalEntradas += $cant;
            } else {
                $saldoAcumulado -= $cant;
                $totalSalidas += $cant;
            }
            $m->saldo_dinamico_global = $saldoAcumulado;
        }
        $saldoFinal = $saldoAcumulado;
        return view('inventario.ajustes.kardex', compact(
            'movimientos', 'familias', 'productos', 'totalEntradas', 'totalSalidas', 'saldoInicial', 'saldoFinal'
        ));
    }
}
