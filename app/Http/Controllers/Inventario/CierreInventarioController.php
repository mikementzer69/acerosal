<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CierreInventarioController extends Controller
{
    /* ==========================
       VISTA CIERRE DIARIO
    ========================== */
    public function vistaCierreDiario()
    {
        return view('inventario.cierre.diario');
    }

    /* ==========================
       EJECUTAR CIERRE DIARIO
    ========================== */
    public function ejecutarCierreDiario(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|integer',
            'fecha'      => 'required|date'
        ]);

        $empresa = $request->id_empresa;
        $fecha = $request->fecha;

        try {
            $resultado = DB::select("CALL cierre_inventario_diario(?, ?)", [
                $empresa,
                $fecha
            ]);

            DB::table('cierre_diario_log')->insert([
                'id_empresa'        => $empresa,
                'fecha'             => $fecha,
                'registros_generados' => $resultado[0]->total ?? 0,
               'ejecutado_por' => session('usuario')->usuario ?? 'sistema',
                'estado'            => 'OK'
            ]);

            return back()->with('msg', 'Cierre diario ejecutado correctamente.');
        }
        catch (\Exception $e) {

            DB::table('cierre_diario_log')->insert([
                'id_empresa'        => $empresa,
                'fecha'             => $fecha,
                'registros_generados' => 0,
                'ejecutado_por' => session('usuario')->usuario ?? 'sistema',
                'estado'            => 'ERROR'
            ]);

            return back()->with('error', 'Error al ejecutar el cierre: ' . $e->getMessage());
        }
    }


    /* ==========================
       VISTA CIERRE MENSUAL
    ========================== */
    public function vistaCierreMensual()
    {
        return view('inventario.cierre.mensual');
    }


    /* ==========================
       EJECUTAR CIERRE MENSUAL
    ========================== */
    public function ejecutarCierreMensual(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|integer',
            'mes'        => 'required|integer|min:1|max:12',
            'anio'       => 'required|integer|min:2020'
        ]);

        $empresa = $request->id_empresa;
        $mes     = $request->mes;
        $anio    = $request->anio;

        try {
            $resultado = DB::select("CALL cierre_inventario_mensual(?, ?, ?)", [
                $empresa,
                $anio,
                $mes
            ]);

            DB::table('cierre_mensual_log')->insert([
                'id_empresa'        => $empresa,
                'anio'              => $anio,
                'mes'               => $mes,
                'registros_generados' => $resultado[0]->total ?? 0,
                'ejecutado_por' => session('usuario')->usuario ?? 'sistema',
                'estado'            => 'OK'
            ]);

            return back()->with('msg', 'Cierre mensual generado correctamente.');
        }
        catch (\Exception $e) {

            DB::table('cierre_mensual_log')->insert([
                'id_empresa'    => $empresa,
                'anio'          => $anio,
                'mes'           => $mes,
                'registros_generados' => 0,
                'ejecutado_por' => session('usuario')->usuario ?? 'sistema',
                'estado'        => 'ERROR'
            ]);

            return back()->with('error', 'Error al ejecutar cierre mensual: ' . $e->getMessage());
        }
    }
}

