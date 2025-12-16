<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MesCerrado;
use Illuminate\Support\Facades\DB;

class CierreContableController extends Controller
{
    public function index()
    {
        return view('cierre.index');
    }

    public function cerrarMes(Request $request)
    {
        $empresa = session('idEmpresa');
        $anio = $request->anio;
        $mes = $request->mes;

        // Validar si ya está cerrado
        if (MesCerrado::where('idEmpresa', $empresa)->where('anio', $anio)->where('mes', $mes)->exists()) {
            return back()->with('msg', '⚠ Este mes ya está cerrado.');
        }

        // Ejecutar el procedimiento de mayorización
        DB::statement("CALL generar_saldos_mensuales($anio, $mes, $empresa)");

        // Guardar cierre
        MesCerrado::create([
            'idEmpresa' => $empresa,
            'anio'      => $anio,
            'mes'       => $mes,
            'cerrado_en'=> now(),
        ]);

        return back()->with('msg', '✔ Mes cerrado correctamente.');
    }
}
