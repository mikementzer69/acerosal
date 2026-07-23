<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Inventario\Compra;
use App\Models\Inventario\CompraProducto;
use App\Models\Inventario\CompraCosto;
use App\Models\Inventario\CompraFamilia;
use App\Models\Inventario\Proveedor;
use App\Models\Inventario\Familia;
use App\Models\Inventario\Costos;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Pieza;

class CompraController extends Controller
{
    public function anular($id)
    {
        // 🔐 BLINDAJE: Solo permitimos que el ID llegue si pertenece a la empresa de la sesión
        // dd("¡POR FIN LLEGÓ! El ID es: " . $id);

        return DB::transaction(function () use ($id) {
            // 1. Buscamos la compra con filtro de empresa y sus relaciones
            $compra = Compra::with(['lotes.piezas' => function($q) {
                $q->where('id_empresa', session('idEmpresa'));
            }])
            ->where('id_empresa', session('idEmpresa'))
            ->findOrFail($id);

            // 2. Recorremos los lotes
            foreach ($compra->lotes as $lote) {
                foreach ($lote->piezas as $pieza) {

                    // VALIDACIÓN CRÍTICA: Movimientos registrados
                    if ($pieza->cantidad_metros_actual < $pieza->cantidad_metros_inicial) {
                        return redirect()->back()->with('error', "No se puede anular: La pieza {$pieza->codigo} ya tiene movimientos registrados.");
                    }

                    // Marcado lógico de la pieza (Stock fuera de inventario)
                    $pieza->update([
                        'eliminado' => 1,
                        'estado' => 'ANULADA'
                    ]);
                }

                // Marcado lógico del lote (Verificando empresa)
                $lote->update(['eliminado' => 1]);
            }

            // 3. Anulación final de la compra principal
            $compra->update([
                'eliminado' => 1,
                'estado' => 'ANULADA'
            ]);

            return redirect()->route('compras.index')->with('success', '¡Compra anulada y stock revertido con éxito!');
        });
    }

    public function show($id)
    {
        $compra = Compra::with([
            'proveedor',
            'compraProductos.producto',
            'compraCostos.costo',
            'compraFamilias.familia'
        ])
        ->where('id_empresa', session('idEmpresa')) // 🔐 Filtro de seguridad
        ->findOrFail($id);

        return view('compras.show', compact('compra'));
    }

    public function detalle($id)
    {
        $compra = Compra::with([
            'proveedor',
            'productos.producto',
            'costos.costo',
            'familias.familia'
        ])
        ->where('id_empresa', session('idEmpresa')) // 🔐 Filtro de seguridad
        ->findOrFail($id);

        return view('compras.detalle', compact('compra'));
    }

