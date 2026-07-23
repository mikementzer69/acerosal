<?php

namespace App\Http\Controllers\Inventario;

use App\Models\Inventario\Producto;
use App\Http\Controllers\Controller;
use App\Models\Inventario\Compra;
use App\Models\Inventario\CompraProducto;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Pieza;
use App\Models\Inventario\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\String\s;

class InventarioController extends Controller
{

    public function producto($id)
    {
        // Buscamos el producto
        $producto = Producto::with([
            // Cargamos los lotes ordenados por fecha (del más nuevo al más viejo)
            'lotes' => function($query) {
                $query->orderBy('fecha_ingreso', 'desc');
            },
            // Y dentro de cada lote, cargamos sus piezas
            'lotes.piezas'
        ])->findOrFail($id);

        return view('inventario.producto_detalle', compact('producto'));
    }

    public function verFacturaTerminada($id)
    {
        // Buscamos la compra con sus lotes y sus piezas de un solo golpe
// 🔥 CAMBIO: Agregamos 'lotes.producto' para poder leer el nombre y las medidas
    $compra = Compra::with(['proveedor', 'lotes.piezas', 'lotes.producto'])
        ->where('id_empresa', session('idEmpresa'))
        ->findOrFail($id);
        return view('inventario.ver_estatico', compact('compra'));
    }

    public function siguienteCodigoLote($idProducto)
    {
        // 1. Buscamos el número más alto (correlativo) para ESTE producto en ESTA empresa
        $ultimoCorrelativo = Lote::where('id_producto', $idProducto)
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0)
            ->max('correlativo'); // Usamos una columna numérica directa

        $nuevoNumero = ($ultimoCorrelativo ?? 0) + 1;

