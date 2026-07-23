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
    public function index()
    {
        $ordenes = OrdenDespacho::with(['cliente', 'vendedor'])
            ->where('id_empresa', session('idEmpresa'))
            ->orderByDesc('id_orden_despacho')
            ->get();

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

        // Vendedores filtrados por empresa (usuarios con rol = 2)
        $vendedores = DB::table('usuarios')
            ->where('id_empresa', session('idEmpresa'))
            ->where('id_rol', 2)
            ->where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        // Correlativo orden por empresa
        $ultimoId = OrdenDespacho::where('id_empresa', session('idEmpresa'))->max('id_orden_despacho') ?? 0;
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

            $orden = OrdenDespacho::create([
                'numero_orden'  => $request->numero_orden,
                'fecha'         => $request->fecha,
                'id_cliente'    => $request->id_cliente,
                'id_usuario'    => session('idUsuario'),
                'estado'        => 'FINALIZADA',
                'facturado'     => 0,
                'observaciones' => $request->observaciones ?? null,
                'id_empresa'    => session('idEmpresa')
            ]);

            $detalles = json_decode($request->detalles, true);

            if (!$detalles || count($detalles) == 0) {
                throw new \Exception("La orden no contiene líneas de detalle.");
            }

            foreach ($detalles as $d) {
                // A. BLOQUEO PESIMISTA: Filtrando por empresa para evitar cruces
                $pieza = DB::table('piezas as p')
                    ->join('productos as pr', function($join) {
                        $join->on('p.id_producto', '=', 'pr.id_producto')
                             ->on('p.id_empresa', '=', 'pr.id_empresa');
                    })
                    ->where('p.id_pieza', $d['id_pieza'])
                    ->where('p.id_empresa', session('idEmpresa'))
                    ->select('p.*', 'pr.tolerancia as tolerancia_producto', 'pr.peso_lb_mts','pr.precio_unitario_bodega')
                    ->lockForUpdate()
                    ->first();

                if (!$pieza) {
                    throw new \Exception("La pieza con ID {$d['id_pieza']} no existe o no pertenece a su empresa.");
                }

                $disponibleMetros   = floatval($pieza->cantidad_metros_actual);
                $disponibleLibras   = floatval($pieza->peso_libras_actual ?? 0);
                $cantidadSolicitada = floatval($d['cantidad_metros']);
                $tolerancia         = floatval($pieza->tolerancia_producto ?? 0);
                $pesoPorMetro       = floatval($pieza->peso_lb_mts ?? 0);

                // Cálculo de Peso Neto: $Peso_{neto} = Cantidad_{solicitada} \times Factor_{lb/mt}$
                $pesoNetoLibras = $cantidadSolicitada * $pesoPorMetro;

                $cantidadTotalSalida = $cantidadSolicitada + $tolerancia;
                if ($cantidadTotalSalida > $disponibleMetros) {
                    $cantidadTotalSalida = $disponibleMetros;
                    $tolerancia = $cantidadTotalSalida - $cantidadSolicitada;
                }

                $pesoRealDescontado = floatval($d['cantidad_libras'] ?? 0);

                // D. GUARDAR DETALLE
                DB::table('ordenes_despacho_detalle')->insert([
                    'id_orden_despacho' => $orden->id_orden_despacho,
                    'id_familia'        => $d['id_familia'],
                    'id_producto'       => $d['id_producto'],
                    'id_lote'           => $d['id_lote'],
                    'id_pieza'          => $d['id_pieza'],
                    'medida_solicitada' => $d['medida_solicitada'] ?? null,
                    'cantidad_metros'   => $cantidadSolicitada,
                    'cantidad_libras'   => $pesoRealDescontado,
                    'merma_metros'      => $tolerancia,
                    'merma_libras'      => floatval($d['merma_lbs'] ?? 0),
                    'id_empresa'        => session('idEmpresa'),
                    'created_at'        => now(),
                    'updated_at'        => now()
                ]);

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
                    'merma_libras_grabada'   => $d['merma_lbs'],
                    'cantidad_total_retirada'=> $cantidadTotalSalida,
                    'peso'                   => $pesoRealDescontado,
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
                    ->where('id_lote', $d['id_lote'])
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
            }

            DB::commit();

            return response()->json([
                'status'   => 'success',
                'message' => '¡Orden procesada! Stock y peso actualizados con merma de corte.',
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

            $detalles = DB::table('ordenes_despacho_detalle')
                ->where('id_orden_despacho', $id)
                ->where('id_empresa', session('idEmpresa'))
                ->get();

            foreach ($detalles as $d) {
                $pieza = DB::table('piezas')->where('id_pieza', $d->id_pieza)->where('id_empresa', session('idEmpresa'))->first();
                $producto = DB::table('productos')->where('id_producto', $d->id_producto)->where('id_empresa', session('idEmpresa'))->first();

                $nuevosMetros = $pieza->cantidad_metros_actual + $d->cantidad_metros;
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
                        'cantidad_total_metros' => DB::raw("cantidad_total_metros + $d->cantidad_metros"),
                        'peso_total_libras'     => DB::raw("peso_total_libras + $d->cantidad_libras"),
                        'total_piezas'          => DB::raw("total_piezas + 1") // Agregamos esta línea
                    ]);

                // C. Reversión en PRODUCTO
                DB::table('productos')
                    ->where('id_producto', $d->id_producto)
                    ->where('id_empresa', session('idEmpresa'))
                    ->update([
                        'stock_metros'      => DB::raw("stock_metros + $d->cantidad_metros"),
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
}

