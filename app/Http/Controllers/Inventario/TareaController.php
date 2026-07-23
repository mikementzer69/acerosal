<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Tarea;
use App\Models\Inventario\Modulo;

class TareaController extends Controller
{
    /* =====================================
       LISTA
    ======================================*/
    public function index(Request $request)
    {
        $filtroNombre = $request->get('nombre', '');

        $tareas = Tarea::where('inactivo', 0)
            ->when($filtroNombre !== '', function ($q) use ($filtroNombre) {
                $q->where('nombre', 'LIKE', "%{$filtroNombre}%");
            })
            ->orderBy('orden')
            ->paginate(10);

        return view('inventario.tareas.index', [
            'tareas'       => $tareas,
            'filtroNombre' => $filtroNombre
        ]);
    }

    /* =====================================
       CREAR
    ======================================*/
    public function crear()
    {
        $modulos = Modulo::where('inactivo', 0)->orderBy('nombre')->get();
        return view('inventario.tareas.crear', compact('modulos'));
    }

    /* =====================================
       INSERTAR
    ======================================*/
    public function insertar(Request $request)
    {
        $request->validate([
            'id_modulo'   => 'required|integer',
            'nombre'      => 'required|string|max:45',
            'descripcion' => 'nullable|string|max:45',
            'ruta'        => 'nullable|string|max:100',
            'icono'       => 'nullable|string|max:45',
            'orden'       => 'nullable|integer',
            'visible'     => 'nullable|boolean',
        ]);

        Tarea::create([
            'id_modulo'   => $request->id_modulo,
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'ruta'        => $request->ruta,
            'icono'       => $request->icono,
            'orden'       => $request->orden ?? 0,
            'visible'     => $request->visible ?? 1,
            'inactivo'    => 0
        ]);

        return redirect()->route('tarea.lista')
                         ->with('msg', 'Tarea creada correctamente.');
    }

    /* =====================================
       EDITAR
    ======================================*/
    public function editar($id)
    {
        $tarea = Tarea::findOrFail($id);
        $modulos = Modulo::where('inactivo', 0)->orderBy('nombre')->get();

        return view('inventario.tareas.editar', compact('tarea', 'modulos'));
    }

    /* =====================================
       ACTUALIZAR
    ======================================*/
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'id_modulo'   => 'required|integer',
            'nombre'      => 'required|string|max:45',
            'descripcion' => 'nullable|string|max:45',
            'ruta'        => 'nullable|string|max:100',
            'icono'       => 'nullable|string|max:45',
            'orden'       => 'nullable|integer',
            'visible'     => 'nullable|boolean',
        ]);

        $tarea = Tarea::findOrFail($id);

        $tarea->update([
            'id_modulo'   => $request->id_modulo,
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'ruta'        => $request->ruta,
            'icono'       => $request->icono,
            'orden'       => $request->orden ?? 0,
            'visible'     => $request->visible ?? 1
        ]);

        return redirect()->route('tarea.lista')
                         ->with('msg', 'Tarea actualizada correctamente.');
    }

    /* =====================================
       ELIMINAR (LÓGICO)
    ======================================*/
    public function eliminar($id)
    {
        $tarea = Tarea::findOrFail($id);

        $tarea->update([
            'inactivo' => 1
        ]);

        return redirect()->route('tarea.lista')
                         ->with('msg', 'Tarea eliminada.');
    }
}


