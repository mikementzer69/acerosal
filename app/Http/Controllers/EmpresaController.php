<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;

class EmpresaController extends Controller
{
    /* Mostrar formulario */
    public function crear()
    {
        return view('formularios.empresa');
    }

    /* Insertar nueva empresa */
    public function insertar(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:255',
            'NIT' => 'nullable|string|max:100',
            'NRC' => 'nullable|string|max:100',
            'Razon_Social' => 'nullable|string|max:255',
            'Direccion' => 'nullable|string',
            'Telefono' => 'nullable|string|max:50',
            'Correo_Contacto' => 'nullable|email|max:255',
        ]);

        Empresa::create($request->all());

        return redirect()->route('empresa.lista')
            ->with('msg', '✅ Empresa creada correctamente.');
    }

    /* Listar empresas */
public function lista(Request $request)
{
    // Capturar filtros
    $nombre = $request->input('nombre');
    $nit = $request->input('nit');
    $nrc = $request->input('nrc');
    $correo = $request->input('correo');
    $telefono = $request->input('telefono');

    // Construir consulta dinámica
    $empresas = Empresa::whereNull('deleted_at')
        ->when($nombre, fn($q) => $q->where('Nombre', 'LIKE', "%{$nombre}%"))
        ->when($nit, fn($q) => $q->where('NIT', 'LIKE', "%{$nit}%"))
        ->when($nrc, fn($q) => $q->where('NRC', 'LIKE', "%{$nrc}%"))
        ->when($correo, fn($q) => $q->where('Correo_Contacto', 'LIKE', "%{$correo}%"))
        ->when($telefono, fn($q) => $q->where('Telefono', 'LIKE', "%{$telefono}%"))
        ->orderBy('Nombre')
        ->paginate(10);

    // Mantener filtros al cambiar de página
    $empresas->appends($request->all());

    return view('listas.listaempresa', compact('empresas', 'nombre', 'nit', 'nrc', 'correo', 'telefono'));
}


    /* Actualizar */
   // Mostrar formulario con datos cargados
public function editar($id)
{
    $empresa = Empresa::findOrFail($id);

    return view('formularios.editar_empresa', compact('empresa'));
}


// Actualizar datos en DB
public function actualizar(Request $request, $id)
{
    $request->validate([
        'Nombre' => 'required|string|max:255',
        'NIT' => 'nullable|string|max:100',
        'NRC' => 'nullable|string|max:100',
        'Razon_Social' => 'nullable|string|max:255',
        'Direccion' => 'nullable|string',
        'Telefono' => 'nullable|string|max:50',
        'Correo_Contacto' => 'nullable|email|max:255',
    ]);

    $empresa = Empresa::findOrFail($id);
    $empresa->update($request->all());

    return redirect()->route('empresa.lista')->with('msg', '✅ Empresa actualizada correctamente.');
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
