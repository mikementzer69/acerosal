<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Usuario;
use App\Models\Inventario\Rol;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /* ==========================================
       LISTA CON FILTROS
    ========================================== */
    public function index(Request $request)
    {
        $query = Usuario::activos();

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $usuarios = $query
            ->orderBy('username')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    /* ==========================================
       FORMULARIO CREAR
    ========================================== */
    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    /* ==========================================
       GUARDAR NUEVO USUARIO
    ========================================== */
    public function store(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|max:50|unique:usuarios,username',
            'password'  => 'required|string|min:6',
            'nombre'    => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'email'     => 'required|email|max:150|unique:usuarios,email',
            'celular'   => 'nullable|string|max:20',
            'id_rol'    => 'required'
        ]);

        Usuario::create([
            'username'  => $request->username,
            'password'  => bcrypt($request->password),
            'nombre'    => $request->nombre,
            'apellidos' => $request->apellidos,
            'email'     => $request->email,
            'celular'   => $request->celular,
            'id_rol'    => $request->id_rol,
            'inactivo'  => 0,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('msg', 'Usuario creado correctamente');
    }

    /* ==========================================
       FORMULARIO EDITAR
    ========================================== */
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /* ==========================================
       ACTUALIZAR
    ========================================== */
/* ==========================================
       ACTUALIZAR (CORREGIDO)
    ========================================== */
/* ==========================================
   ACTUALIZAR (CORREGIDO)
========================================== */
/* ==========================================
       ACTUALIZAR (VERSIÓN FINAL BLINDADA)
    ========================================== */
    public function update(Request $request, $id)
    {
        // 1. Buscamos al usuario por su ID real
        $usuario = Usuario::findOrFail($id);

        // 2. Validamos los campos que sí se pueden editar en tu vista
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            // Validamos email ignorando el ID actual del usuario
            'email'     => 'required|email|max:150|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'celular'   => 'nullable|string|max:20',
            'id_rol'    => 'required'
        ]);

        // 3. Mapeo manual de datos: NO incluimos 'username' para protegerlo
        $usuario->nombre    = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->email     = $request->email;
        $usuario->celular   = $request->celular;
        $usuario->id_rol    = $request->id_rol; // ✨ Aquí se guarda el Rol (Vendedor/Admin)

        // 4. Solo si escribiste algo en el campo password de tu vista
        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        // 5. Guardado físico en la base de datos de Acerosal
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('mensaje', '✅ Usuario actualizado correctamente sin afectar el nombre de acceso.');
    }
        /* ==========================================
       ELIMINAR (LÓGICO)
    ========================================== */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['inactivo' => 1]);

        return redirect()
            ->route('usuarios.index')
            ->with('msg', 'Usuario desactivado correctamente');
    }
}
