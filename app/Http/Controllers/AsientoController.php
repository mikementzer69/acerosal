<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\AsientoDetalle;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ContabilidadService;
use App\Models\CierreContable;
use Carbon\Carbon;

class AsientoController extends Controller

{
    public function index(Request $request)
    {
        $asientos = Asiento::orderBy('fecha', 'desc')->paginate(20);
        return view('asientos.index', compact('asientos'));
    }

    public function create()
    {
        $cuentas = Cuenta::orderBy('codigo')->get();
        return view('asientos.create', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'          => 'required|date',
            'descripcion'    => 'nullable|string',
            'cuenta_id.*'    => 'required|exists:cuentas,id',
            'debe.*'         => 'nullable|numeric|min:0',
            'haber.*'        => 'nullable|numeric|min:0',
        ]);

        // Validaciones de doble partida
        $total_debe  = array_sum($request->debe ?? []);
        $total_haber = array_sum($request->haber ?? []);

        if ($total_debe != $total_haber) {
            return back()
                ->withErrors(['msg' => 'El asiento no cuadra: el total del debe debe ser igual al total del haber.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $asiento = Asiento::create([
                'idEmpresa' => session('idEmpresa'),
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'total_debe' => $total_debe,
                'total_haber' => $total_haber,
                'activo' => 1
            ]);


      foreach ($request->cuenta_id as $i => $cuentaId) {

            AsientoDetalle::create([
                'idEmpresa' => session('idEmpresa'),
                'asiento_id' => $asiento->id,
                'cuenta_id' => $cuentaId,
                'descripcion' => $request->detalle[$i],
                'debe' => $request->debe[$i],
                'haber' => $request->haber[$i],
            ]);

        }


            DB::commit();

            return redirect()->route('asientos.index')
                ->with('msg', 'Asiento registrado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Error al registrar asiento: '.$e->getMessage()]);
        }
    }

public function show(Asiento $asiento)
{
    // Aseguramos que cargue detalles + la cuenta relacionada
    $asiento->load(['detalles.cuenta']);

    return view('asientos.show', compact('asiento'));
}

public function edit($id)
{
    $asiento = Asiento::with('detalles')->where('id', $id)
                       ->where('idEmpresa', session('idEmpresa'))
                       ->firstOrFail();

    $cuentas = Cuenta::where('idEmpresa', session('idEmpresa'))
                     ->where('es_movimiento', 1)
                     ->orderBy('codigo')
                     ->get();

    return view('asientos.edit', compact('asiento', 'cuentas'));
}

public function update(Request $request, $id)
{
    $asiento = Asiento::where('id', $id)
                      ->where('idEmpresa', session('idEmpresa'))
                      ->firstOrFail();

    // Validar
    $request->validate([
        'fecha'        => 'required|date',
        'descripcion'  => 'required|string|max:255',
        'cuenta_id.*'  => 'required|exists:cuentas,id',
        'detalle.*'    => 'nullable|string',
        'debe.*'       => 'nullable|numeric|min:0',
        'haber.*'      => 'nullable|numeric|min:0',
    ]);

    // Recalcular totales
    $totalDebe = array_sum($request->debe ?? []);
    $totalHaber = array_sum($request->haber ?? []);

    // Actualizar asiento
    $asiento->update([
        'fecha'        => $request->fecha,
        'descripcion'  => $request->descripcion,
        'total_debe'   => $totalDebe,
        'total_haber'  => $totalHaber,
    ]);

    // Borrar detalles anteriores
    AsientoDetalle::where('asiento_id', $asiento->id)->delete();

    // Insertar nuevas líneas
    foreach ($request->cuenta_id as $i => $cuentaId) {
        AsientoDetalle::create([
            'idEmpresa'   => session('idEmpresa'),
            'asiento_id'  => $asiento->id,
            'cuenta_id'   => $cuentaId,
            'descripcion' => $request->detalle[$i] ?? '',
            'debe'        => $request->debe[$i] ?? 0,
            'haber'       => $request->haber[$i] ?? 0,
        ]);
    }

    return redirect()
        ->route('asientos.index')
        ->with('msg', 'Asiento actualizado correctamente.');
}


}
