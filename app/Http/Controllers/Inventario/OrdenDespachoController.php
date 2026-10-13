<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Inventario\OrdenDespacho;
use App\Models\Inventario\OrdenDespachoDetalle;

class OrdenDespachoController extends Controller
{
    /**
     * LISTADO DE ÓRDENES
     */
    public function index(Request $request)
    {
        $idEmpresa = session('idEmpresa');
        $query = OrdenDespacho::with(['cliente', 'vendedor'])
            ->orderByDesc('id_orden_despacho');

        if ($idEmpresa) {
            $query->where('id_empresa', $idEmpresa);
        }

        // Búsqueda por número de orden, cliente o vendedor
        if ($request->filled('busqueda')) {
            $busqueda = trim($request->busqueda);
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_orden', 'like', "%{$busqueda}%")
                  ->orWhereHas('cliente', function ($qc) use ($busqueda) {
                      $qc->where('nombre', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('vendedor', function ($qv) use ($busqueda) {
                      $qv->where('nombre', 'like', "%{$busqueda}%")
                         ->orWhere('apellidos', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', trim($request->estado));
        }

        $ordenes = $query->paginate(15)->withQueryString();

        return view('inventario.despacho.index', compact('ordenes'));
    }

    /**
     * FORMULARIO NUEVA ORDEN
     */
    public function create()
    {
        // Familias (Globales según indicación previa)
        $familias = DB::table('familias')
            ->orderBy('nombre')
            ->get();

        // Clientes filtrados por empresa
        $clientes = DB::table('clientes')
            ->orderBy('nombre')
            ->get();

        // Vendedores con rol Vendedor (visibles en cualquier sucursal)
        $vendedores = DB::table('usuarios as u')
            ->join('roles as r', 'u.id_rol', '=', 'r.id_rol')
            ->where(function ($q) {
                $q->whereRaw('LOWER(r.name) LIKE ?', ['%vendedor%'])
                  ->orWhere('u.id_rol', 3);
            })
            ->where('u.inactivo', 0)
            ->select('u.id_usuario', 'u.nombre', 'u.apellidos')
            ->orderBy('u.nombre')
            ->get();

        // Correlativo orden global (para evitar conflictos de clave única entre empresas)
        $ultimoId = OrdenDespacho::max('id_orden_despacho') ?? 0;
        $numeroOrden = 'OD-' . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);

        return view('inventario.despacho.create', compact(
            'familias',
            'clientes',
            'vendedores',
            'numeroOrden'
        ));
    }

    /**
     * GUARDAR ORDEN (DESCUENTO EN PIEZA, LOTE Y PRODUCTO)
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'numero_orden' => 'required',
                'fecha'        => 'required|date',
                'id_cliente'   => 'required',
                'detalles'     => 'required'
            ]);

            $tipoGuardado = $request->input('tipo_guardado', 'despacho');
            $estado = ($tipoGuardado === 'cotizacion') ? 'COTIZACION' : 'FINALIZADA';

            $orden = OrdenDespacho::create([
                'numero_orden'  => $request->numero_orden,
                'fecha'         => $request->fecha,
                'id_cliente'    => $request->id_cliente,
                'id_usuario'    => $request->id_usuario ?? session('idUsuario'),
                'estado'        => $estado,
                'facturado'     => 0,
                'observaciones' => $request->observaciones ?? null,
                'motivo_precio' => $request->motivo_precio ?? null,
                'id_empresa'    => session('idEmpresa')
            ]);

            $detalles = json_decode($request->detalles, true);

            if (!$detalles || count($detalles) == 0) {
                throw new \Exception("La orden no contiene líneas de detalle.");
            }

            foreach ($detalles as $d) {
                // A. CONSULTA DE PIEZA (con bloqueo pesimista solo si es despacho inmediato)
                $piezaQuery = DB::table('piezas as p')
                    ->join('productos as pr', function($join) {
                        $join->on('p.id_producto', '=', 'pr.id_producto')
                             ->on('p.id_empresa', '=', 'pr.id_empresa');
                    })
                    ->where('p.id_pieza', $d['id_pieza'])
                    ->where('p.id_empresa', session('idEmpresa'))
                    ->select('p.*', 'pr.tolerancia as tolerancia_producto', 'pr.peso_lb_mts','pr.precio_unitario_bodega');

                if ($tipoGuardado !== 'cotizacion') {
                    $pieza = $piezaQuery->lockForUpdate()->first();
                } else {
                    $pieza = $piezaQuery->first();
                }

                if (!$pieza) {
                    throw new \Exception("La pieza con ID {$d['id_pieza']} no existe o no pertenece a su empresa.");
                }

                $disponibleMetros   = floatval($pieza->cantidad_metros_actual);
                $disponibleLibras   = floatval($pieza->peso_libras_actual ?? 0);
                $cantidadSolicitada = floatval($d['cantidad_metros']);
                $tolerancia         = floatval($d['merma_mts'] ?? ($pieza->tolerancia_producto ?? 0));
                $pesoPorMetro       = floatval($pieza->peso_lb_mts ?? 0);

                // Cálculo de Peso Neto: $Peso_{neto} = Cantidad_{solicitada} \times Factor_{lb/mt}$
                $pesoNetoLibras = $cantidadSolicitada * $pesoPorMetro;

                $cantidadTotalSalida = $cantidadSolicitada + $tolerancia;
                if ($tipoGuardado !== 'cotizacion' && $cantidadTotalSalida > $disponibleMetros) {
                    $cantidadTotalSalida = $disponibleMetros;
                    $tolerancia = $cantidadTotalSalida - $cantidadSolicitada;
                }

                $pesoRealDescontado = floatval($d['cantidad_libras'] ?? 0);

                // --- SEGURO DE VIDA PARA EL PRECIO ---
                $precioVenta = floatval($d['precio_venta_sin_iva'] ?? 0);
                if ($precioVenta <= 0) {
                    $famInfo = DB::table('familias')->where('id_familia', $d['id_familia'])->first();
                    if ($famInfo) {
                        $tipoPrecio = $d['tipo_precio'] ?? 'lista';
                        if ($tipoPrecio === 'especial' && floatval($famInfo->precio_especial) > 0 && $famInfo->precio_autorizado === 'S') {
                            $precioVenta = floatval($famInfo->precio_especial);
                        } else {
                            $precioVenta = floatval($famInfo->precio_lista ?? 0);
                        }
                    }
                }

                // BLOQUEO ESTRICTO: La calidad debe tener precio configurado (precio de lista o especial)
                if ($precioVenta <= 0) {
                    $nomFamilia = DB::table('familias')->where('id_familia', $d['id_familia'])->value('nombre') ?? "ID {$d['id_familia']}";
                    throw new \Exception("La calidad '{$nomFamilia}' no tiene precio de lista ni especial configurado. No se puede guardar la orden sin precio.");
                }

                // Blindaje ID Lote para evitar valores vacíos ''
                $idLoteReal = !empty($d['id_lote']) ? intval($d['id_lote']) : intval($pieza->id_lote ?? 0);

                // D. GUARDAR DETALLE
                DB::table('ordenes_despacho_detalle')->insert([
                    'id_orden_despacho' => $orden->id_orden_despacho,
                    'id_familia'        => $d['id_familia'],
                    'id_producto'       => $d['id_producto'],
                    'id_lote'           => $idLoteReal,
                    'id_pieza'          => $d['id_pieza'],
                    'medida_solicitada' => $d['medida_solicitada'] ?? null,
                    'cantidad_metros'   => $cantidadSolicitada,
                    'cantidad_libras'   => $pesoRealDescontado,
                    'merma_metros'      => $tolerancia,
                    'merma_libras'      => floatval($d['merma_lbs'] ?? 0),
                    'precio_venta_sin_iva' => $precioVenta,
                    'id_empresa'        => session('idEmpresa'),
                    'created_at'        => now(),
                    'updated_at'        => now()
                ]);

                // --- SI ES DESPACHO INMEDIATO: DESCONTAR INVENTARIO Y REGISTRAR KARDEX ---
                if ($tipoGuardado !== 'cotizacion') {
                    $nuevoSaldoMetros = $disponibleMetros - $cantidadTotalSalida;
                    $nuevoSaldoLibras = $disponibleLibras - $pesoRealDescontado;

                    if($nuevoSaldoMetros < 0) $nuevoSaldoMetros = 0;
                    if($nuevoSaldoLibras < 0) $nuevoSaldoLibras = 0;

                    // 1. ACTUALIZAR PIEZA (Con blindaje)
                    DB::table('piezas')
                        ->where('id_pieza', $d['id_pieza'])
                        ->where('id_empresa', session('idEmpresa'))
                        ->update([
                            'cantidad_metros_actual' => $nuevoSaldoMetros,
                            'peso_libras_actual'     => $nuevoSaldoLibras,
                            'updated_at' => now()
                        ]);

                    // 2. REGISTRAR KARDEX
                    DB::table('movimientos_inventario')->insert([
                        'id_pieza'               => $d['id_pieza'],
                        'id_empresa'             => session('idEmpresa'),
                        'id_producto'            => $d['id_producto'],
                        'medida_solicitada'      => $d['medida_solicitada'] ?? null,
                        'origen'                 => 'Orden Despacho',
                        'tipo'                   => 'salida',
                        'cantidad'               => $cantidadSolicitada,
                        'cantidad_solicitada'    => $cantidadSolicitada,
                        'peso_neto_libras'       => $pesoNetoLibras,
                        'tolerancia_aplicada'    => $tolerancia,
                        'merma_libras_grabada'   => $d['merma_lbs'] ?? 0,
                        'cantidad_total_retirada'=> $cantidadTotalSalida,
                        'peso'                   => $pesoRealDescontado,
                        'precio_venta_sin_iva'   => $precioVenta,
                        'saldo_metros'           => $nuevoSaldoMetros,
                        'saldo_libras'           => $nuevoSaldoLibras,
                        'fecha'                  => now(),
                        'id_usuario'             => session('idUsuario'),
                        'comentario'             => "Despacho + Merma de corte ({$tolerancia}m). Orden #{$request->numero_orden}",
                        'no_orden'               => $request->numero_orden,
                        'precio_unitario_bodega' => $pieza->precio_unitario_bodega ?? 0,
                        'eliminado'              => 0,
                        'created_at'             => now()
                    ]);

                    // 3. ACTUALIZAR LOTE (Con blindaje)
                    $camposLote = [
                        'cantidad_total_metros' => DB::raw("cantidad_total_metros - $cantidadTotalSalida"),
                        'peso_total_libras'     => DB::raw("peso_total_libras - $pesoRealDescontado")
                    ];

                    // Si la pieza individual se agotó, le restamos 1 al contador de piezas del lote
                    if ($nuevoSaldoMetros <= 0.001) {
                        $camposLote['total_piezas'] = DB::raw("total_piezas - 1");
                    }

                    DB::table('lotes')
                        ->where('id_lote', $idLoteReal)
                        ->where('id_empresa', session('idEmpresa'))
                        ->update($camposLote);

                    // 4. ACTUALIZAR PRODUCTO (Con blindaje)
                    DB::table('productos')
                        ->where('id_producto', $d['id_producto'])
                        ->where('id_empresa', session('idEmpresa'))
                        ->update([
                            'stock_metros'      => DB::raw("stock_metros - $cantidadTotalSalida"),
                            'peso_total_libras' => DB::raw("peso_total_libras - $pesoRealDescontado")
                        ]);

                    // 5. APAGAR PERMISO DE PRECIO ESPECIAL POR SEGURIDAD
                    if (isset($d['tipo_precio']) && $d['tipo_precio'] === 'especial') {
                        DB::table('familias')
                            ->where('id_familia', $d['id_familia'])
                            ->update(['precio_autorizado' => 'N']);
                    }
                }
            }

            DB::commit();

            $mensajeExito = ($tipoGuardado === 'cotizacion')
                ? '¡Cotización guardada exitosamente! No se ha descontado inventario.'
                : '¡Orden procesada! Stock y peso actualizados con merma de corte.';

            return response()->json([
                'status'   => 'success',
                'message'  => $mensajeExito,
                'orden_id' => $orden->id_orden_despacho
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error en Acerosal Store: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $orden = OrdenDespacho::with([
            'cliente',
            'vendedor',
            'detalles.familia',
            'detalles.producto',
            'detalles.lote',
            'detalles.pieza'
        ])
        ->where('id_empresa', session('idEmpresa'))
        ->findOrFail($id);

        return view('inventario.despacho.show', compact('orden'));
    }

    /**
     * VISTA IMPRIMIBLE VALORIZADA DE COTIZACIÓN
     */
    public function imprimirCotizacion($id)
    {
        $orden = OrdenDespacho::with([
            'cliente',
            'vendedor',
            'detalles.familia',
            'detalles.producto',
            'detalles.lote',
            'detalles.pieza'
        ])
        ->where('id_empresa', session('idEmpresa'))
        ->findOrFail($id);

        $empresa = DB::table('empresas')
            ->where('id_empresa', session('idEmpresa'))
            ->first();

        return view('inventario.despacho.cotizacion_imprimir', compact('orden', 'empresa'));
    }

public function productosPorFamilia($id_familia)
{
    // 1. Usamos un try-catch para que si algo falla, nos diga QUÉ es en la consola
    try {
        $productos = DB::table('productos as p')
            ->join('familias as f', 'p.id_familia', '=', 'f.id_familia')
            // OJO: Si tu tabla de nombres de ubicación NO se llama 'ubicaciones',
            // cambiá esa palabra aquí abajo:
            ->leftJoin('ubicaciones as u', 'p.id_ubicacion', '=', 'u.id_ubicacion')
            ->where('p.id_familia', $id_familia)
            ->where('p.id_empresa', session('idEmpresa'))
            ->where('p.eliminado', 0)
            ->select('p.*', 'f.nombre as familia_nombre', 'u.nombre as nombre_ubicacion')
            ->orderBy('p.descripcion')
            ->get();

        return response()->json($productos);
    } catch (\Exception $e) {
        // Esto te avisará en la consola del navegador si la tabla no existe
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function lotesPorProducto($id_producto)
    {
        $lotes = DB::table('lotes')
            ->where('id_producto', $id_producto)
            ->where('id_empresa', session('idEmpresa'))
            ->where('cantidad_total_metros', '>', 0)
            ->orderBy('cantidad_total_metros', 'asc')
            ->get();

        return response()->json($lotes);
    }

    public function piezasPorLote($id_lote)
    {
        $piezas = DB::table('piezas')
            ->where('id_lote', $id_lote)
            ->where('id_empresa', session('idEmpresa'))
            ->where('cantidad_metros_actual', '>', 0.01)
            ->orderBy('codigo')
            ->get();

        $piezasFiltradas = [];
        foreach ($piezas as $p) {
            $disponible = (float) $p->cantidad_metros_actual;
            if ($disponible > 0.001) {
                $p->disponible_real = $disponible;
                $p->tiene_reserva   = false;
                $p->info_reserva    = "0.00";
                $p->cantidad_metros_actual = $disponible;
                $piezasFiltradas[] = $p;
            }
        }

        return response()->json($piezasFiltradas);
    }

    public function anular($id)
    {
        DB::beginTransaction();
        try {
            $orden = OrdenDespacho::where('id_empresa', session('idEmpresa'))->findOrFail($id);

            if ($orden->facturado == 1) {
                return back()->with('error', '🚫 Error: La orden ya fue facturada y no puede anularse.');
            }
            if ($orden->estado == 'ANULADA') {
                return back()->with('info', 'La orden ya se encuentra anulada.');
            }

            // Si es una Cotización, no se tocó inventario, solo se cancela el estado
            if ($orden->estado == 'COTIZACION') {
                $orden->update(['estado' => 'ANULADA']);
                DB::commit();
                return back()->with('success', "✅ Cotización #{$orden->numero_orden} cancelada exitosamente.");
            }

            $detalles = DB::table('ordenes_despacho_detalle')
                ->where('id_orden_despacho', $id)
                ->where('id_empresa', session('idEmpresa'))
                ->get();

            foreach ($detalles as $d) {
                $pieza = DB::table('piezas')->where('id_pieza', $d->id_pieza)->where('id_empresa', session('idEmpresa'))->first();
                $producto = DB::table('productos')->where('id_producto', $d->id_producto)->where('id_empresa', session('idEmpresa'))->first();

                $metrosADevolver = floatval($d->cantidad_metros) + floatval($d->merma_metros ?? 0);
                $nuevosMetros = $pieza->cantidad_metros_actual + $metrosADevolver;
                $nuevoPesoLbs = $nuevosMetros * ($producto->peso_lb_mts ?? 0);

                // A. Reversión en PIEZA (Metros y Libras)
                DB::table('piezas')
                    ->where('id_pieza', $d->id_pieza)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'cantidad_metros_actual' => $nuevosMetros,
                        'peso_libras_actual'     => $nuevoPesoLbs,
                        'updated_at' => now()
                    ]);

                // B. Reversión en LOTE
                DB::table('lotes')
                    ->where('id_lote', $d->id_lote)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'cantidad_total_metros' => DB::raw("cantidad_total_metros + $metrosADevolver"),
                        'peso_total_libras'     => DB::raw("peso_total_libras + $d->cantidad_libras"),
                        'total_piezas'          => DB::raw("total_piezas + 1") // Agregamos esta línea
                    ]);

                // C. Reversión en PRODUCTO
                DB::table('productos')
                    ->where('id_producto', $d->id_producto)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'stock_metros'      => DB::raw("stock_metros + $metrosADevolver"),
                        'peso_total_libras' => DB::raw("peso_total_libras + $d->cantidad_libras")
                    ]);

                // D. Registro en KARDEX
                DB::table('movimientos_inventario')->insert([
                    'id_pieza'     => $d->id_pieza,
                    'id_empresa'   => session('idEmpresa'),
                    'id_producto'  => $d->id_producto,
                    'origen'       => 'Anulación Orden',
                    'tipo'         => 'entrada',
                    'cantidad'     => $d->cantidad_metros,
                    'peso'         => $d->cantidad_libras,
                    'saldo_metros' => $nuevosMetros,
                    'saldo_libras' => $nuevoPesoLbs,
                    'fecha'        => now(),
                    'id_usuario'   => session('idUsuario'),
                    'comentario'   => "REVERSIÓN POR ANULACIÓN - Orden #{$orden->numero_orden}",
                    'no_orden'     => $orden->numero_orden,
                    'created_at'   => now()
                ]);
            }

            $orden->update(['estado' => 'ANULADA']);

            DB::commit();
            return back()->with('success', '✅ Orden anulada exitosamente. El inventario ha sido restablecido.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error al anular orden {$id}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error crítico al anular: ' . $e->getMessage());
        }
    }

    /**
     * CONVERTIR COTIZACIÓN A ORDEN DE DESPACHO
     * (Verifica stock disponible, descuenta piezas, lotes, productos y registra en Kardex)
     */
    public function convertirADespacho($id)
    {
        DB::beginTransaction();
        try {
            $orden = OrdenDespacho::where('id_empresa', session('idEmpresa'))->findOrFail($id);

            if ($orden->estado !== 'COTIZACION') {
                return back()->with('error', "La orden #{$orden->numero_orden} no está en estado de Cotización (Estado actual: {$orden->estado}).");
            }

            $detalles = DB::table('ordenes_despacho_detalle')
                ->where('id_orden_despacho', $id)
                ->where('id_empresa', session('idEmpresa'))
                ->get();

            if ($detalles->isEmpty()) {
                throw new \Exception("La cotización no contiene líneas de detalle registradas.");
            }

            foreach ($detalles as $d) {
                // 1. Bloqueo pesimista y verificación de pieza
                $pieza = DB::table('piezas as p')
                    ->join('productos as pr', function($join) {
                        $join->on('p.id_producto', '=', 'pr.id_producto')
                             ->on('p.id_empresa', '=', 'pr.id_empresa');
                    })
                    ->where('p.id_pieza', $d->id_pieza)
                    ->where('p.id_empresa', session('idEmpresa'))
                    ->select('p.*', 'pr.tolerancia as tolerancia_producto', 'pr.peso_lb_mts', 'pr.precio_unitario_bodega')
                    ->lockForUpdate()
                    ->first();

                if (!$pieza) {
                    throw new \Exception("La pieza con ID {$d->id_pieza} ya no existe en el inventario.");
                }

                $disponibleMetros   = floatval($pieza->cantidad_metros_actual);
                $disponibleLibras   = floatval($pieza->peso_libras_actual ?? 0);
                $cantidadSolicitada = floatval($d->cantidad_metros);
                $tolerancia         = floatval($d->merma_metros ?? ($pieza->tolerancia_producto ?? 0));
                $pesoPorMetro       = floatval($pieza->peso_lb_mts ?? 0);

                $cantidadTotalSalida = $cantidadSolicitada + $tolerancia;

                // VALIDACIÓN CRÍTICA DE STOCK
                if ($cantidadTotalSalida > $disponibleMetros) {
                    $codigoPieza = $pieza->codigo ?? "ID {$d->id_pieza}";
                    throw new \Exception("Stock insuficiente en la pieza {$codigoPieza}: Tiene " . number_format($disponibleMetros, 4) . " m disponibles, pero se requieren " . number_format($cantidadTotalSalida, 4) . " m (incluyendo corte/merma). Por favor reasigne la pieza.");
                }

                $pesoNetoLibras     = $cantidadSolicitada * $pesoPorMetro;
                $pesoRealDescontado = floatval($d->cantidad_libras ?? 0);

                $nuevoSaldoMetros = $disponibleMetros - $cantidadTotalSalida;
                $nuevoSaldoLibras = $disponibleLibras - $pesoRealDescontado;

                if ($nuevoSaldoMetros < 0) $nuevoSaldoMetros = 0;
                if ($nuevoSaldoLibras < 0) $nuevoSaldoLibras = 0;

                // 2. ACTUALIZAR PIEZA
                DB::table('piezas')
                    ->where('id_pieza', $d->id_pieza)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'cantidad_metros_actual' => $nuevoSaldoMetros,
                        'peso_libras_actual'     => $nuevoSaldoLibras,
                        'updated_at'             => now()
                    ]);

                // 3. REGISTRAR KARDEX (movimientos_inventario)
                DB::table('movimientos_inventario')->insert([
                    'id_pieza'                => $d->id_pieza,
                    'id_empresa'              => session('idEmpresa'),
                    'id_producto'             => $d->id_producto,
                    'medida_solicitada'       => $d->medida_solicitada ?? null,
                    'origen'                  => 'Orden Despacho',
                    'tipo'                    => 'salida',
                    'cantidad'                => $cantidadSolicitada,
                    'cantidad_solicitada'     => $cantidadSolicitada,
                    'peso_neto_libras'        => $pesoNetoLibras,
                    'tolerancia_aplicada'     => $tolerancia,
                    'merma_libras_grabada'    => $d->merma_libras ?? 0,
                    'cantidad_total_retirada' => $cantidadTotalSalida,
                    'peso'                    => $pesoRealDescontado,
                    'precio_venta_sin_iva'    => $d->precio_venta_sin_iva,
                    'saldo_metros'            => $nuevoSaldoMetros,
                    'saldo_libras'            => $nuevoSaldoLibras,
                    'fecha'                   => now(),
                    'id_usuario'              => session('idUsuario'),
                    'comentario'              => "Despacho + Merma de corte ({$tolerancia}m). Cotización convertida a Orden #{$orden->numero_orden}",
                    'no_orden'                => $orden->numero_orden,
                    'precio_unitario_bodega'  => $pieza->precio_unitario_bodega ?? 0,
                    'eliminado'               => 0,
                    'created_at'              => now()
                ]);

                // 4. ACTUALIZAR LOTE
                $idLoteReal = !empty($d->id_lote) ? intval($d->id_lote) : intval($pieza->id_lote ?? 0);
                $camposLote = [
                    'cantidad_total_metros' => DB::raw("cantidad_total_metros - $cantidadTotalSalida"),
                    'peso_total_libras'     => DB::raw("peso_total_libras - $pesoRealDescontado")
                ];
                if ($nuevoSaldoMetros <= 0.001) {
                    $camposLote['total_piezas'] = DB::raw("total_piezas - 1");
                }

                DB::table('lotes')
                    ->where('id_lote', $idLoteReal)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update($camposLote);

                // 5. ACTUALIZAR PRODUCTO
                DB::table('productos')
                    ->where('id_producto', $d->id_producto)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'stock_metros'      => DB::raw("stock_metros - $cantidadTotalSalida"),
                        'peso_total_libras' => DB::raw("peso_total_libras - $pesoRealDescontado")
                    ]);
            }

            // Cambiar estado a FINALIZADA y actualizar fecha al momento del despacho
            $orden->update([
                'estado' => 'FINALIZADA',
                'fecha'  => now()->toDateString()
            ]);

            DB::commit();

            return redirect()->route('inventario.despacho.show', $orden->id_orden_despacho)
                ->with('success', "✅ ¡Cotización #{$orden->numero_orden} convertida exitosamente a Orden de Despacho! El inventario y Kardex han sido actualizados.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error al convertir cotización {$id} a despacho: " . $e->getMessage());
            return back()->with('error', "No se pudo convertir la cotización a despacho: " . $e->getMessage());
        }
    }
}

