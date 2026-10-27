<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        // Normalizar comas a puntos decimales si el usuario escribió con coma
        if ($request->has('precio_lista')) {
            $request->merge(['precio_lista' => str_replace(',', '.', trim($request->precio_lista))]);
        }
        if ($request->has('precio_especial')) {
            $request->merge(['precio_especial' => str_replace(',', '.', trim($request->precio_especial))]);
        }

        $request->validate([
            'codigo'            => ['required', 'string', 'max:50', Rule::unique('familias', 'codigo')->where('inactivo', 0)],
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'detalle_color'     => 'nullable|string|max:50',
            'ubicacion'         => 'nullable|string|max:20',
            'precio_lista'      => 'nullable|numeric|min:0',
            'precio_especial'   => 'nullable|numeric|min:0',
            'precio_autorizado' => 'nullable|in:S,N',
        ]);

        try {
            Familia::insertar([
                'codigo'            => $request->codigo,
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'detalle_color'     => $request->detalle_color,
                'ubicacion'         => $request->ubicacion,
                'precio_lista'      => $request->precio_lista,
                'precio_especial'   => $request->precio_especial,
                'precio_autorizado' => $request->precio_autorizado ?? 'N',
            ]);

            return redirect()->route('familia.lista')
                             ->with('msg', 'Calidad creada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear la calidad: ' . $e->getMessage());
        }
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
        // Normalizar comas a puntos decimales si el usuario escribió con coma
        if ($request->has('precio_lista')) {
            $request->merge(['precio_lista' => str_replace(',', '.', trim($request->precio_lista))]);
        }
        if ($request->has('precio_especial')) {
            $request->merge(['precio_especial' => str_replace(',', '.', trim($request->precio_especial))]);
        }

        $request->validate([
            'codigo'            => ['required', 'string', 'max:50', Rule::unique('familias', 'codigo')->ignore($id, 'id_familia')->where('inactivo', 0)],
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'detalle_color'     => 'nullable|string|max:50',
            'ubicacion'         => 'nullable|string|max:20',
            'precio_lista'      => 'nullable|numeric|min:0',
            'precio_especial'   => 'nullable|numeric|min:0',
            'precio_autorizado' => 'nullable|in:S,N',
        ]);

        try {
            $familiaActual = Familia::findOrFail($id);
            $ubicacion = $request->has('ubicacion') ? $request->ubicacion : $familiaActual->ubicacion;

            Familia::actualizarInline($id, [
                'codigo'            => $request->codigo,
                'nombre'            => $request->nombre,
                'descripcion'       => $request->descripcion,
                'detalle_color'     => $request->detalle_color,
                'ubicacion'         => $ubicacion,
                'precio_lista'      => $request->precio_lista,
                'precio_especial'   => $request->precio_especial,
                'precio_autorizado' => $request->precio_autorizado ?? 'N',
            ]);

            return redirect()->route('familia.lista')
                             ->with('msg', 'Calidad actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar la calidad: ' . $e->getMessage());
        }
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
