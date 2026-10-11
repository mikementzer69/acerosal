<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Usuario;
use App\Models\Inventario\Rol;
use App\Models\Inventario\Empresa;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /* ==========================================
       LISTA CON FILTROS (GLOBAL / MULTI-SUCURSAL)
    ========================================== */
    public function index(Request $request)
    {
        $query = Usuario::activos()->with(['rol', 'empresa']);

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
        $empresas = Empresa::where('inactivo', 0)->orderBy('nombre')->get();
        return view('usuarios.create', compact('roles', 'empresas'));
    }

    /* ==========================================
       GUARDAR NUEVO USUARIO
    ========================================== */
    public function store(Request $request)
    {
        $request->validate([
            'username'   => 'required|string|max:50|unique:usuarios,username',
            'password'   => 'required|string|min:6',
            'nombre'     => 'required|string|max:100',
            'apellidos'  => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:usuarios,email',
            'celular'    => 'nullable|string|max:20',
            'id_rol'     => 'nullable',
            'rol_nombre' => 'nullable',
            'id_empresa' => 'nullable',
        ]);

        $idRol = $request->id_rol;
        if (!$idRol && $request->filled('rol_nombre')) {
            $rol = Rol::where('name', $request->rol_nombre)->first();
            $idRol = $rol ? $rol->id_rol : null;
        }

        if (!$idRol) {
            return back()->withErrors(['id_rol' => 'El rol es obligatorio.'])->withInput();
        }

        Usuario::create([
            'username'   => $request->username,
            'password'   => bcrypt($request->password),
            'nombre'     => $request->nombre,
            'apellidos'  => $request->apellidos,
            'email'      => $request->email,
            'celular'    => $request->celular,
            'id_rol'     => $idRol,
            'id_empresa' => $request->id_empresa ?? session('idEmpresa'),
            'inactivo'   => 0,
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
        $empresas = Empresa::where('inactivo', 0)->orderBy('nombre')->get();
        return view('usuarios.edit', compact('usuario', 'roles', 'empresas'));
    }

    /* ==========================================
       ACTUALIZAR (VERSIÓN FINAL BLINDADA)
    ========================================== */
    public function update(Request $request, $id)
    {
        // 1. Buscamos al usuario por su ID real
        $usuario = Usuario::findOrFail($id);

        // 2. Validamos los campos que sí se pueden editar en tu vista
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'apellidos'  => 'required|string|max:100',
            // Validamos email ignorando el ID actual del usuario
            'email'      => 'required|email|max:150|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'celular'    => 'nullable|string|max:20',
            'id_rol'     => 'required',
            'id_empresa' => 'nullable',
        ]);

        // 3. Mapeo manual de datos: NO incluimos 'username' para protegerlo
        $usuario->nombre    = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->email     = $request->email;
        $usuario->celular   = $request->celular;
        $usuario->id_rol    = $request->id_rol; // ✨ Aquí se guarda el Rol (Vendedor/Admin)

        if ($request->has('id_empresa')) {
            $usuario->id_empresa = $request->id_empresa ?: null;
        }

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