        return response()->json([
            'correlativo' => $nuevoNumero,
            'codigo' => 'L' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT)
        ]);
    }

    public function automatico()
    {
        $comprasNuevas = Compra::where('eliminado', 0)
            ->where('id_empresa', session('idEmpresa'))
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('inventario.automatico', compact('comprasNuevas'));
    }

    public function detalleCompra($id)
    {
        try {
            $compra = Compra::with(['proveedor', 'empresa'])
                ->where('id_empresa', session('idEmpresa'))
                ->findOrFail($id);

            // Determinamos el estado para el JS: 1 es Pendiente, 0 es Ingresado
            $estadoCompra = ($compra->nueva_compra == 1) ? 'PENDIENTE' : 'INGRESADO';

            $detalle = CompraProducto::with(['producto'])
                ->where('id_compra', $id)
                ->where('id_empresa', session('idEmpresa'))
                ->where('eliminado', 0)
                ->get()
                ->map(function ($cp) use ($id, $estadoCompra) {

                    // 🪄 LA MAGIA: Si la compra ya está ingresada, buscamos las piezas REALES
                    $piezasReales = [];
                    if ($estadoCompra === 'INGRESADO') {
                        $piezasReales = Pieza::where('id_producto', $cp->id_producto)
                            ->whereExists(function ($query) use ($id) {
                                $query->select(DB::raw(1))
                                      ->from('lotes')
                                      ->whereColumn('lotes.id_lote', 'piezas.id_lote')
                                      ->where('lotes.id_compra', $id);
                            })
                            ->get()
                            ->map(function($p) {
                                return [
                                    'Codigo' => $p->codigo,
                                    'Cantidad_Metros_Inicial' => (float)$p->cantidad_metros_inicial,
                                    'Peso_Libras_Inicial' => (float)$p->peso_libras_inicial
                                ];
                            });
                    }

                    return [
                        'idProductos'           => $cp->id_producto,
                        'Codigo'                => $cp->producto->codigo ?? '',
                        'Descripcion'           => $cp->producto->descripcion ?? '',
                        'Milimetros'            => $cp->producto->milimetros ?? '0.00',
                        'Pulgadas'              => $cp->producto->pulgadas ?? '-',
                        'Peso_Total_Libras'     => (float) $cp->peso_libra,
                        'Cantidad_Total_Metros' => (float) $cp->cantidad,
                        'piezas'                => $piezasReales, // Mandamos las piezas de la DB
                        'Total_Piezas'          => count($piezasReales),
                    ];
                });

            return response()->json([
                'success' => true,
                'compra' => [
                    'Numero_Factura' => $compra->numero_factura,
                    'Fecha_EmisionF' => $compra->fecha_emision_factura ?? $compra->fecha_emision,
                    'Fecha_Ingreso'  => $compra->fecha_ingreso,
                    'Proveedor'      => optional($compra->proveedor)->nombre,
                    'Empresa'        => optional($compra->empresa)->nombre,
                    'Estado'         => $estadoCompra, // 👈 LA LLAVE PARA EL JS
                ],
                'detalle' => $detalle,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function verificarCodigoLote(Request $request)
    {
        $idProducto = (int) $request->query('idProducto');
        $codigo = trim($request->query('codigo', ''));

        if (!$idProducto || $codigo === '') {
            return response()->json(['existe' => false]);
        }

        $existe = Lote::where('id_producto', $idProducto)
            ->where('id_empresa', session('idEmpresa'))
            ->where('codigo', $codigo)
            ->where('eliminado', 0)
            ->exists();

        return response()->json(['existe' => $existe]);
    }

public function guardarAutomatico(Request $request)
{
    $idCompra = (int) $request->input('id_compra', 0);

    // 1. Validaciones preventivas
    if ($idCompra <= 0) {
        return response()->json(['success' => false, 'message' => 'Falta el ID de compra.']);
    }

    $lotes = json_decode($request->input('lotes', '[]'), true);
    $todasLasPiezas = json_decode($request->input('piezas', '[]'), true);

    if (empty($lotes)) {
        return response()->json(['success' => false, 'message' => 'No se recibieron lotes para procesar.']);
    }

    // 2. INICIO DEL BLINDAJE TOTAL
    DB::beginTransaction();

    try {
        // A. Bloquear la compra
        $compra = Compra::where('id_empresa', session('idEmpresa'))
            ->where('id_compra', $idCompra)
            ->lockForUpdate()
            ->first();

        if (!$compra) {
            throw new \Exception("Compra no encontrada.");
        }

        if ($compra->nueva_compra == 0) {
            throw new \Exception("Esta compra ya fue procesada anteriormente.");
        }

        foreach ($lotes as $lote) {
            $idProd = (int) ($lote['Id_Productos'] ?? 0);
            $codigoLote = trim($lote['Codigo'] ?? '');

            if ($idProd <= 0 || $codigoLote === '') {
                throw new \Exception("Datos incompletos en uno de los lotes.");
            }

            // B. Bloquear el Producto
            $producto = DB::table('productos')
                ->where('id_producto', $idProd)
                ->where('id_empresa', session('idEmpresa'))
                ->lockForUpdate()
                ->first();

            if (!$producto) {
                throw new \Exception("El producto con ID {$idProd} no existe.");
            }

            // C. Obtener costo de bodega inicial (antes del recalculo)
            $precioDeCosto = DB::table('compra_familia')
                ->where('id_compra', $idCompra)
                ->where('id_familia', $producto->id_familia)
                ->value('precio_unitario_bodega') ?? 0;

            // D. Cálculo de correlativo
            $ultimoCorrelativo = Lote::where('id_producto', $idProd)
                ->where('id_empresa', session('idEmpresa'))
                ->max('correlativo');
            $nuevoCorrelativo = ($ultimoCorrelativo ?? 0) + 1;

            // E. CREAR LOTE
            $loteModel = Lote::create([
                'id_compra'              => $idCompra,
                'id_empresa'             => session('idEmpresa'),
                'id_producto'            => $idProd,
                'codigo'                 => $codigoLote,
                'correlativo'            => $nuevoCorrelativo,
                'fecha_ingreso'          => $lote['Fecha_Ingreso'] ?? now(),
                'peso_total_libras'      => (float) ($lote['Peso_Total_Libras'] ?? 0),
                'cantidad_total_metros'  => (float) ($lote['Cantidad_Total_Metros'] ?? 0),
                'relacion_cantidad_peso' => (float) ($lote['Relacion_Cantidad_Peso'] ?? 0),
                'total_piezas'           => count($todasLasPiezas[$idProd] ?? []),
                'unidad_medida_peso'     => 'LB',
                'unidad_medida_longitud' => 'M',
                'eliminado'              => 0,
            ]);

            // F. CREAR PIEZAS Y KARDEX
            $piezasEsteLote = $todasLasPiezas[$idProd] ?? [];
            $itPieza = 1;

            foreach ($piezasEsteLote as $pieza) {
                $metros = (float) $pieza['Cantidad_Metros_Inicial'];
                $rel    = (float) ($lote['Relacion_Cantidad_Peso'] ?? 0);
                $peso   = $rel * $metros;

                $codigoPieza = $producto->codigo . '-' . $codigoLote . '-' . str_pad($itPieza, 3, '0', STR_PAD_LEFT);

                $nuevaPieza = Pieza::create([
                    'id_empresa'                 => session('idEmpresa'),
                    'id_producto'                => $idProd,
                    'id_lote'                    => $loteModel->id_lote,
                    'codigo'                     => $codigoPieza,
                    'peso_libras_inicial'        => $peso,
                    'cantidad_metros_inicial'    => $metros,
                    'peso_libras_actual'         => $peso,
                    'cantidad_metros_actual'     => $metros,
                    'estado'                     => 'ACTIVA',
                    'eliminado'                  => 0,
                ]);

                // Kardex
               MovimientoInventario::create([
                    'id_pieza'               => $nuevaPieza->id_pieza,
                    'id_producto'            => $idProd,
                    'id_compra'              => $idCompra,
                    'origen'                 => 'COMPRA',
                    'tipo'                   => 'entrada',
                    'cantidad'               => $metros,
                    'peso'                   => $peso,
                    'saldo_metros'           => $metros,
                    'saldo_libras'           => $peso,
                    'precio_unitario_bodega' => $precioDeCosto,
                    'fecha'                  => now(),
                    'id_usuario'             => session('idUsuario') ?? 1,
                    'comentario'             => "Ingreso Automático Compra ID: {$idCompra}",
                    'id_empresa'             => session('idEmpresa'),
                    'eliminado'              => 0,
                ]);

                $itPieza++;
            }

            // G. ACTUALIZACIÓN DEL STOCK EN PRODUCTO
            $pesoNuevoLote      = (float) ($lote['Peso_Total_Libras'] ?? 0);
            $metrosNuevoLote    = (float) ($lote['Cantidad_Total_Metros'] ?? 0);
            $cantidadPiezasLote = count($piezasEsteLote);

            DB::table('productos')
                ->where('id_producto', $idProd)
                ->where('id_empresa', session('idEmpresa'))
                ->update([
                    'stock_actual'      => DB::raw("stock_actual + $cantidadPiezasLote"),
                    'stock_metros'      => DB::raw("stock_metros + $metrosNuevoLote"),
                    'peso_total_libras' => DB::raw("peso_total_libras + $pesoNuevoLote"),
                ]);
        }

        // ---------------------------------------------------------------------
        // PASO CLAVE: LLAMAR AL PROCESADOR DE COSTOS
        // ---------------------------------------------------------------------
        // Instanciamos el controlador de costos y ejecutamos la lógica que vimos hoy
        $costoController = new \App\Http\Controllers\Inventario\CompraCostoController();
        $resultadoCosto = $costoController->procesarCostos($request);

        // Verificamos si el proceso de costo devolvió un error
        $dataCosto = $resultadoCosto->getData();
        if ($dataCosto->status === 'error') {
            throw new \Exception("Inventario guardado pero falló el costo: " . $dataCosto->message);
        }

        // H. Finalizar Compra (Ya se hace dentro de procesarCostos, pero lo aseguramos)
        $compra->update(['nueva_compra' => 0]);

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Inventario y Costo Promedio actualizados correctamente.']);

    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error("Fallo en Ingreso de Inventario: " . $th->getMessage());
        return response()->json(['success' => false, 'message' => 'Error: ' . $th->getMessage()]);
    }
}}
