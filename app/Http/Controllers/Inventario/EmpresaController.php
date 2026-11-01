<?php

namespace App\Http\Controllers\Inventario;

use Illuminate\Http\Request;
use App\Models\Inventario\Empresa;
use App\Http\Controllers\Controller;

class EmpresaController extends Controller
{
    /**
     * Obtiene los archivos de imagen disponibles en public/images
     */
    private function obtenerLogosAlmacenados(): array
    {
        $rutaImages = public_path('images');
        $logos = [];
        if (is_dir($rutaImages)) {
            foreach (scandir($rutaImages) as $archivo) {
                if (preg_match('/\.(png|jpe?g|webp|svg|gif)$/i', $archivo)) {
                    $logos[] = 'images/' . $archivo;
                }
            }
        }
        return $logos;
    }

    /* Mostrar formulario */
    public function crear()
    {
        $siguienteId = ((int) Empresa::max('id_empresa')) + 1;
        $logosDisponibles = $this->obtenerLogosAlmacenados();

        return view('inventario.empresa.crear', compact('siguienteId', 'logosDisponibles'));
    }

    /* Insertar nueva empresa */
    public function insertar(Request $request)
    {
        $request->validate([
            'id_empresa'      => 'required|integer|min:1',
            'nombre'          => 'required|string|max:255',
            'nit'             => 'nullable|string|max:100',
            'nrc'             => 'nullable|string|max:100',
            'razon_social'    => 'nullable|string|max:255',
            'direccion'       => 'nullable|string',
            'telefono'        => 'nullable|string|max:50',
            'correo_contacto' => 'nullable|email|max:255',
            'logo_existente'  => 'nullable|string|max:255',
            'logo_file'       => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
        ]);

        $idEmpresa = (int) $request->id_empresa;
        $existente = Empresa::find($idEmpresa);

        if ($existente && is_null($existente->deleted_at)) {
            return back()->withInput()->with('msg', "⚠️ Ya existe una empresa activa con el ID {$idEmpresa}. Puede editarla desde el listado o elegir otro ID.");
        }

        $rutaLogo = $request->input('logo_existente') ?: ($idEmpresa === 2 ? 'images/logo_empresa2.jpg' : 'images/logo.png');

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $nombreArchivo = 'logo_empresa' . $idEmpresa . '_' . time() . '.' . $ext;
            $file->move(public_path('images'), $nombreArchivo);
            $rutaLogo = 'images/' . $nombreArchivo;
        }

        $datos = [
            'id_empresa'      => $idEmpresa,
            'nombre'          => $request->nombre,
            'nit'             => $request->nit ?? '',
            'nrc'             => $request->nrc ?? '',
            'razon_social'    => $request->razon_social ?? '',
            'direccion'       => $request->direccion ?? '',
            'telefono'        => $request->telefono ?? '',
            'correo_contacto' => $request->correo_contacto ?? '',
            'logo'            => $rutaLogo,
            'inactivo'        => 0,
            'deleted_at'      => null,
        ];

        if ($existente) {
            $existente->update($datos);
        } else {
            Empresa::create($datos);
        }

        return redirect()->route('empresa.lista')
            ->with('msg', "✅ Empresa #{$idEmpresa} guardada correctamente.");
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
            ->orderBy('id_empresa')
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
        $logosDisponibles = $this->obtenerLogosAlmacenados();

        return view('inventario.empresa.editar', compact('empresa', 'logosDisponibles'));
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
            'logo_existente'  => 'nullable|string|max:255',
            'logo_file'       => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
        ]);

        $empresa = Empresa::findOrFail($id);

        $rutaLogo = $request->input('logo_existente') ?: ($empresa->logo ?: ($empresa->id_empresa == 2 ? 'images/logo_empresa2.jpg' : 'images/logo.png'));

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $nombreArchivo = 'logo_empresa' . $empresa->id_empresa . '_' . time() . '.' . $ext;
            $file->move(public_path('images'), $nombreArchivo);
            $rutaLogo = 'images/' . $nombreArchivo;
        }

        $empresa->update([
            'nombre'          => $request->nombre,
            'nit'             => $request->nit ?? '',
            'nrc'             => $request->nrc ?? '',
            'razon_social'    => $request->razon_social ?? '',
            'direccion'       => $request->direccion ?? '',
            'telefono'        => $request->telefono ?? '',
            'correo_contacto' => $request->correo_contacto ?? '',
            'logo'            => $rutaLogo,
        ]);

        return redirect()->route('empresa.lista')
            ->with('msg', '✅ Empresa actualizada correctamente.');
    }


    /* Eliminación lógica */
    public function eliminar($id)
    {
        $empresa = Empresa::findOrFail($id);

        $empresa->update([
            'inactivo'   => 1,
            'deleted_at' => now()
        ]);

        return redirect()->route('empresa.lista')
            ->with('msg', '🗑️ Empresa eliminada.');
    }
}
