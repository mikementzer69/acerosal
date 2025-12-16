<?php

namespace App\Http\Controllers\Inventario;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Compra;
use App\Models\Inventario\CompraProducto;
use App\Models\Inventario\CompraCosto;
use App\Models\Inventario\CompraFamilia;

class CompraController extends Controller
{
    public function create()
    {
        $proveedores = \App\Models\Inventario\Proveedor::where('eliminado', 0)->orderBy('nombre')->get();
        $familias = \App\Models\Inventario\Familia::where('inactivo', 0)->orderBy('nombre')->get();
        $costos = \App\Models\Inventario\Costos::where('inactivo', 0)->orderBy('nombre')->get();

        return view('compra.crear', compact('proveedores', 'familias', 'costos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'id_proveedor'       => 'required|integer',
            'numero_factura'     => 'required|string',
            'fecha_ingreso'      => 'required|date',
            'fecha_emision_factura' => 'required|date',
            'tasa_cambio'        => 'required|numeric',
        ]);

        // VALIDACIÓN LÓGICA — NO PERMITIR FAMILIAS SIN PRODUCTOS
        if (!$request->has('familias_seleccionadas')) {
            return back()->with('error', 'Debe seleccionar al menos una familia.')->withInput();
        }

        if (!$request->has('familia_producto')) {
            return back()->with('error', 'Debe agregar productos antes de guardar la compra.')->withInput();
        }

        $familiasSeleccionadas = $request->familias_seleccionadas; // hidden inputs por cada tabla familia
        $familiaProducto       = $request->familia_producto;       // input hidden por cada fila producto

        // Verificar si existe familia sin productos
        $conteoFamilias = array_count_values($familiaProducto); // cuenta productos por familia
        foreach ($familiasSeleccionadas as $idFam) {
            if (!isset($conteoFamilias[$idFam])) {
                return back()->with('error', 'Hay una familia sin productos agregados.')->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // =======================================================
            // 1) GUARDAMOS LA COMPRA
            // =======================================================
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

            // =======================================================
            // 2) GUARDAR PRODUCTOS DETALLE
            // =======================================================
            $totalKG  = 0;
            $totalLB  = 0;
            $totalEU  = 0;
            $totalUSD = 0;
            $productosPorFamilia = [];

            $cant = count($request->id_producto);

            for ($i = 0; $i < $cant; $i++) {

                if (!$request->id_producto[$i]) continue;

                $idFam = $request->familia_producto[$i];
                $kg    = floatval($request->peso_kg[$i]);
                $lb    = floatval($request->peso_lb[$i]);
                $eu    = floatval($request->importe_eu[$i]);
                $usd   = floatval($request->importe_usd[$i]);

                // Acumular global
                $totalKG  += $kg;
                $totalLB  += $lb;
                $totalEU  += $eu;
                $totalUSD += $usd;

                // Acumular por familia
                if (!isset($productosPorFamilia[$idFam])) {
                    $productosPorFamilia[$idFam] = [
                        'kg' => 0, 'lb' => 0, 'eur' => 0, 'usd' => 0, 'cantidad' => 0
                    ];
                }

                $productosPorFamilia[$idFam]['kg']      += $kg;
                $productosPorFamilia[$idFam]['lb']      += $lb;
                $productosPorFamilia[$idFam]['eur']     += $eu;
                $productosPorFamilia[$idFam]['usd']     += $usd;
                $productosPorFamilia[$idFam]['cantidad'] += floatval($request->cantidad[$i]);

                CompraProducto::create([
                    'id_compra'      => $compra->id_compra,
                    'id_producto'    => $request->id_producto[$i],
                    'cantidad'       => $request->cantidad[$i],
                    'precio_kg_eu'   => $request->precio_kg_eu[$i],
                    'precio_kg_usd'  => $request->precio_kg_usd[$i],
                    'peso_kg'        => $kg,
                    'peso_libra'     => $lb,
                    'importe_eu'     => $eu,
                    'importe_dolares' => $usd,
                ]);
            }

            // =======================================================
            // 3) GUARDAR COSTOS ADICIONALES
            // =======================================================
            $totalCostosUSD = 0;

            if ($request->has('valor_eu')) {
                $cantCostos = count($request->valor_eu);
                for ($i = 0; $i < $cantCostos; $i++) {
                    $valorEU  = floatval($request->valor_eu[$i]);
                    $valorUSD = floatval($request->valor_usd[$i]);

                    $totalCostosUSD += $valorUSD;

                    if (!$request->id_costo[$i]) continue;

                    CompraCosto::create([
                        'id_compra'   => $compra->id_compra,
                        'id_costo'    => $request->id_costo[$i],
                        'valor_usd'   => $valorUSD,
                        'valor_eu'    => $valorEU,
                    ]);
                }
            }

            // CÁLCULO DE COSTO POR LIBRA GLOBAL
            $costoPorLibra = $totalLB > 0 ? ($totalCostosUSD / $totalLB) : 0;


            // =======================================================
            // 4) GUARDAR RESUMEN POR FAMILIA
            // =======================================================
            foreach ($productosPorFamilia as $idFam => $valores) {
                $pesoLB      = $valores['lb'];
                $importeUSD  = $valores['usd'];

                $precioCIF   = $pesoLB > 0 ? ($importeUSD / $pesoLB) : 0;
                $precioBodega = $precioCIF + $costoPorLibra;
                $totalFamilia = $precioBodega * $pesoLB;

                CompraFamilia::create([
                    'id_compra'              => $compra->id_compra,
                    'id_familia'             => $idFam,
                    'cantidad_total'         => $valores['cantidad'],
                    'peso_total_kg'          => $valores['kg'],
                    'peso_total_libras'      => $pesoLB,
                    'importe_total_eu'       => $valores['eur'],
                    'importe_total_dolares'  => $importeUSD,
                    'precio_cif'             => $precioCIF,
                    'precio_unitario_bodega' => $precioBodega,
                    'total_familia'          => $totalFamilia,
                ]);
            }

            // =======================================================
            // 5) ACTUALIZAR CAMPOS TOTALES EN COMPRA
            // =======================================================
            $compra->update([
                'peso_total_libras'       => $totalLB,
                'peso_total_kg'           => $totalKG,
                'total_costos_adicionales'=> $totalCostosUSD,
                'costos_adicionales_libra'=> $costoPorLibra,
                'importe_total_factura'   => $totalUSD,
                'total_factura'           => $totalUSD + $totalCostosUSD,
            ]);

            DB::commit();

            return redirect()->route('compras.gestion')->with('success', 'Compra registrada correctamente.');

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $ex->getMessage())->withInput();
        }
    }

    public function productosPorFamilia($id)
    {
        $productos = \App\Models\Inventario\Producto::where('id_familia', $id)
            ->where('eliminado', 0)
            ->orderBy('descripcion')
            ->get(['id_producto', 'descripcion']);

        return response()->json($productos);
    }
}