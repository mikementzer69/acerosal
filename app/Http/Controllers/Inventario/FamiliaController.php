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
        $filtroNombre = $request->input('nombre', '');

        $familias = Familia::buscarConPaginacion($filtroNombre, 10);

        return view('inventario.familia.index', compact('familias', 'filtroNombre'));
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
            'codigo' => 'required|unique:familias,codigo',
            'nombre'        => 'required|string|max:255',
            'descripcion'   => 'nullable|string',
            'detalle_color' => 'nullable|string|max:20',
            'ubicacion'    => 'nullable|string|max:20',
        ]);

        Familia::insertar([
            'codigo'        => $request->codigo,
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'detalle_color' => $request->detalle_color,
            'ubicacion'     => $request->ubicacion,
        ]);

        return redirect()->route('familia.lista')
                         ->with('msg', 'Calidad creada correctamente.');
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
        'codigo' => 'required|string|max:50|unique:familias,codigo,' . $id . ',id_familia',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'detalle_color' => 'nullable|string|max:20',
        'ubicacion' => 'nullable|string|max:20',
    ]);


        Familia::actualizarInline($id, [
            'codigo'        => $request->codigo,
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'detalle_color' => $request->detalle_color,
            'ubicacion'     => $request->ubicacion,
        ]);

        return redirect()->route('familia.lista')
                         ->with('msg', 'Calidad actualizada correctamente.');
    }


    /* ===========================
       ELIMINAR (lógico)
    ============================ */
    public function eliminar($id)
    {
        Familia::eliminarLogico($id);

        return redirect()->route('familia.lista')
                         ->with('msg', 'Calidad eliminada.');
    }
}
