<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CompraCostoController extends Controller
{
    public function procesarCostos(Request $request)
    {
        $idCompra = (int) $request->id_compra;
        $idEmpresa = session('idEmpresa');

        DB::beginTransaction();
        try {
            // 1. FLETE / COSTOS ADICIONALES GLOBALES DE LA COMPRA
            $fleteTotal = DB::table('compra_costo')
                ->where('id_compra', $idCompra)
                ->where('id_empresa', $idEmpresa)
                ->where('eliminado', 0)
                ->sum('valor_usd');

            $pesoTotalFactura = DB::table('compra_familia')
                ->where('id_compra', $idCompra)
                ->where('id_empresa', $idEmpresa)
                ->sum('peso_total_libras');

            $gastoPorLibra = ($pesoTotalFactura > 0) ? ($fleteTotal / $pesoTotalFactura) : 0;

            // 2. RESUMEN DE ESTA ENTRADA POR PRODUCTO (Para sincronizar el peso_total_libras real de cada producto)
            $resumenEntradaProductos = DB::table('piezas')
                ->join('lotes', 'piezas.id_lote', '=', 'lotes.id_lote')
                ->where('lotes.id_compra', $idCompra)
                ->where('piezas.id_empresa', $idEmpresa)
                ->select(
                    'piezas.id_producto',
                    DB::raw('SUM(piezas.cantidad_metros_actual) as metros_nuevos'),
                    DB::raw('SUM(piezas.peso_libras_actual) as peso_nuevo')
                )
                ->groupBy('piezas.id_producto')
                ->get()
                ->keyBy('id_producto');

            foreach ($resumenEntradaProductos as $idProd => $entProd) {
                $p = DB::table('productos')
                    ->where('id_producto', $idProd)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if ($p) {
                    $pesoTotalReal = floatval($p->stock_metros) * (floatval($p->peso_lb_mts) ?: 1);
                    DB::table('productos')
                        ->where('id_producto', $idProd)
                        ->where('id_empresa', $idEmpresa)
                        ->update([
                            'peso_total_libras' => $pesoTotalReal
                        ]);
                }
            }

            // 3. CÁLCULO DEL COSTO PROMEDIO POR FAMILIA Y REPLICACIÓN EN TODOS LOS PRODUCTOS DE LA FAMILIA
            $resumenEntradaFamilias = DB::table('piezas')
                ->join('lotes', 'piezas.id_lote', '=', 'lotes.id_lote')
                ->join('productos', function ($join) {
                    $join->on('piezas.id_producto', '=', 'productos.id_producto')
                         ->on('piezas.id_empresa', '=', 'productos.id_empresa');
                })
                ->where('lotes.id_compra', $idCompra)
                ->where('piezas.id_empresa', $idEmpresa)
                ->select(
                    'productos.id_familia',
                    DB::raw('SUM(piezas.cantidad_metros_actual) as metros_nuevos_familia'),
                    DB::raw('SUM(piezas.peso_libras_actual) as peso_nuevo_familia')
                )
                ->groupBy('productos.id_familia')
                ->get();

            foreach ($resumenEntradaFamilias as $entFam) {
                $idFamilia = $entFam->id_familia;

                $fam = DB::table('compra_familia')
                    ->where('id_compra', $idCompra)
                    ->where('id_familia', $idFamilia)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$fam) {
                    $fam = DB::table('compra_familia')
                        ->where('id_compra', $idCompra)
                        ->where('id_familia', $idFamilia)
                        ->first();
                }

                if (!$fam) continue;

                // Obtener todos los productos de esta familia en la empresa
                $productosFamilia = DB::table('productos')
                    ->where('id_familia', $idFamilia)
                    ->where('id_empresa', $idEmpresa)
                    ->where('eliminado', 0)
                    ->get();

                // A. Valor y peso que ya existía en bodega para toda la familia (antes de esta entrada)
                $pesoViejoFamilia = 0;
                $valorBodegaViejaFamilia = 0;

                foreach ($productosFamilia as $prodFam) {
                    $pesoNuevoProd = isset($resumenEntradaProductos[$prodFam->id_producto])
                        ? floatval($resumenEntradaProductos[$prodFam->id_producto]->peso_nuevo)
                        : 0;

                    $pesoActualProd = floatval($prodFam->peso_total_libras);
                    $pesoViejoProd = max(0, $pesoActualProd - $pesoNuevoProd);

                    $pesoViejoFamilia += $pesoViejoProd;
                    $valorBodegaViejaFamilia += ($pesoViejoProd * floatval($prodFam->precio_unitario_bodega));
                }

                // B. Valor de la entrada nueva de la familia (Mercadería + Flete/Costos proporcionales)
                $pesoNuevoFamilia = floatval($entFam->peso_nuevo_familia);
                $precioCIFLibra = (floatval($fam->peso_total_libras) > 0)
                    ? (floatval($fam->importe_total_dolares) / floatval($fam->peso_total_libras))
                    : floatval($fam->precio_cif);

                $valorMercaNuevaFamilia = $precioCIFLibra * $pesoNuevoFamilia;
                $valorFleteNuevoFamilia = $pesoNuevoFamilia * $gastoPorLibra;
                $valorTotalEntradaFamilia = $valorMercaNuevaFamilia + $valorFleteNuevoFamilia;

                // C. Nuevo Costo Promedio de la Familia (por libra)
                $pesoTotalFamilia = $pesoViejoFamilia + $pesoNuevoFamilia;
                if ($pesoTotalFamilia > 0) {
                    $nuevoCostoFamilia = round(($valorBodegaViejaFamilia + $valorTotalEntradaFamilia) / $pesoTotalFamilia, 4);
                } else {
                    $nuevoCostoFamilia = round(floatval($fam->precio_unitario_bodega), 4);
                }

                // D. Replicar el precio_unitario_bodega en TODOS los productos de esta familia
                DB::table('productos')
                    ->where('id_familia', $idFamilia)
                    ->where('id_empresa', $idEmpresa)
                    ->update([
                        'precio_unitario_bodega' => $nuevoCostoFamilia
                    ]);

                // E. Actualizar también el costo en compra_familia y en los movimientos de Kardex de esta compra
                DB::table('compra_familia')
                    ->where('id_compra_familia', $fam->id_compra_familia)
                    ->update([
                        'precio_unitario_bodega' => $nuevoCostoFamilia
                    ]);

                $idsProductosFamilia = $productosFamilia->pluck('id_producto')->toArray();
                if (!empty($idsProductosFamilia)) {
                    DB::table('movimientos_inventario')
                        ->where('id_compra', $idCompra)
                        ->where('id_empresa', $idEmpresa)
                        ->whereIn('id_producto', $idsProductosFamilia)
                        ->update([
                            'precio_unitario_bodega' => $nuevoCostoFamilia
                        ]);
                }
            }

            DB::table('compras')->where('id_compra', $idCompra)->update(['nueva_compra' => 0]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => '¡Costo promedio calculado por familia y replicado en sus productos!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
