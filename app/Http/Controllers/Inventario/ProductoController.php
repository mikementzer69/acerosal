<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Familia;

class ProductoController extends Controller
{
    /* ==========================
       LISTA
    ========================== */
    public function index(Request $request)
    {
        $codigo     = $request->get('codigo', '');
        $texto      = $request->get('texto', '');
        $idFamilia  = $request->get('familia', null);

        $productos = Producto::buscarConPaginacion(
            $codigo,
            $texto,
            $idFamilia,
            10
        );

        $familias = Familia::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.index', [
            'productos' => $productos,
            'familias'  => $familias,
            'codigo'    => $codigo,
            'texto'     => $texto,
            'idFamilia' => $idFamilia,
        ]);
    }

    /* ==========================
       NUEVO
    ========================== */
    public function crear()
    {
        $familias = Familia::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.crear', compact('familias'));
    }

    /* ==========================
       INSERTAR
    ========================== */
    public function insertar(Request $request)
    {
        $request->validate([
            'id_familia'            => 'required|integer',
            'codigo'                => 'required|string|max:255',
            'descripcion'           => 'required|string',
            'unidad_medida'         => 'required|string|max:20',
            'milimetros'            => 'nullable|numeric',
            'pulgadas'              => 'nullable|numeric',
            'tolerancia'            => 'nullable|numeric',
            'peso_lb_mts'           => 'nullable|numeric',
            'precio_venta_sin_iva'  => 'nullable|numeric',
            'precio_fijo'           => 'nullable|integer',
        ]);

        Producto::insertar($request->all());

        return redirect()->route('producto.lista')
                         ->with('msg', 'Producto creado correctamente.');
    }

    /* ==========================
       EDITAR
    ========================== */
    public function editar($id)
    {
        $producto = Producto::findOrFail($id);

        $familias = Familia::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.editar', compact('producto', 'familias'));
    }

    /* ==========================
       ACTUALIZAR
    ========================== */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'id_familia'            => 'required|integer',
            'codigo'                => 'required|string|max:255',
            'descripcion'           => 'required|string',
            'unidad_medida'         => 'required|string|max:20',
            'milimetros'            => 'nullable|numeric',
            'pulgadas'              => 'nullable|numeric',
            'tolerancia'            => 'nullable|numeric',
            'peso_lb_mts'           => 'nullable|numeric',
            'precio_venta_sin_iva'  => 'nullable|numeric',
            'precio_fijo'           => 'nullable|integer',
        ]);

        Producto::actualizarInline($id, $request->all());

        return redirect()->route('producto.lista')
                         ->with('msg', 'Producto actualizado correctamente.');
    }

    /* ==========================
       ELIMINAR (LÓGICO)
    ========================== */
    public function eliminar($id)
    {
        Producto::eliminarLogico($id);

        return redirect()->route('producto.lista')
                         ->with('msg', 'Producto eliminado.');
    }
}
