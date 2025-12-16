<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller

{
    public function mostrarLogin()
    {
        // Cargar empresas activas
        $empresas = DB::table('Empresa')
            ->where('Activo', 1)
            ->orderBy('Nombre', 'ASC')
            ->get();

        return view('login', compact('empresas'));
    }

    public function procesarLogin(Request $request)
    {
        // Validación
        $request->validate([
            'usuario' => 'required',
            'password' => 'required',
            'empresa' => 'required',
        ], [
            'usuario.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'empresa.required' => 'Debe seleccionar una empresa.',
        ]);

        // Buscar usuario
        $user = DB::table('Usuarios')
            ->where('Username', $request->usuario)
            ->first();

        if (!$user) {
            return back()->with('mensaje', '❌ Usuario o contraseña incorrectos.');
        }

        if (!$user->Activo) {
            return back()->with('mensaje', '❌ Usuario inactivo.');
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->Password)) {
            return back()->with('mensaje', '❌ Usuario o contraseña incorrectos.');
        }

        // Buscar empresa seleccionada
        $empresa = DB::table('Empresa')
            ->where('idEmpresa', $request->empresa)
            ->first();

        if (!$empresa) {
            return back()->with('mensaje', '❌ Empresa no válida.');
        }

        // Guardar sesión Laravel
        Session::put('idUsuario', $user->idUsuarios);
        Session::put('nombreUsuario', $user->Username);
        Session::put('idEmpresa', $empresa->idEmpresa);
        Session::put('nombreEmpresa', $empresa->Nombre);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }
}
