<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Rol;
use Psy\Command\WhereamiCommand;

class RolController extends Controller
{
    /* =========================
     * LISTADO
     * ========================= */
    public function index(Request $request)
    {
        $nombre = $request->get('nombre', '');

        $roles = Rol::where('inactivo', 0) //
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('name', 'like', "%{$nombre}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('roles.index', [
            'roles'       => $roles,
            'filtroNombre'=> $nombre
        ]);
    }

    /* =========================
     * FORMULARIO CREAR
     * ========================= */
    public function create()
    {
        return view('roles.create');
    }

    /* =========================
     * GUARDAR
     * ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,NULL,id_roles',
            'guard_name' => 'nullable|string|max:255'
        ]);

        Rol::create([
            'name'      => $request->name,
            'guard_name' => $request->guard_name,
            'inactivo' => 0 // Aunque no exista en la tabla, lo dejamos por si acaso
        ]);

        return redirect()
            ->route('roles.index')
            ->with('msg', 'Rol creado correctamente.');
    }

    /* =========================
     * FORMULARIO EDITAR
     * ========================= */
    public function edit($id)
    {
        $rol = Rol::findOrFail($id);

        return view('roles.edit', compact('rol'));
    }

    /* =========================
     * ACTUALIZAR
     * ========================= */
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:100|unique:roles,name,' . $id . ',id_rol',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $rol->update([
            'name'      => $request->name,
            'guard_name' => $request->guard_name
        ]);

        return redirect()
            ->route('roles.index')
            ->with('msg', 'Rol actualizado correctamente.');
    }

    /* =========================
     * ELIMINAR (LÓGICO)
     * ========================= */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        $rol->update([
            'inactivo' => 1
        ]);

        return redirect()
            ->route('roles.index')
            ->with('msg', 'Rol eliminado correctamente.');
    }
}
