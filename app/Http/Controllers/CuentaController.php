<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use Illuminate\Http\Request;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $codigo = $request->input('codigo');
        $nombre = $request->input('nombre');
        $tipo   = $request->input('tipo');

        $query = Cuenta::query();

        if ($codigo) {
            $query->where('codigo', 'like', "%{$codigo}%");
        }

        if ($nombre) {
            $query->where('nombre', 'like', "%{$nombre}%");
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $cuentas = $query->orderBy('codigo')->paginate(15);

        return view('cuentas.index', compact('cuentas', 'codigo', 'nombre', 'tipo'));
    }

    public function create()
    {
        $cuentas_padre = Cuenta::orderBy('codigo')->get();
        return view('cuentas.create', compact('cuentas_padre'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo'    => 'required|max:20|unique:cuentas,codigo',
            'nombre'    => 'required',
            'tipo'      => 'required|in:activo,pasivo,patrimonio,ingreso,gasto',
            'parent_id' => 'nullable|exists:cuentas,id',
        ]);

        // checkboxes
        $validated['es_movimiento'] = $request->has('es_movimiento') ? 1 : 0;
        $validated['activo']        = $request->has('activo') ? 1 : 0;
        $validated['idEmpresa'] = session('idEmpresa');
        Cuenta::create($validated);

        Cuenta::create($validated);

        return redirect()
            ->route('cuentas.index')
            ->with('msg', 'Cuenta creada con éxito.');
    }

    public function edit(Cuenta $cuenta)
    {
        $cuentas_padre = Cuenta::where('id', '!=', $cuenta->id)
                               ->orderBy('codigo')
                               ->get();

        return view('cuentas.edit', compact('cuenta', 'cuentas_padre'));
    }

    public function update(Request $request, Cuenta $cuenta)
    {
        $validated = $request->validate([
            'codigo'        => 'required|max:20|unique:cuentas,codigo,' . $cuenta->id,
            'nombre'        => 'required',
            'tipo'          => 'required|in:activo,pasivo,patrimonio,ingreso,gasto',
            'parent_id'     => 'nullable|exists:cuentas,id',
        ]);

        $validated['es_movimiento'] = $request->has('es_movimiento') ? 1 : 0;
        $validated['activo']        = $request->has('activo') ? 1 : 0;

        $cuenta->update($validated);

        return redirect()
            ->route('cuentas.index')
            ->with('msg', 'Cuenta actualizada correctamente.');
    }

    public function destroy(Cuenta $cuenta)
    {
        $cuenta->delete();

        return redirect()
            ->route('cuentas.index')
            ->with('msg', 'Cuenta eliminada.');
    }
}
