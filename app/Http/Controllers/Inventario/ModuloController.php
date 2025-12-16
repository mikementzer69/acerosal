<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Modulo;

class ModuloController extends Controller
{
    /* ================================
       LISTA
    ================================= */
    public function index(Request $request)
    {
        $filtroNombre = $request->get('nombre', '');

        $modulos = Modulo::buscarConPaginacion($filtroNombre, 10);

        return view('inventario.modulos.index', [
            'modulos'      => $modulos,
            'filtroNombre' => $filtroNombre,
        ]);
    }

    /* ================================
       CREAR
    ================================= */
    public function crear()
    {
        $padres = Modulo::obtenerActivos();
        return view('inventario.modulos.crear', compact('padres'));
    }

    /* ================================
       INSERTAR
    ================================= */
    public function insertar(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'id_modulopadre'  => 'nullable|integer',
        ]);

        Modulo::insertar($request->all());

        return redirect()->route('modulo.lista')
                         ->with('msg', 'Módulo creado correctamente.');
    }

    /* ================================
       EDITAR
    ================================= */
    public function editar($id)
    {
        $modulo = Modulo::findOrFail($id);
        $padres = Modulo::obtenerActivos();

        return view('inventario.modulos.editar', compact('modulo', 'padres'));
    }

    /* ================================
       ACTUALIZAR
    ================================= */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'id_modulopadre'  => 'nullable|integer',
        ]);

        Modulo::actualizarInline($id, $request->all());

        return redirect()->route('modulo.lista')
                         ->with('msg', 'Módulo actualizado correctamente.');
    }

    /* ================================
       ELIMINAR (LÓGICO)
    ================================= */
    public function eliminar($id)
    {
        Modulo::eliminarLogico($id);

        return redirect()->route('modulo.lista')
                         ->with('msg', 'Módulo eliminado.');
    }
}
