<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Costo;


class CostosController extends Controller
{

    /* Mostrar formulario para nuevo costo */
    public function crear()
    {
        return view('formularios.costos');
    }

    /* Insertar nuevo costo */
    public function insertar(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:255',
            'Descripcion' => 'nullable|string'
        ]);

        Costo::create([
            'Nombre' => $request->Nombre,
            'Descripcion' => $request->Descripcion,
            'Activo' => 1
        ]);

        return redirect()->route('costo.lista')
            ->with('msg', '✅ Costo creado correctamente.');
    }

    /* Mostrar lista */
    public function lista(Request $request)
    {
        $filtroNombre = $request->get('nombre', '');

        $costos = Costo::where('Activo', 1)
            ->when($filtroNombre, fn($q) =>
                $q->where('Nombre', 'LIKE', "%$filtroNombre%")
            )
            ->paginate(10);

        return view('costos.lista', compact('costos', 'filtroNombre'));
    }

    /* Editar */
    public function editar($id)
    {
        $costo = Costo::findOrFail($id);
        return view('costos.editar', compact('costo'));
    }

    /* Actualizar */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'Nombre' => 'required|string|max:255'
        ]);

        $costo = Costo::findOrFail($id);

        $costo->update([
            'Nombre' => $request->Nombre,
            'Descripcion' => $request->Descripcion
        ]);

        return redirect()->route('costo.lista')
            ->with('msg', '✅ Costo actualizado.');
    }

    /* Eliminar (borrado lógico) */
    public function eliminar($id)
    {
        $costo = Costo::findOrFail($id);

        $costo->update([
            'Activo' => 0
        ]);

        return redirect()->route('costo.lista')
            ->with('msg', '🗑️ Costo eliminado.');
    }
}
