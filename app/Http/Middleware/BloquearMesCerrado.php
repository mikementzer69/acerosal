<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Asiento;
use App\Models\MesCerrado;

class BloquearMesCerrado
{
    public function handle(Request $request, Closure $next)
    {

        if ($request->route('asiento')) {

            $asiento = Asiento::find($request->route('asiento'));

            if ($asiento) {
                $empresa = session('idEmpresa');

                $cerrado = MesCerrado::where('idEmpresa', $empresa)
                    ->where('anio', date('Y', strtotime($asiento->fecha)))
                    ->where('mes', date('m', strtotime($asiento->fecha)))
                    ->exists();

                if ($cerrado) {
                    return redirect()->route('asientos.index')
                        ->with('msg', '❌ No puede editar o eliminar un asiento en un mes cerrado.');
                }
            }
        }

        return $next($request);
    }
}
