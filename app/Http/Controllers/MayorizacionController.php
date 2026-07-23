<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MayorizacionController extends Controller
{
    public function index()
    {
        return view('mayorizacion.index');
    }

    public function generar(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2000|max:2100',
            'mes'  => 'required|integer|between(1,12)',
        ]);

        $empresa = session('idEmpresa');
        $anio    = $request->anio;
        $mes     = $request->mes;

        // ⭐ AQUÍ SE LLAMA EL PROCEDIMIENTO MYSQL ⭐
        DB::statement("CALL generar_saldos_mensuales($empresa, $anio, $mes)");

        return back()->with('msg', "Mayorización generada para $mes/$anio");
    }
}
