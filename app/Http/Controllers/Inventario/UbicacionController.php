<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UbicacionController extends Controller
{
    /**
     * LISTADO CON BUSCADOR
     */
    public function index(Request $request)
    {
        // 1. Capturamos el filtro del buscador
        $filtroNombre = $request->get('nombre');

        // 2. Consulta filtrada (El Global Scope del modelo ya filtra por id_empresa)
        $ubicaciones = Ubicacion::where('nombre', 'LIKE', "%$filtroNombre%")
            ->orderBy('id_ubicacion', 'desc')
            ->paginate(10);

        // 3. Retornamos la vista con las variables necesarias
        return view('inventario.ubicaciones.index', compact('ubicaciones', 'filtroNombre'));
    }

    /**
     * FORMULARIO: NUEVA UBICACIÓN
     */
    public function create()
    {
        return view('inventario.ubicaciones.create');
    }

    /**
     * GUARDAR EN BASE DE DATOS
     */
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'nombre' => 'required|max:100',
        ]);

        try {
            Ubicacion::create([
                'id_empresa'  => session('idEmpresa'),
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
                'inactivo'    => 0
            ]);

            return redirect()->route('inventario.ubicaciones.index')
                             ->with('success', '✅ Ubicación guardada exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error al crear ubicación: " . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo guardar la ubicación.'])->withInput();
        }
    }

    /**
     * FORMULARIO: EDITAR UBICACIÓN
     */
    public function edit($id)
    {
        // FindOrFail asegura que si no existe o es de otra empresa (vía Global Scope), de un 404
        $ubicacion = Ubicacion::findOrFail($id);

        return view('inventario.ubicaciones.edit', compact('ubicacion'));
    }

    /**
     * ACTUALIZAR CAMBIOS
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|max:100',
            'inactivo' => 'required|boolean'
        ]);

        try {
            $ubicacion = Ubicacion::findOrFail($id);

            $ubicacion->update([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
                'inactivo'    => $request->inactivo
            ]);

            return redirect()->route('inventario.ubicaciones.index')
                             ->with('success', '✅ Ubicación actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error("Error al actualizar ubicación {$id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar los datos.'])->withInput();
        }
    }

    /**
     * INACTIVAR (Borrado lógico)
     */
    public function destroy($id)
    {
        try {
            $ubicacion = Ubicacion::findOrFail($id);

            // En lugar de borrar, inactivamos para no romper el histórico de stock
            $ubicacion->update(['inactivo' => 1]);

            return redirect()->route('inventario.ubicaciones.index')
                             ->with('success', '🚫 Ubicación marcada como inactiva.');

        } catch (\Exception $e) {
            Log::error("Error al inactivar ubicación {$id}: " . $e->getMessage());
            return back()->with('error', 'No se pudo realizar la acción.');
        }
    }
}
