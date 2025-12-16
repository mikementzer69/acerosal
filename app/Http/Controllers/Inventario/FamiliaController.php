<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Familia;

class FamiliaController extends Controller
{
    /* ===========================
       LISTA (index)
    ============================ */
    public function index(Request $request)
    {
        $filtroNombre = $request->get('nombre', '');

        $familias = Familia::buscarConPaginacion($filtroNombre, 10);

        return view('inventario.familia.index', [
            'familias'      => $familias,
            'filtroNombre'  => $filtroNombre,
        ]);
    }


    /* ===========================
       CREAR
    ============================ */
    public function crear()
    {
        return view('inventario.familia.crear');
    }


    /* ===========================
       GUARDAR
    ============================ */
    public function insertar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Familia::insertar([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('familia.lista')
                         ->with('msg', '✅ Familia creada correctamente.');
    }


    /* ===========================
       EDITAR
    ============================ */
    public function editar($id)
    {
        $familia = Familia::findOrFail($id);

        return view('inventario.familia.editar', compact('familia'));
    }


    /* ===========================
       ACTUALIZAR
    ============================ */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Familia::actualizarInline($id, [
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('familia.lista')
                         ->with('msg', '✅ Familia actualizada correctamente.');
    }


    /* ===========================
       ELIMINAR (lógico)
    ============================ */
    public function eliminar($id)
    {
        Familia::eliminarLogico($id);

        return redirect()->route('familia.lista')
                         ->with('msg', '🗑️ Familia eliminada.');
    }
}
