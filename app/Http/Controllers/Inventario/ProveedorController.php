<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Proveedor;

class ProveedorController extends Controller
{
    /* =========================
     * LISTADO
     * ========================= */
    public function index(Request $request)
    {
        $query = Proveedor::where('eliminado', 0);

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('origen')) {
            $query->where('origen', 'like', '%' . $request->origen . '%');
        }

        $proveedores = $query
            ->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    /* =========================
     * FORMULARIO CREAR
     * ========================= */
    public function create()
    {
        return view('proveedores.create');
    }

    /* =========================
     * GUARDAR
     * ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'origen'    => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        Proveedor::create([
            'nombre'    => $request->nombre,
            'origen'    => $request->origen,
            'direccion' => $request->direccion,
            'eliminado' => 0,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('msg', 'Proveedor creado correctamente.');
    }

    /* =========================
     * FORMULARIO EDITAR
     * ========================= */
    public function edit($id)
    {
        $proveedor = Proveedor::where('id_proveedor', $id)->firstOrFail();

        return view('proveedores.edit', compact('proveedor'));
    }

    /* =========================
     * ACTUALIZAR
     * ========================= */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::where('id_proveedor', $id)->firstOrFail();

        $request->validate([
            'nombre'    => 'required|string|max:100',
            'origen'    => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        $proveedor->update([
            'nombre'    => $request->nombre,
            'origen'    => $request->origen,
            'direccion' => $request->direccion,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('msg', 'Proveedor actualizado correctamente.');
    }

    /* =========================
     * ELIMINAR (LÓGICO)
     * ========================= */
    public function destroy($id)
    {
        $proveedor = Proveedor::where('id_proveedor', $id)->firstOrFail();

        $proveedor->update([
            'eliminado' => 1,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('msg', 'Proveedor eliminado correctamente.');
    }
}
