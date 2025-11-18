<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CostosController;

Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', function () {
        return view('inicio');
    });
});

// Dashboard

// Compras
Route::get('/compras/nueva', fn() => 'nueva compra')->name('compras.nueva');

// Productos
Route::get('/productos', fn() => 'lista productos')->name('productos.lista');
Route::get('/productos/nuevo', fn() => 'nuevo producto')->name('productos.nuevo');

// Proveedores
Route::get('/proveedores', fn() => 'lista proveedores')->name('proveedores.lista');

// Usuarios
Route::get('/usuarios', fn() => 'lista usuarios')->name('usuarios.lista');
Route::get('/usuarios/nuevo', fn() => 'nuevo usuario')->name('usuarios.nuevo');

// Inventario
Route::get('/inventario', fn() => 'inventario')->name('inventario.form');

// Roles
Route::get('/roles', fn() => 'lista roles')->name('roles.lista');

// Mostrar formulario
Route::get('/empresa/nueva', [EmpresaController::class, 'crear'])
    ->name('empresa.nueva');

// Guardar datos
Route::post('/empresa/insertar', [EmpresaController::class, 'insertar'])
    ->name('empresa.insertar');

// Listado
Route::get('/empresa/lista/', [EmpresaController::class, 'lista'])
    ->name('empresa.lista');

route::get('/empresa/editar/{id}', [EmpresaController::class, 'editar'])
    ->name('empresa.editar');

Route::put('/empresa/actualizar/{id}', [EmpresaController::class, 'actualizar'])
    ->name('empresa.actualizar');

Route::delete('/empresa/eliminar/{id}', [EmpresaController::class, 'eliminar'])
    ->name('empresa.eliminar');
// -------- FORMULARIOS ----------
Route::get('/familia/nueva', fn() => 'Formulario: Nueva Familia')->name('familia.nueva');
Route::get('/costo/nuevo', fn() => 'Formulario: Nuevo Costo')->name('costo.nuevo');
Route::get('/modulo/nuevo', fn() => 'Formulario: Nuevo Modulo')->name('modulo.nuevo');
Route::get('/producto/nuevo', fn() => 'Formulario: Nuevo Producto')->name('producto.nuevo');
Route::get('/tarea/nueva', fn() => 'Formulario: Nueva Tarea')->name('tarea.nueva');
Route::get('/usuario/nuevo', fn() => 'Formulario: Nuevo Usuario')->name('usuario.nuevo');
Route::get('/rol/nuevo', fn() => 'Formulario: Nuevo Rol')->name('rol.nuevo');
Route::get('/proveedor/nuevo', fn() => 'Formulario: Nuevo Proveedor')->name('proveedor.nuevo');
Route::get('/compra/nueva', fn() => 'Formulario: Nueva Compra')->name('compra.nueva');
Route::get('/inventario/nuevo', fn() => 'Formulario: Inventario')->name('inventario.nuevo');
Route::get('/inventario/manual', fn() => 'Formulario: Inventario Manual')->name('inventario.manual');

// -------- LISTAS ----------
Route::get('/familia/lista', fn() => 'Lista: Familias')->name('familia.lista');
Route::get('/costo/lista', fn() => 'Lista: Costos')->name('costo.lista');
Route::get('/modulo/lista', fn() => 'Lista: Modulos')->name('modulo.lista');
Route::get('/producto/lista', fn() => 'Lista: Productos')->name('producto.lista');
Route::get('/tarea/lista', fn() => 'Lista: Tareas')->name('tarea.lista');
Route::get('/usuario/lista', fn() => 'Lista: Usuarios')->name('usuario.lista');
Route::get('/rol/lista', fn() => 'Lista: Roles')->name('rol.lista');
Route::get('/proveedor/lista', fn() => 'Lista: Proveedores')->name('proveedor.lista');

// -------- GESTIONES ----------
Route::get('/familia/gestion', fn() => 'Gestionar: Familias')->name('familia.gestion');
Route::get('/costo/gestion', fn() => 'Gestionar: Costos')->name('costo.gestion');
Route::get('/modulo/gestion', fn() => 'Gestionar: Modulos')->name('modulo.gestion');
Route::get('/producto/gestion', fn() => 'Gestionar: Productos')->name('producto.gestion');
Route::get('/tarea/gestion', fn() => 'Gestionar: Tareas')->name('tarea.gestion');
Route::get('/usuario/gestion', fn() => 'Gestionar: Usuarios')->name('usuario.gestion');
Route::get('/rol/gestion', fn() => 'Gestionar: Roles')->name('rol.gestion');
Route::get('/proveedor/gestion', fn() => 'Gestionar: Proveedores')->name('proveedor.gestion');


Route::get('/costo/nuevo', [CostosController::class, 'crear'])->name('costo.nuevo');
Route::post('/costo/insertar', [CostosController::class, 'insertar'])->name('costo.insertar');

Route::get('/costo/lista', [CostosController::class, 'lista'])->name('costo.lista');

Route::get('/costo/editar/{id}', [CostosController::class, 'editar'])->name('costo.editar');
Route::post('/costo/actualizar/{id}', [CostosController::class, 'actualizar'])->name('costo.actualizar');

Route::delete('/costo/eliminar/{id}', [CostosController::class, 'eliminar'])->name('costo.eliminar');

Route::get('/costo/lista', [CostosController::class, 'lista'])
    ->name('costo.lista');

Route::get('/costo/nuevo', [CostosController::class, 'crear'])
    ->name('costo.nuevo');

Route::post('/costo/insertar', [CostosController::class, 'insertar'])
    ->name('costo.insertar');

    