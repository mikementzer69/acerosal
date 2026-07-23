<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Costos;

class CostosController extends Controller
{
    /* ===========================
       NUEVO COSTO
    ============================*/
    public function crear()
    {
        return view('inventario.costos.crear');
    }


    /* ===========================
       INSERTAR COSTO
    ============================*/
    public function insertar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string'
        ]);

        Costos::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('costo.lista')
            ->with('msg', 'Costo creado correctamente.');
    }


    /* ===========================
       LISTA DE COSTOS
    ============================*/
   public function lista(Request $request)
{
    $filtroNombre = $request->get('nombre', '');

    $costos = Costos::whereNull('deleted_at')
        ->when($filtroNombre !== '', function ($q) use ($filtroNombre) {
            $q->where('nombre', 'LIKE', "%{$filtroNombre}%");
        })
        ->orderBy('nombre', 'ASC')
        ->paginate(10);

    return view('inventario.costos.index', [
        'costos' => $costos,
        'filtroNombre' => $filtroNombre
    ]);
}



    /* ===========================
       EDITAR COSTO
    ============================*/
    public function editar($id)
    {
        $costo = Costos::findOrFail($id);
        return view('inventario.costos.editar', compact('costo'));
    }


    /* ===========================
       ACTUALIZAR COSTO
    ============================*/
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $costo = Costos::findOrFail($id);

        $costo->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        return redirect()->route('costo.lista')
            ->with('msg', 'Costo actualizado correctamente.');
    }


    /* ===========================
       ELIMINAR COSTO (SOFT DELETE)
    ============================*/
    public function eliminar($id)
    {
        $costo = Costos::findOrFail($id);
        $costo->delete();  // aplica deleted_at

        return redirect()->route('costo.lista')
            ->with('msg', 'Costo eliminado.');
    }
}
