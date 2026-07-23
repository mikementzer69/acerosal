<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Inventario\CierreDiarioLog; // Modelo para el log de cierres diarios
class CierreInventarioController extends Controller
{
    /* ==========================
       VISTA CIERRE DIARIO
    ========================== */
 public function vistaCierreDiario()
{
    $usuario = session('usuario');
    $idEmpresa = $usuario->id_empresa ?? 1;
    $nombreEmpresa = $usuario->nombre_empresa ?? 'ACEROSAL';

    $ultimoCierre = CierreDiarioLog::where('id_empresa', $idEmpresa)
                    ->where('estado', 'OK')
                    ->max('fecha');

    // 💡 LA CLAVE: Si venimos de un intento de cierre, usamos esa fecha.
    // Si no, sugerimos el siguiente día.
    $fechaSugerida = session('fecha_reintentada') ?? ($ultimoCierre
        ? Carbon::parse($ultimoCierre)->addDay()->format('Y-m-d')
        : date('Y-m-d'));

    $historial = CierreDiarioLog::where('id_empresa', $idEmpresa)
                    ->orderBy('fecha', 'desc')
                    ->take(7)
                    ->get();

    return view('inventario.cierre.diario', compact('nombreEmpresa', 'idEmpresa', 'fechaSugerida', 'historial'));
}

    /* ==========================
       EJECUTAR CIERRE DIARIO
    ========================== */
  public function ejecutarCierreDiario(Request $request)
{
    $idEmpresa = $request->id_empresa;
    $fechaSolicitada = $request->fecha;
    $confirmado = $request->input('confirmado') == '1';

    $ultimoCierre = CierreDiarioLog::where('id_empresa', $idEmpresa)
                    ->where('estado', 'OK')
                    ->max('fecha');

    if ($ultimoCierre) {
        if ($fechaSolicitada < $ultimoCierre) {
            return back()->with('error', "No puedes cerrar fechas anteriores al $ultimoCierre.");
        }

        if ($fechaSolicitada == $ultimoCierre && !$confirmado) {
            // Mandamos 'fecha_reintentada' para que la vista no la cambie al 10/02
            return back()
                ->with('confirmar_sobreescritura', true)
                ->with('fecha_reintentada', $fechaSolicitada);
        }
    }

    try {
        DB::select("CALL cierre_inventario_diario(?, ?)", [$idEmpresa, $fechaSolicitada]);

        // Usamos tu variable de sesión real: idUsuario
        $idAutor = session('idUsuario') ?? 'Sistema';

        CierreDiarioLog::updateOrCreate(
            ['id_empresa' => $idEmpresa, 'fecha' => $fechaSolicitada],
            [
                'registros_generados' => $resultado[0]->total ?? 0,
                'ejecutado_por' => $idAutor,
                'estado' => 'OK'
            ]
        );

        return redirect()->route('inventario.cierre.diario')->with('msg', '✅ Cierre del ' . $fechaSolicitada . ' procesado con éxito.');
    } catch (\Exception $e) {
        return back()->with('error', 'Error: ' . $e->getMessage());
    }
}



    /* ==========================
       VISTA CIERRE MENSUAL
    ========================== */
    public function vistaCierreMensual()
    {
        return view('inventario.cierre.mensual');
    }

public function reconstruirInventario(Request $request)
{
    // 1. Validar la fecha (si no viene, usamos una fecha muy antigua)
    $fechaInicio = $request->fecha_inicio ?? '2026-01-01';
    $idEmpresa = session('idEmpresa');

    DB::beginTransaction();
    try {
        // PASO A: RECONSTRUIR PIEZAS (La base de todo)
        // Obtenemos los saldos reales desde el Kardex
        $saldosKardex = DB::table('movimientos_inventario as m')
            ->select(
                'm.id_pieza',
                DB::raw("SUM(CASE
                    WHEN m.tipo IN ('entrada', 'ajuste_entrada', 'reingreso_por_corte', 'inicial') THEN m.cantidad
                    WHEN m.tipo = 'salida' THEN (m.cantidad_total_retirada * -1)
                    WHEN m.tipo = 'ajuste_salida' THEN (m.cantidad * -1)
                    ELSE 0 END) as metros"),
                DB::raw("SUM(CASE
                    WHEN m.tipo IN ('entrada', 'ajuste_entrada', 'reingreso_por_corte', 'inicial') THEN m.peso
                    WHEN m.tipo IN ('salida', 'ajuste_salida') THEN (m.peso * -1)
                    ELSE 0 END) as libras")
            )
            ->where('m.id_empresa', $idEmpresa)
            ->where('m.eliminado', 0)
            ->where('m.fecha', '>=', $fechaInicio)
            ->groupBy('m.id_pieza')
            ->get();

        foreach ($saldosKardex as $sk) {
            DB::table('piezas')
                ->where('id_pieza', $sk->id_pieza)
                ->where('id_empresa', $idEmpresa)
                ->update([
                    'cantidad_metros_actual' => $sk->metros,
                    'peso_libras_actual'     => $sk->libras,
                    'updated_at'             => now()
                ]);
        }

        // PASO B: SINCRONIZAR LOTES (Sumando sus piezas actualizadas)
        $lotes = DB::table('piezas')
            ->select('id_lote',
                DB::raw("SUM(cantidad_metros_actual) as m"),
                DB::raw("SUM(peso_libras_actual) as lb"),
                DB::raw("COUNT(CASE WHEN cantidad_metros_actual > 0.001 THEN 1 END) as pz")
            )
            ->where('id_empresa', $idEmpresa)
            ->groupBy('id_lote')
            ->get();

        foreach ($lotes as $l) {
            DB::table('lotes')
                ->where('id_lote', $l->id_lote)
                ->update([
                    'cantidad_total_metros' => $l->m,
                    'peso_total_libras'     => $l->lb,
                    'total_piezas'          => $l->pz,
                    'updated_at'            => now()
                ]);
        }

        // PASO C: SINCRONIZAR PRODUCTOS (Sumando sus lotes actualizados)
        $productos = DB::table('lotes')
            ->select('id_producto',
                DB::raw("SUM(cantidad_total_metros) as m"),
                DB::raw("SUM(peso_total_libras) as lb")
            )
            ->where('id_empresa', $idEmpresa)
            ->where('eliminado', 0)
            ->groupBy('id_producto')
            ->get();

        foreach ($productos as $p) {
            DB::table('productos')
                ->where('id_producto', $p->id_producto)
                ->update([
                    'stock_metros'      => $p->m,
                    'peso_total_libras' => $p->lb,
                    'updated_at'        => now()
                ]);
        }

        DB::commit();
        return response()->json([
            'status'  => 'success',
            'message' => '¡Inventario reconstruido! Piezas, lotes y productos sincronizados.'
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'status'  => 'error',
            'message' => 'Error en la reconstrucción: ' . $e->getMessage()
        ], 500);
    }
}

}

