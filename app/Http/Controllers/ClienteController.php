<?php

namespace App\Http\Controllers;

use App\Models\Inventario\Cliente; // <--- Namespace correcto
use Illuminate\Http\Request;

class ClienteController extends Controller
{
public function index(Request $request)
{
    $query = Cliente::where('estado', '!=', 'ELIMINADO');

    // Lógica del buscador
    if ($request->has('busqueda') && $request->busqueda != '') {
        $busqueda = $request->busqueda;
        $query->where(function($q) use ($busqueda) {
            $q->where('codigo', 'like', "%$busqueda%")
              ->orWhere('nombre', 'like', "%$busqueda%")
              ->orWhere('documento', 'like', "%$busqueda%");
        });
    }

    // Usar paginate(10) en lugar de get() para que salgan los numeritos abajo
    $clientes = $query->orderBy('id_cliente', 'desc')->paginate(10);

    return view('clientes.index', compact('clientes'));
}

    public function create()
    {
        // Generador de código automático: CLI-0001
        $lastId = Cliente::max('id_cliente') + 1;
        $codigoSugerido = 'CLI-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

        $giros = \Illuminate\Support\Facades\DB::table('cliente_gironegocio')->where('estado', 1)->get();

        return view('clientes.create', compact('codigoSugerido', 'giros'));
    }

    public function store(Request $request)
    {
        // Validaciones
        $request->validate([
            'codigo'             => 'required|unique:clientes,codigo',
            'nombre'             => 'required|string|max:150',
            'documento'          => 'nullable|string|regex:/^\d{8}-\d$/', // DUI: 00000000-0
            'nit'                => 'nullable|string|regex:/^\d{4}-\d{6}-\d{3}-\d$/', // NIT: 0000-000000-000-0
            'nrc'                => 'nullable|string|regex:/^\d{7}-\d$/', // NRC: 0123456-7
            'nite'               => 'nullable|string|max:50',
            'pasaporte'          => 'nullable|string|max:50',
            'origen'             => 'required|in:N,E',
            'tipo_contribuyente' => 'required|in:O,M,G,P',
            'id_giro'            => 'nullable|integer',
            'correo'             => 'nullable|email',
        ], [
            'documento.regex' => 'El formato del DUI debe ser 00000000-0',
            'nit.regex'       => 'El formato del NIT debe ser 0000-000000-000-0',
            'nrc.regex'       => 'El formato del NRC debe ser 0123456-7',
        ]);

        // Crear Cliente
        Cliente::create([
            'codigo'             => $request->codigo,
            'nombre'             => $request->nombre,
            'nombre_comercial'   => $request->nombre_comercial,
            'tipo_cliente'       => $request->tipo_cliente,
            'documento'          => $request->documento,
            'telefono'           => $request->telefono,
            'correo'             => $request->correo,
            'contacto_principal' => $request->contacto_principal,
            'direccion'          => $request->direccion,
            'ciudad'             => $request->ciudad,
            'departamento'       => $request->departamento,
            'pais'               => 'El Salvador', // Valor por defecto
            'limite_credito'     => $request->limite_credito ?? 0,
            'dias_credito'       => $request->dias_credito ?? 0,
            'estado'             => $request->estado,
            'nit'                => $request->nit,
            'nrc'                => $request->nrc,
            'nite'               => $request->nite,
            'pasaporte'          => $request->pasaporte,
            'origen'             => $request->origen,
            'tipo_contribuyente' => $request->tipo_contribuyente,
            'id_giro'            => $request->id_giro,
            'exento'             => $request->has('exento'),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    // Método para MOSTRAR el formulario
public function edit($id)
{
    $cliente = Cliente::findOrFail($id);
    $giros = \Illuminate\Support\Facades\DB::table('cliente_gironegocio')->where('estado', 1)->get();
    
    return view('clientes.edit', compact('cliente', 'giros'));
}

// Método para GUARDAR los cambios
public function update(Request $request, $id)
{
    $cliente = Cliente::findOrFail($id);

    $request->validate([
        'nombre'             => 'required|string|max:150',
        'documento'          => 'nullable|string|regex:/^\d{8}-\d$/',
        'nit'                => 'nullable|string|regex:/^\d{4}-\d{6}-\d{3}-\d$/',
        'nrc'                => 'nullable|string|regex:/^\d{7}-\d$/',
        'nite'               => 'nullable|string|max:50',
        'pasaporte'          => 'nullable|string|max:50',
        'origen'             => 'required|in:N,E',
        'tipo_contribuyente' => 'required|in:O,M,G,P',
        'id_giro'            => 'nullable|integer',
        // Validar unique pero ignorando el ID actual para que no de error consigo mismo
        'codigo'    => 'required|unique:clientes,codigo,' . $cliente->id_cliente . ',id_cliente',
    ], [
        'documento.regex' => 'El formato del DUI debe ser 00000000-0',
        'nit.regex'       => 'El formato del NIT debe ser 0000-000000-000-0',
        'nrc.regex'       => 'El formato del NRC debe ser 0123456-7',
    ]);

    $data = $request->all();
    $data['exento'] = $request->has('exento');

    $cliente->update($data);

    return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
}
}
