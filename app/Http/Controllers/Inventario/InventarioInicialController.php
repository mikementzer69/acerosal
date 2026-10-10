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
    public function importarCSV(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file'
        ]);

        $archivo = $request->file('archivo_csv');
        $handle = fopen($archivo->getRealPath(), "r");

        DB::beginTransaction();
        try {
            $filaCount = 0;
            $piezasCreadas = 0;

            // --- OPTIMIZACIÓN: Pre-calcular Correlativos de Lotes fuera del bucle ---
            $codigosExistentes = DB::table('lotes')
                ->where('id_empresa', session('idEmpresa'))
                ->where('codigo', 'LIKE', 'INI-%')
                ->pluck('codigo');

            $maxNumeroIni = $codigosExistentes->map(function ($codigo) {
                return (int) substr($codigo, 4);
            })->max();
            $siguienteNumeroLote = $maxNumeroIni ? ($maxNumeroIni + 1) : 1;

            $ultimoCorrelativoDB = DB::table('lotes')
                ->where('id_empresa', session('idEmpresa'))
                ->max('correlativo');
            $nuevoCorrelativoLote = $ultimoCorrelativoDB ? ($ultimoCorrelativoDB + 1) : 1;

            // Caché de productos para evitar miles de consultas repetidas a BD
            $productosCache = [];

            // Arrays para inserción masiva (Batch Insert)
            $piezasBatch = [];
            $kardexBatch = [];
            // Arrays para sumar stock de forma agrupada
            $stockUpdates = [];

            while (($datos = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $filaCount++;
                
                // Si la fila no tiene al menos 3 columnas, intentamos con coma por si acaso
                if (count($datos) < 3) {
                    $datos = explode(",", implode(";", $datos));
                    if(count($datos) < 3) continue;
                }

                $codigo_producto = trim($datos[0]);
                $cantidad_metros = floatval($datos[1]);
                $numero_piezas = intval($datos[2]);

                // Si el encabezado u omitir línea vacía
                if(empty($codigo_producto) || $cantidad_metros <= 0 || $numero_piezas <= 0) continue;

                // 1. Búsqueda del Producto (Memoized)
                if (!isset($productosCache[$codigo_producto])) {
                    $productosCache[$codigo_producto] = DB::table('productos')
                        ->where('codigo', $codigo_producto)
                        ->where('id_empresa', session('idEmpresa'))
                        ->where('eliminado', 0)
                        ->first();
                }
                $infoProducto = $productosCache[$codigo_producto];

                if (!$infoProducto) {
                    throw new \Exception("Fila {$filaCount}: El producto con código '{$codigo_producto}' no existe o no pertenece a su empresa.");
                }

                // 2. Correlativo Lote para el código (INI-XXX)
                $codigoLoteFinal = 'INI-' . str_pad($siguienteNumeroLote, 3, '0', STR_PAD_LEFT);
                $siguienteNumeroLote++;

                // 3. Correlativo interno Lote
                $nuevoCorrelativo = $nuevoCorrelativoLote;
                $nuevoCorrelativoLote++;

                // 4. Totales
                $totalMetros = $cantidad_metros * $numero_piezas;
                $pesoUnitario = $cantidad_metros * ($infoProducto->peso_lb_mts ?? 0);
                $totalLibras = $pesoUnitario * $numero_piezas;

                // 5. Crear el Lote
                $idLote = DB::table('lotes')->insertGetId([
                    'id_empresa'             => session('idEmpresa'),
                    'id_producto'            => $infoProducto->id_producto,
                    'id_compra'              => null,
                    'correlativo'            => $nuevoCorrelativo,
                    'codigo'                 => $codigoLoteFinal,
                    'fecha_ingreso'          => now()->format('Y-m-d'),
                    'peso_total_libras'      => $totalLibras,
                    'unidad_medida_peso'     => 'LB',
                    'cantidad_total_metros'  => $totalMetros,
                    'unidad_medida_longitud' => 'MTS',
                    'relacion_cantidad_peso' => $infoProducto->peso_lb_mts ?? 0,
                    'total_piezas'           => $numero_piezas,
                    'eliminado'              => 0,
                    'created_at'             => now(),
                    'updated_at'             => now()
                ]);

                // 6. Recorrer y Guardar Piezas
                for ($i = 0; $i < $numero_piezas; $i++) {
                    $correlativoPieza = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
                    $codigoPieza = "{$infoProducto->codigo}-{$codigoLoteFinal}-{$correlativoPieza}";

                    $idPieza = DB::table('piezas')->insertGetId([
                        'id_empresa'                 => session('idEmpresa'),
                        'id_producto'                => $infoProducto->id_producto,
                        'id_lote'                    => $idLote,
                        'codigo'                     => $codigoPieza,
                        'cantidad_metros_inicial'    => $cantidad_metros,
                        'peso_libras_inicial'        => $pesoUnitario,
                        'cantidad_metros_actual'     => $cantidad_metros,
                        'peso_libras_actual'         => $pesoUnitario,
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

                    // Registrar Movimiento (Kárdex)
                    DB::table('movimientos_inventario')->insert([
                        'id_pieza'               => $idPieza,
                        'id_empresa'             => session('idEmpresa'),
                        'id_producto'            => $infoProducto->id_producto,
                        'id_corte'               => null,
                        'id_compra'              => null,
                        'no_orden'               => null,
                        'origen'                 => 'INICIAL',
                        'tipo'                   => 'entrada',
                        'cantidad'               => $cantidad_metros,
                        'cantidad_solicitada'    => $cantidad_metros,
                        'cantidad_total_retirada'=> $cantidad_metros,
                        'tolerancia_aplicada'    => 0,
                        'peso'                   => $pesoUnitario,
                        'peso_neto_libras'       => $pesoUnitario,
                        'precio_unitario_bodega' => $infoProducto->precio_unitario_bodega ?? 0,
                        'saldo_metros'           => $cantidad_metros,
                        'saldo_libras'           => $pesoUnitario,
                        'fecha'                  => now(),
                        'id_usuario'             => session('idUsuario') ?? 1,
                        'comentario'             => "Carga inicial Lote: " . $codigoLoteFinal . " (CSV)",
                        'eliminado'              => 0
                    ]);

                    $piezasCreadas++;
                }

                // 7. Acumular totales de productos (se actualizará después del bucle)
                if (!isset($stockUpdates[$infoProducto->id_producto])) {
                    $stockUpdates[$infoProducto->id_producto] = [
                        'stock_metros' => 0,
                        'peso_total_libras' => 0,
                        'stock_actual' => 0
                    ];
                }
                $stockUpdates[$infoProducto->id_producto]['stock_metros'] += $totalMetros;
                $stockUpdates[$infoProducto->id_producto]['peso_total_libras'] += $totalLibras;
                $stockUpdates[$infoProducto->id_producto]['stock_actual'] += $numero_piezas;
            }
            fclose($handle);

            // ACTUALIZACIÓN DE PRODUCTOS EN BATCH
            foreach ($stockUpdates as $id_prod => $sumas) {
                DB::table('productos')
                    ->where('id_producto', $id_prod)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'stock_metros'      => DB::raw("stock_metros + {$sumas['stock_metros']}"),
                        'peso_total_libras' => DB::raw("peso_total_libras + {$sumas['peso_total_libras']}"),
                        'stock_actual'      => DB::raw("stock_actual + {$sumas['stock_actual']}")
                    ]);
            }

            if($piezasCreadas === 0) {
                throw new \Exception("El archivo CSV estaba vacío o no contenía datos con el formato correcto.");
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Inventario cargado correctamente. Se crearon $piezasCreadas piezas."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if(isset($handle)) fclose($handle);
            Log::error("Error en Carga CSV Inicial: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