    public function index()
    {
        $compras = Compra::where('id_empresa', session('idEmpresa')) // 🔐 Filtro de seguridad
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        // 🔐 Filtramos los catálogos por empresa (excepto familias que es global)
        $proveedores = Proveedor::where('eliminado', 0)
            ->orderBy('nombre')
            ->get();

        $familias = Familia::where('inactivo', 0)->orderBy('nombre')->get();

        $costos = Costos::where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        return view('compras.crear', compact('proveedores', 'familias', 'costos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proveedor'           => 'required|integer',
            'numero_factura'         => 'required|string',
            'fecha_ingreso'          => 'required|date',
            'fecha_emision_factura'  => 'required|date',
            'tasa_cambio'            => 'required|numeric',
        ]);

        if (!$request->has('familias_seleccionadas') || !$request->has('id_producto')) {
            return back()->with('error', 'Debe seleccionar familias y productos.')->withInput();
        }

        DB::beginTransaction();

        try {
            // A. CREAR ENCABEZADO (Con sello de empresa)
            $compra = Compra::create([
                'id_proveedor'           => $request->id_proveedor,
                'id_empresa'             => session('idEmpresa'),
                'numero_factura'         => $request->numero_factura,
                'fecha_ingreso'          => $request->fecha_ingreso,
                'fecha_emision_factura'  => $request->fecha_emision_factura,
                'tasa_cambio'            => $request->tasa_cambio,
                'peso_total_libras'      => 0,
                'peso_total_kg'          => 0,
                'total_costos_adicionales' => 0,
                'costos_adicionales_libra' => 0,
                'importe_total_factura'  => 0,
                'total_factura'          => 0,
                'nueva_compra'           => 1,
            ]);

            $totalKG = 0; $totalLB = 0; $totalEU = 0; $totalUSD = 0;
            $productosPorFamilia = [];

            // B. PROCESAR PRODUCTOS
            for ($i = 0; $i < count($request->id_producto); $i++) {
                if (!$request->id_producto[$i]) continue;

                $idProd = $request->id_producto[$i];
                $idFam  = $request->familia_producto[$i];
                $kg     = floatval($request->peso_kg[$i]);
                $lb     = floatval($request->peso_lb[$i]);
                $cant   = floatval($request->cantidad[$i]);
                $usd    = floatval($request->importe_usd[$i]);

                $eu     = isset($request->importe_eur[$i]) ? floatval($request->importe_eur[$i]) : 0;
                $p_eu   = isset($request->precio_kg_eur[$i]) ? floatval($request->precio_kg_eur[$i]) : 0;

                CompraProducto::create([
                    'id_compra'      => $compra->id_compra,
                    'id_producto'    => $idProd,
                    'cantidad'       => $cant,
                    'precio_kg_eu'   => $p_eu,
                    'precio_kg_usd'  => $request->precio_kg_usd[$i],
                    'peso_kg'        => $kg,
                    'peso_libra'     => $lb,
                    'importe_eu'     => $eu,
                    'importe_dolares'=> $usd,
                    'id_empresa'     => session('idEmpresa'),
                ]);

                $totalKG += $kg; $totalLB += $lb; $totalEU += $eu; $totalUSD += $usd;

                if (!isset($productosPorFamilia[$idFam])) {
                    $productosPorFamilia[$idFam] = ['kg' => 0, 'lb' => 0, 'eur' => 0, 'usd' => 0, 'cantidad' => 0];
                }
                $productosPorFamilia[$idFam]['kg'] += $kg;
                $productosPorFamilia[$idFam]['lb'] += $lb;
                $productosPorFamilia[$idFam]['eur'] += $eu;
                $productosPorFamilia[$idFam]['usd'] += $usd;
                $productosPorFamilia[$idFam]['cantidad'] += $cant;
            }

            // C. COSTOS ADICIONALES
            $totalCostosUSD = 0;
            if ($request->has('valor_usd')) {
                for ($j = 0; $j < count($request->valor_usd); $j++) {
                    if (empty($request->id_costo[$j])) continue;

                    $vUSD = floatval($request->valor_usd[$j]);
                    $totalCostosUSD += $vUSD;

                    CompraCosto::create([
                        'id_compra' => $compra->id_compra,
                        'id_costo'  => $request->id_costo[$j],
                        'valor_usd' => $vUSD,
                        'valor_eu'  => $request->valor_eu[$j] ?? 0,
                        'id_empresa' => session('idEmpresa')
                    ]);
                }
            }

            // D. CÁLCULOS POR FAMILIA
            $costoPorLibra = $totalLB > 0 ? ($totalCostosUSD / $totalLB) : 0;
            foreach ($productosPorFamilia as $idF => $v) {
                $pLB = $v['lb'];
                $iUSD = $v['usd'];
                $pCIF = $pLB > 0 ? ($iUSD / $pLB) : 0;
                $pBodega = $pCIF + $costoPorLibra;

                CompraFamilia::create([
                    'id_compra'              => $compra->id_compra,
                    'id_familia'             => $idF,
                    'cantidad_total'         => $v['cantidad'],
                    'peso_total_kg'          => $v['kg'],
                    'peso_total_libras'      => $pLB,
                    'importe_total_eu'       => $v['eur'],
                    'importe_total_dolares'  => $iUSD,
                    'precio_cif'             => $pCIF,
                    'precio_unitario_bodega' => $pBodega,
                    'total_familia'          => $pBodega * $pLB,
                    'id_empresa'             => session('idEmpresa'),
                ]);
            }

            // E. ACTUALIZAR TOTALES
            $compra->update([
                'peso_total_libras'        => $totalLB,
                'peso_total_kg'            => $totalKG,
                'total_costos_adicionales' => $totalCostosUSD,
                'costos_adicionales_libra' => $costoPorLibra,
                'importe_total_factura'    => $totalUSD,
                'total_factura'            => $totalUSD + $totalCostosUSD,
            ]);

            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');

        } catch (\Exception $ex) {
            DB::rollback();
            Log::error("Error en Compra Store: " . $ex->getMessage());
            return back()->with('error', 'Error al procesar la compra: ' . $ex->getMessage())->withInput();
        }
    }

    public function productosPorFamilia($id)
    {
        $productos = Producto::where('id_familia', $id)
            ->where('id_empresa', session('idEmpresa')) // 🔐 Blindaje Empresa
            ->where('eliminado', 0)
            ->get(['id_producto', 'descripcion', 'milimetros', 'pulgadas', 'codigo', 'peso_lb_mts']);

        $respuesta = $productos->map(function($p) {
            return [
                'id_producto' => $p->id_producto,
                'descripcion' => $p->descripcion,
                'peso_lb_mts' => (float) $p->peso_lb_mts,
                'milimetros'  => $p->milimetros,
                'pulgadas'    => $p->pulgadas,
            ];
        });

        return response()->json($respuesta);
    }

    public function edit($id)
    {
        $compra = Compra::with([
            'proveedor',
            'compraFamilias.familia',
            'compraProductos.producto',
            'compraCostos.costo'
        ])
        ->where('id_empresa', session('idEmpresa')) // 🔐 Blindaje Empresa
        ->findOrFail($id);

        $proveedores = Proveedor::where('id_empresa', session('idEmpresa'))->get();
        $familias    = Familia::all();
        $costos      = Costos::where('id_empresa', session('idEmpresa'))->get();

        return view('compras.edit', compact('compra', 'proveedores', 'familias', 'costos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_proveedor' => 'required',
            'numero_factura' => 'required',
            'fecha_ingreso' => 'required|date',
            'tasa_cambio' => 'required|numeric',
        ]);

        // 🔐 Verificamos propiedad antes de actualizar
        $compra = Compra::where('id_empresa', session('idEmpresa'))->findOrFail($id);

        try {
            if (empty($request->id_producto) || count(array_filter($request->id_producto)) === 0) {
                return back()->with('error', 'No puede registrar una compra sin productos.')->withInput();
            }

            DB::beginTransaction();

            $compra->id_proveedor = $request->id_proveedor;
            $compra->numero_factura = $request->numero_factura;
            $compra->fecha_ingreso = $request->fecha_ingreso;
            $compra->fecha_emision_factura = $request->fecha_emision_factura;
            $compra->tasa_cambio = $request->tasa_cambio;
            $compra->save();

            DB::commit();

            return redirect()->route('compras.index')
                             ->with('success', 'Compra actualizada correctamente (Datos generales).');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Error al actualizar: ' . $e->getMessage())
                             ->withInput();
        }
    }
}
