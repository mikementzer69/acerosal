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
        // 1. FLETE GLOBAL
        $fleteTotal = DB::table('compra_costo')
            ->where('id_compra', $idCompra)->where('id_empresa', $idEmpresa)
            ->where('eliminado', 0)->sum('valor_usd');

        $pesoTotalFactura = DB::table('compra_familia')
            ->where('id_compra', $idCompra)->where('id_empresa', $idEmpresa)
            ->sum('peso_total_libras');

        $gastoPorLibra = ($pesoTotalFactura > 0) ? ($fleteTotal / $pesoTotalFactura) : 0;

        // 2. RESUMEN DE ESTA ENTRADA (Lo que acabas de meter hoy)
        $resumenEntrada = DB::table('piezas')
            ->join('lotes', 'piezas.id_lote', '=', 'lotes.id_lote')
            ->where('lotes.id_compra', $idCompra)
            ->where('piezas.id_empresa', $idEmpresa)
            ->select(
                'piezas.id_producto',
                DB::raw('SUM(piezas.cantidad_metros_actual) as metros_nuevos'),
                DB::raw('SUM(piezas.peso_libras_actual) as peso_nuevo')
            )
            ->groupBy('piezas.id_producto')->get();

        foreach ($resumenEntrada as $ent) {
            $p = DB::table('productos')->where('id_producto', $ent->id_producto)->first();
            $fam = DB::table('compra_familia')
                ->where('id_compra', $idCompra)->where('id_familia', $p->id_familia)->first();

            if (!$fam) continue;

            // --- LÓGICA DE COSTO BASADA EN METROS (Para evitar el error del peso) ---

            // A. Dinero que ya tenías en bodega
            $metrosViejos = $p->stock_metros - $ent->metros_nuevos;
            $valorBodegaVieja = ($metrosViejos > 0) ? ($metrosViejos * $p->precio_unitario_bodega) : 0;

            // B. Dinero de la entrada nueva (Merca + Flete)
            $precioMercaMetro = $fam->importe_total_dolares / ($fam->peso_total_libras * ($p->peso_lb_mts ?: 1));
            // Simplificado: Usamos el importe de familia prorrateado por peso de las piezas
            $valorMercaNueva = ($fam->importe_total_dolares / ($fam->peso_total_libras ?: 1)) * $ent->peso_nuevo;
            $valorFleteNuevo = $ent->peso_nuevo * $gastoPorLibra;
            $valorTotalEntrada = $valorMercaNueva + $valorFleteNuevo;

            // C. NUEVO COSTO PROMEDIO
            $nuevoCosto = ($valorBodegaVieja + $valorTotalEntrada) / ($p->stock_metros ?: 1);

            // D. ACTUALIZACIÓN FINAL (Calculamos el peso total real para que no se infle más)
            $pesoTotalReal = $p->stock_metros * ($p->peso_lb_mts ?: 1);

            DB::table('productos')->where('id_producto', $p->id_producto)
                ->update([
                    'precio_unitario_bodega' => round($nuevoCosto, 4),
                    'peso_total_libras' => $pesoTotalReal // <--- Aquí corregimos el peso fantasma
                ]);
        }

        DB::table('compras')->where('id_compra', $idCompra)->update(['nueva_compra' => 0]);
        DB::commit();
        return response()->json(['status' => 'success', 'message' => '¡Costos y Pesos corregidos!']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
}
