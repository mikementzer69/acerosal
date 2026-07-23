<?php

namespace App\Http\Controllers\Inventario;

use Illuminate\Http\Request;
use App\Models\Inventario\Empresa;
use App\Http\Controllers\Controller;

class EmpresaController extends Controller
{
    /* Mostrar formulario */
    public function crear()
    {
        return view('inventario.empresa.crear');
    }

    /* Insertar nueva empresa */
    public function insertar(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'nit'             => 'nullable|string|max:100',
            'nrc'             => 'nullable|string|max:100',
            'razon_social'    => 'nullable|string|max:255',
            'direccion'       => 'nullable|string',
            'telefono'        => 'nullable|string|max:50',
            'correo_contacto' => 'nullable|email|max:255',
        ]);

        Empresa::create($request->all());

        return redirect()->route('empresa.lista')
            ->with('msg', '✅ Empresa creada correctamente.');
    }

    /* Listar empresas */
    public function lista(Request $request)
    {
        // Capturar filtros
        $nombre   = $request->input('nombre');
        $nit      = $request->input('nit');
        $nrc      = $request->input('nrc');
        $correo   = $request->input('correo');
        $telefono = $request->input('telefono');

        // Consulta dinámica con columnas MINÚSCULAS
        $empresas = Empresa::whereNull('deleted_at')
            ->when($nombre, fn($q) => $q->where('nombre', 'LIKE', "%{$nombre}%"))
            ->when($nit, fn($q) => $q->where('nit', 'LIKE', "%{$nit}%"))
            ->when($nrc, fn($q) => $q->where('nrc', 'LIKE', "%{$nrc}%"))
            ->when($correo, fn($q) => $q->where('correo_contacto', 'LIKE', "%{$correo}%"))
            ->when($telefono, fn($q) => $q->where('telefono', 'LIKE', "%{$telefono}%"))
            ->orderBy('nombre')
            ->paginate(10);

        $empresas->appends($request->all());

        return view('inventario.empresa.index', compact(
            'empresas',
            'nombre',
            'nit',
            'nrc',
            'correo',
            'telefono'
        ));
    }


    /* Mostrar formulario de edición */
    public function editar($id)
    {
        $empresa = Empresa::findOrFail($id);
        return view('inventario.empresa.editar', compact('empresa'));
    }


    /* Actualizar datos */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'nit'             => 'nullable|string|max:100',
            'nrc'             => 'nullable|string|max:100',
            'razon_social'    => 'nullable|string|max:255',
            'direccion'       => 'nullable|string',
            'telefono'        => 'nullable|string|max:50',
            'correo_contacto' => 'nullable|email|max:255',
        ]);

        $empresa = Empresa::findOrFail($id);
        $empresa->update($request->all());

        return redirect()->route('empresa.lista')
            ->with('msg', '✅ Empresa actualizada correctamente.');
    }


    /* Eliminación lógica */
    public function eliminar($id)
    {
        $empresa = Empresa::findOrFail($id);

        $empresa->update([
            'deleted_at' => now()
        ]);

        return redirect()->route('empresa.lista')
            ->with('msg', '🗑️ Empresa eliminada.');
    }
}
