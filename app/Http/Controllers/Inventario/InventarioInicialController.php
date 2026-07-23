<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InventarioInicialController extends Controller
{

    public function index()
    {
        // 1. Traemos las familias (Sin filtro de empresa porque no lleva el campo)
        $familias = DB::table('familias')->get(['id_familia', 'nombre']);

        $productos = [];

        // --- CÁLCULO DEL SIGUIENTE CÓDIGO (Tu lógica original INI-001) ---
        $codigosExistentes = DB::table('lotes')
            ->where('id_empresa', session('idEmpresa'))
            ->where('codigo', 'LIKE', 'INI-%')
            ->pluck('codigo');

        $maxNumeroIni = $codigosExistentes->map(function ($codigo) {
            return (int) substr($codigo, 4);
        })->max();

        $siguienteNumero = $maxNumeroIni ? ($maxNumeroIni + 1) : 1;
        $siguienteCodigo = 'INI-' . str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);

        return view('inventario.ajustes.inventario_inicial', compact('familias', 'productos', 'siguienteCodigo'));
    }

    /**
     * NUEVO MÉTODO: Obtener productos por familia filtrados por empresa
     */
    public function getProductosPorFamilia($id_familia)
    {
        try {
            $productos = DB::table('productos')
                ->where('id_familia', $id_familia)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->get(['id_producto', 'codigo', 'descripcion', 'peso_lb_mts', 'milimetros', 'pulgadas']);

            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // 1. Validaciones
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'codigo_lote' => 'required|string',
            'fecha'       => 'required|date',
            'metros'      => 'required|array|min:1',
            'libras'      => 'required|array|min:1',
        ]);

        $metros = $request->input('metros');
        $libras = $request->input('libras');

        // === MAGIA DE FORMATO ===
        $codigoLoteFinal = $request->codigo_lote;
        if (is_numeric($request->codigo_lote)) {
            $codigoLoteFinal = 'INI-' . str_pad($request->codigo_lote, 3, '0', STR_PAD_LEFT);
        } else {
            $codigoLoteFinal = strtoupper($request->codigo_lote);
        }

        try {
            DB::beginTransaction();

            // A. DATOS DEL PRODUCTO - El blindaje se hace solo por productos.id_empresa
            $infoProducto = DB::table('productos')
                ->join('familias', 'productos.id_familia', '=', 'familias.id_familia')
                ->where('productos.id_producto', $request->id_producto)
                ->where('productos.id_empresa', session('idEmpresa'))
                ->select('productos.codigo', 'productos.peso_lb_mts', 'productos.precio_unitario_bodega')
                ->first();

            if (!$infoProducto) {
                throw new \Exception("El producto seleccionado no pertenece a su empresa o no existe.");
            }

            // B. CORRELATIVO LOTE
            $ultimoCorrelativo = DB::table('lotes')
                ->where('id_empresa', session('idEmpresa'))
                ->max('correlativo');

            $nuevoCorrelativo = $ultimoCorrelativo ? ($ultimoCorrelativo + 1) : 1;

            // C. TOTALES
            $totalMetros = array_sum($metros);
            $totalLibras = array_sum($libras);
            $totalPiezas = count($metros);

            // ---------------------------------------------------------
            // 2. CREAR EL LOTE
            // ---------------------------------------------------------
            $idLote = DB::table('lotes')->insertGetId([
                'id_empresa'             => session('idEmpresa'),
                'id_producto'            => $request->id_producto,
                'id_compra'              => null,
                'correlativo'            => $nuevoCorrelativo,
                'codigo'                 => $codigoLoteFinal,
                'fecha_ingreso'          => $request->fecha,
                'peso_total_libras'      => $totalLibras,
                'unidad_medida_peso'     => 'LB',
                'cantidad_total_metros'  => $totalMetros,
                'unidad_medida_longitud' => 'MTS',
                'relacion_cantidad_peso' => $infoProducto->peso_lb_mts ?? 0,
                'total_piezas'           => $totalPiezas,
                'eliminado'              => 0,
                'created_at'             => now(),
                'updated_at'             => now()
            ]);

            // ---------------------------------------------------------
            // 3. RECORRER Y GUARDAR PIEZAS
            // ---------------------------------------------------------
            foreach ($metros as $index => $m) {
                $correlativoPieza = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $codigoPieza = "{$infoProducto->codigo}-{$codigoLoteFinal}-{$correlativoPieza}";
                $pesoPieza = $libras[$index];

                $idPieza = DB::table('piezas')->insertGetId([
                    'id_empresa'                 => session('idEmpresa'),
                    'id_producto'                => $request->id_producto,
                    'id_lote'                    => $idLote,
                    'codigo'                     => $codigoPieza,
                    'cantidad_metros_inicial'    => $m,
                    'peso_libras_inicial'        => $pesoPieza,
                    'cantidad_metros_actual'     => $m,
                    'peso_libras_actual'         => $pesoPieza,
                    'cantidad_metros_recortados' => 0,
                    'peso_libras_recortados'     => 0,
                    'cantidad_comprometida'      => 0,
                    'retirado'                   => 0,
                    'finalizado'                 => 0,
                    'estado'                     => 'ACTIVA',
                    'eliminado'                  => 0,
                    'created_at'                 => now(),
                    'updated_at'                 => now()
                ]);

                // -----------------------------------------------------
                // 4. REGISTRAR MOVIMIENTO (KÁRDEX)
                // -----------------------------------------------------
                DB::table('movimientos_inventario')->insert([
                    'id_pieza'               => $idPieza,
                    'id_empresa'             => session('idEmpresa'),
                    'id_producto'            => $request->id_producto,
                    'id_corte'               => null,
                    'id_compra'              => null,
                    'no_orden'               => null,
                    'origen'                 => 'INICIAL',
                    'tipo'                   => 'entrada',
                    'cantidad'               => $m,
                    'cantidad_solicitada'    => $m,
                    'cantidad_total_retirada'=> $m,
                    'tolerancia_aplicada'    => 0,
                    'peso'                   => $pesoPieza,
                    'peso_neto_libras'       => $pesoPieza,
                    'precio_unitario_bodega' => $infoProducto->precio_unitario_bodega ?? 0,
                    'saldo_metros'           => $m,
                    'saldo_libras'           => $pesoPieza,
                    'fecha'                  => $request->fecha . ' ' . now()->format('H:i:s'),
                    'id_usuario'             => session('idUsuario') ?? 1,
                    'comentario'             => "Carga inicial Lote: " . $codigoLoteFinal,
                    'eliminado'              => 0
                ]);
            }

            // 5. ACTUALIZAR STOCK MAESTRO
            DB::table('productos')
                ->where('id_producto', $request->id_producto)
                ->where('id_empresa', session('idEmpresa'))
                ->update([
                    'stock_metros'      => DB::raw("stock_metros + $totalMetros"),
                    'peso_total_libras' => DB::raw("peso_total_libras + $totalLibras"),
                    'stock_actual'      => DB::raw("stock_actual + $totalPiezas")
                ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Inventario cargado correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en Inventario Inicial: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
