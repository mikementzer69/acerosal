<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Familia;
use App\Models\Inventario\Ubicacion; // ✅ IMPORTANTE: Cargamos el modelo de Ubicaciones
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /* ==========================
       AJAX PRODUCTOS POR FAMILIA
    ========================== */
    public function porFamilia($id)
    {
        $productos = Producto::where('id_familia', $id)
            ->where('id_empresa', session('idEmpresa'))
            ->where('eliminado', 0)
            ->orderBy('descripcion')
            ->select('id_producto', 'descripcion', 'milimetros', 'pulgadas', 'codigo', 'peso_lb_mts')
            ->toBase()
            ->get();

        return response()->json($productos);
    }

    /* ==========================
       LISTA (INDEX)
    ========================= */
    public function index(Request $request)
    {
        $codigo      = $request->get('codigo', '');
        $texto       = $request->get('descripcion', ''); // Cambiado de 'texto' a 'descripcion' para ser consistente
        $idFamilia   = $request->get('id_familia', null);
        $idUbicacion = $request->get('id_ubicacion', null); // ✅ NUEVO: Filtro por ubicación

        // ⚠️ Nota: Asegúrate de que tu método buscarConPaginacion en el Modelo acepte el 5to parámetro para Ubicación
        $productos = Producto::buscarConPaginacion(
            $codigo,
            $texto,
            $idFamilia,
            10,
            session('idEmpresa'),
            $idUbicacion // ✅ PASAMOS EL FILTRO AL MODELO
        );

        $familias = Familia::where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        // ✅ CARGAMOS UBICACIONES PARA EL BUSCADOR
        $ubicaciones = Ubicacion::where('inactivo', 0)
            ->where('id_empresa', session('idEmpresa'))
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.index', compact(
            'productos', 'familias', 'ubicaciones', 'codigo', 'texto', 'idFamilia', 'idUbicacion'
        ));
    }

    /* ==========================
       NUEVO
    ========================== */
    public function crear()
    {
        $familias = Familia::where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        // ✅ ENVIAMOS UBICACIONES AL FORMULARIO
        $ubicaciones = Ubicacion::where('inactivo', 0)
            ->where('id_empresa', session('idEmpresa'))
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.crear', compact('familias', 'ubicaciones'));
    }

    /* ==========================
       INSERTAR
    ========================== */
    public function insertar(Request $request)
    {
        $request->validate([
            'id_familia'             => 'required|integer',
            'id_ubicacion'           => 'nullable|integer', // ✅ VALIDACIÓN DE UBICACIÓN
            'codigo'                 => [
                'required', 'string', 'max:255',
                Rule::unique('productos')->where(function ($query) {
                    return $query->where('id_empresa', session('idEmpresa'))->where('eliminado', 0);
                })
            ],
            'descripcion'            => 'required|string',
            'unidad_medida_longitud' => 'nullable|string|max:20',
            'unidad_medida_peso'     => 'nullable|string|max:20',
            'milimetros'             => 'nullable|string|max:50',
            'pulgadas'               => 'nullable|string|max:20',
            'pulgadas_decimal'       => 'nullable|numeric',
            'tolerancia'             => 'nullable|numeric',
            'peso_lb_mts'            => 'nullable|numeric',
            'precio_venta_sin_iva'   => 'required|numeric|min:0',
            'precio_fijo'            => 'nullable|integer',
            'precio_unitario_bodega' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['id_empresa'] = session('idEmpresa');

        Producto::insertar($data);

        return redirect()->route('producto.lista')
                         ->with('msg', '✅ Producto creado correctamente.');
    }

    /* ==========================
       EDITAR
    ========================== */
    public function editar($id)
    {
        $producto = Producto::where('id_empresa', session('idEmpresa'))
            ->findOrFail($id);

        $familias = Familia::where('inactivo', 0)
            ->orderBy('nombre')
            ->get();

        // ✅ ENVIAMOS UBICACIONES AL FORMULARIO DE EDICIÓN
        $ubicaciones = Ubicacion::where('inactivo', 0)
            ->where('id_empresa', session('idEmpresa'))
            ->orderBy('nombre')
            ->get();

        return view('inventario.productos.editar', compact('producto', 'familias', 'ubicaciones'));
    }

    /* ==========================
       ACTUALIZAR
    ========================== */
    public function actualizar(Request $request, $id)
    {
        $producto = Producto::where('id_empresa', session('idEmpresa'))
            ->findOrFail($id);

        $request->validate([
            'id_familia'             => 'required|integer',
            'id_ubicacion'           => 'nullable|integer', // ✅ VALIDACIÓN DE UBICACIÓN
            'codigo'                 => [
                'required', 'string', 'max:255',
                Rule::unique('productos')->ignore($id, 'id_producto')->where(function ($query) {
                    return $query->where('id_empresa', session('idEmpresa'))->where('eliminado', 0);
                })
            ],
            'descripcion'            => 'required|string',
            'unidad_medida_longitud' => 'nullable|string|max:20',
            'unidad_medida_peso'     => 'nullable|string|max:20',
            'milimetros'             => 'nullable|string|max:50',
            'pulgadas'               => 'nullable|string|max:20',
            'pulgadas_decimal'       => 'nullable|numeric',
            'tolerancia'             => 'nullable|numeric',
            'peso_lb_mts'            => 'nullable|numeric',
            'precio_venta_sin_iva'   => 'nullable|numeric',
            'precio_fijo'            => 'nullable|integer',
            'precio_unitario_bodega' => 'nullable|numeric',
        ]);

        $data = $request->all();
        $data['id_empresa'] = session('idEmpresa');

        Producto::actualizarInline($id, $data);

        return redirect()->route('producto.lista')
                         ->with('msg', '✅ Producto actualizado correctamente.');
    }

    /* ==========================
       ELIMINAR LÓGICO
    ========================== */
    public function eliminar($id)
    {
        $producto = Producto::where('id_empresa', session('idEmpresa'))
            ->findOrFail($id);

        Producto::eliminarLogico($id);

        return redirect()->route('producto.lista')
                         ->with('msg', 'Producto eliminado.');
    }
}
