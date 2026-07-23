<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Inventario\Usuario; // Asegúrate que esta sea la ruta de tu modelo

class LoginController extends Controller
{
    public function mostrarLogin()
    {
        $empresas = DB::table('empresas')
            ->where('inactivo', 0)
            ->orderBy('nombre', 'asc')
            ->get();

        return view('login', compact('empresas'));
    }

    public function procesarLogin(Request $request)
    {
        $request->validate([
            'usuario'  => 'required',
            'password' => 'required',
            'empresa'  => 'required',
        ], [
            'usuario.required'  => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'empresa.required'  => 'Debe seleccionar una empresa.',
        ]);

        $user = Usuario::where('username', $request->usuario)->first();

       if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->with('mensaje', '❌ Usuario o contraseña incorrectos.');
    }

    if ($user->inactivo == 1) {
        return back()->with('mensaje', '❌ Usuario inactivo.');
    }

    Auth::login($user);

        $empresa = DB::table('empresas')
            ->where('id_empresa', $request->empresa)
            ->first();

        if (!$empresa) {
            return back()->with('mensaje', '❌ Empresa no válida.');
        }

        // SESIÓN — USANDO LOS NOMBRES REALES DE LA BD
        Session::put('idUsuario', $user->id_usuario);
        Session::put('nombreUsuario', $user->username);
        Session::put('idEmpresa', $empresa->id_empresa);
        Session::put('nombreEmpresa', $empresa->nombre);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }
}
