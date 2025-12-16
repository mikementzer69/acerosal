<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Compra;
use App\Models\Inventario\CompraProducto;
use App\Models\Inventario\Lote;
use App\Models\Inventario\Pieza;
use App\Models\Inventario\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Mostrar formulario de inventario automático (solo compras nuevas)
     */
    public function automatico()
    {
        $comprasNuevas = Compra::where('nueva_compra', 1)
            ->where('eliminado', 0)
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('inventario.automatico', compact('comprasNuevas'));
    }

    /**
     * AJAX → Obtener detalle para JS y generar tarjetas
     */
    public function detalleCompra($id)
    {
        $compra = Compra::with(['proveedor', 'empresa'])->findOrFail($id);

        // si ya está procesada, denegar
        if ($compra->nueva_compra == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Esta compra ya fue procesada.',
            ]);
        }

        $detalle = CompraProducto::with('producto')
            ->where('id_compra', $id)
            ->where('eliminado', 0)
            ->get()
            ->map(function ($cp) {
                return [
                    'idProductos'            => $cp->id_producto,
                    'Codigo'                 => $cp->producto->codigo ?? '',
                    'Descripcion'            => $cp->producto->descripcion ?? '',
                    'Peso_Total_Libras'      => (float) $cp->peso_libra,
                    'Cantidad_Total_Metros'  => (float) $cp->cantidad,
                ];
            });

        return response()->json([
            'success' => true,
            'compra' => [
                'Numero_Factura'  => $compra->numero_factura,
                'Fecha_EmisionF'  => $compra->fecha_emision_factura,
                'Fecha_Ingreso'   => $compra->fecha_ingreso,
                'Proveedor'       => optional($compra->proveedor)->nombre,
                'Empresa'         => optional($compra->empresa)->nombre,
            ],
            'detalle' => $detalle,
        ]);
    }

    /**
     * Validar si un código de lote ya existe para el producto
     */
    public function verificarCodigoLote(Request $request)
    {
        $idProducto = (int) $request->query('idProducto');
        $codigo = trim($request->query('codigo', ''));

        if (!$idProducto || $codigo === '') {
            return response()->json(['existe' => false]);
        }

        $existe = Lote::where('id_producto', $idProducto)
            ->where('codigo', $codigo)
            ->where('eliminado', 0)
            ->exists();

        return response()->json(['existe' => $existe]);
    }

    /**
     * Guardar inventario automático (lotes + piezas + movimientos)
     */
    public function guardarAutomatico(Request $request)
    {
        $idCompra = (int) $request->input('idCompra');
        $lotes = json_decode($request->input('lotes', '[]'), true);
        $piezas = json_decode($request->input('piezas', '[]'), true);

        if ($idCompra <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Falta el ID de compra.',
            ]);
        }

        $compra = Compra::findOrFail($idCompra);

        // protección extra
        if ($compra->nueva_compra == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Esta compra ya fue procesada anteriormente.',
            ]);
        }

        if (empty($lotes)) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron lotes.',
            ]);
        }

        try {
            DB::beginTransaction();

            foreach ($lotes as $lote) {

                $idProd = (int) ($lote['Id_Productos'] ?? 0);
                $codigoLote = trim($lote['Codigo'] ?? '');
                $codigoProducto = trim($lote['Codigo_Producto'] ?? $lote['Codigo'] ?? '');

                if ($idProd <= 0 || $codigoLote === '') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos incompletos en uno de los lotes.',
                    ]);
                }

                // validar si el lote ya existe
                $existe = Lote::where('id_producto', $idProd)
                    ->where('codigo', $codigoLote)
                    ->where('eliminado', 0)
                    ->exists();

                if ($existe) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "El lote {$codigoLote} ya existe para este producto.",
                    ]);
                }

                // crear el lote
                $loteModel = Lote::create([
                    'id_producto'             => $idProd,
                    'codigo'                  => $codigoLote,
                    'fecha_ingreso'           => $lote['Fecha_Ingreso'],
                    'peso_total_libras'       => $lote['Peso_Total_Libras'],
                    'cantidad_total_metros'   => $lote['Cantidad_Total_Metros'],
                    'relacion_cantidad_peso'  => $lote['Relacion_Cantidad_Peso'],
                    'total_piezas'            => $lote['Total_Piezas'],
                    'eliminado'               => 0,
                ]);

                // procesar piezas asociadas a este lote
                if (!empty($piezas[$idProd])) {

                    $correlativo = 1;

                    foreach ($piezas[$idProd] as $pieza) {

                        // calcular pesos y valores
                        $metros = (float) $pieza['Cantidad_Metros_Inicial'];
                        $rel = (float) $lote['Relacion_Cantidad_Peso'];
                        $peso = $rel * $metros;

                        // generar código de pieza
                        $codigoPieza = $codigoProducto
                            . '-' . $codigoLote
                            . '-' . str_pad($correlativo, 3, '0', STR_PAD_LEFT);

                        $piezaModel = Pieza::create([
                            'id_producto'               => $idProd,
                            'id_lote'                   => $loteModel->id_lote,
                            'codigo'                    => $codigoPieza,
                            'peso_libras_inicial'       => $peso,
                            'cantidad_metros_inicial'   => $metros,
                            'peso_libras_actual'        => $peso,
                            'cantidad_metros_actual'    => $metros,
                            'peso_libras_recortados'    => 0,
                            'cantidad_metros_recortados'=> 0,
                            'retirado'                  => 0,
                            'finalizado'                => 0,
                            'eliminado'                 => 0,
                        ]);

                        // crear movimiento de inventario
                        MovimientoInventario::create([
                            'id_pieza'   => $piezaModel->id_pieza,
                            'id_corte'   => null,
                            'id_compra'  => $idCompra,
                            'origen'     => 'Compra',
                            'tipo'       => 'entrada',
                            'cantidad'   => $metros,
                            'peso'       => $peso,
                            'fecha'      => now(),
                            'id_usuario' => Auth::id() ?? 1,
                            'comentario' => "Ingreso por compra, nueva pieza {$codigoPieza}",
                            'eliminado'  => 0,
                        ]);

                        $correlativo++;
                    }

                    // actualizar total piezas
                    $loteModel->update(['total_piezas' => $correlativo - 1]);
                }
            }

            // marcar compra como procesada
            $compra->update(['nueva_compra' => 0]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventario guardado correctamente.',
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error inesperado: ' . $th->getMessage(),
            ]);
        }
    }
}