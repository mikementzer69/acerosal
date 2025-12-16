<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Inventario\EmpresaController;
use App\Http\Controllers\Inventario\CostosController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\AsientoController;
use App\Http\Controllers\DemoContabilidadController;
use App\Http\Controllers\MayorizacionController;
use App\Http\Controllers\CierreContableController;
use App\Http\Controllers\Inventario\FamiliaController;
use App\Http\Controllers\Inventario\ModuloController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\TareaController;
use App\Http\Controllers\Inventario\CierreInventarioController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Inventario\CompraController;

Route::get('/demo-contabilidad', [DemoContabilidadController::class, 'generarDemo'])
     ->name('contabilidad.demo');

Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', function () {
        return view('inicio');
    });
});

// Dashboard


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
Route::get('/modulo/nuevo', fn() => 'Formulario: Nuevo Modulo')->name('modulo.nuevo');
Route::get('/producto/nuevo', fn() => 'Formulario: Nuevo Producto')->name('producto.nuevo');
Route::get('/usuario/nuevo', fn() => 'Formulario: Nuevo Usuario')->name('usuario.nuevo');
Route::get('/rol/nuevo', fn() => 'Formulario: Nuevo Rol')->name('rol.nuevo');
Route::get('/proveedor/nuevo', fn() => 'Formulario: Nuevo Proveedor')->name('proveedor.nuevo');
Route::get('/compra/nueva', fn() => 'Formulario: Nueva Compra')->name('compra.nueva');
Route::get('/inventario/nuevo', fn() => 'Formulario: Inventario')->name('inventario.nuevo');
Route::get('/inventario/manual', fn() => 'Formulario: Inventario Manual')->name('inventario.manual');

// -------- LISTAS ----------
Route::get('/familia/lista', fn() => 'Lista: Familias')->name('familia.lista');
Route::get('/modulo/lista', fn() => 'Lista: Modulos')->name('modulo.lista');
Route::get('/producto/lista', fn() => 'Lista: Productos')->name('producto.lista');
Route::get('/usuario/lista', fn() => 'Lista: Usuarios')->name('usuario.lista');
Route::get('/rol/lista', fn() => 'Lista: Roles')->name('rol.lista');
Route::get('/proveedor/lista', fn() => 'Lista: Proveedores')->name('proveedor.lista');

// -------- GESTIONES ----------
Route::get('/familia/gestion', fn() => 'Gestionar: Familias')->name('familia.gestion');
Route::get('/modulo/gestion', fn() => 'Gestionar: Modulos')->name('modulo.gestion');
Route::get('/producto/gestion', fn() => 'Gestionar: Productos')->name('producto.gestion');
Route::get('/usuario/gestion', fn() => 'Gestionar: Usuarios')->name('usuario.gestion');
Route::get('/rol/gestion', fn() => 'Gestionar: Roles')->name('rol.gestion');
Route::get('/proveedor/gestion', fn() => 'Gestionar: Proveedores')->name('proveedor.gestion');



Route::resource('cuentas', CuentaController::class);
Route::resource('asientos', AsientoController::class);

Route::get('/mayorizacion', [MayorizacionController::class, 'index'])
    ->name('mayorizacion.index');

Route::post('/mayorizacion/generar', [MayorizacionController::class, 'generar'])
    ->name('mayorizacion.generar');

Route::get('/cierre', [CierreContableController::class, 'index'])->name('cierre.index');
Route::post('/cierre/generar', [CierreContableController::class, 'cerrarMes'])->name('cierre.generar');

Route::resource('asientos', AsientoController::class)
    ->middleware('bloquear.mes');

Route::get('/costo/nuevo', [CostosController::class, 'crear'])->name('costo.nuevo');
Route::post('/costo/insertar', [CostosController::class, 'insertar'])->name('costo.insertar');
Route::get('/costo/lista', [CostosController::class, 'lista'])->name('costo.lista');
Route::get('/costo/editar/{id}', [CostosController::class, 'editar'])->name('costo.editar');
Route::put('costo/actualizar/{id}', [CostosController::class, 'actualizar'])
    ->name('costo.actualizar');

Route::delete('/costo/eliminar/{id}', [CostosController::class, 'eliminar'])->name('costo.eliminar');



Route::get('/familia/lista', [FamiliaController::class, 'index'])->name('familia.lista');
Route::get('/familia/nueva', [FamiliaController::class, 'crear'])->name('familia.nueva');
Route::post('/familia/insertar', [FamiliaController::class, 'insertar'])->name('familia.insertar');

Route::get('/familia/editar/{id}', [FamiliaController::class, 'editar'])->name('familia.editar');
Route::put('familia/actualizar/{id}', [FamiliaController::class, 'actualizar'])
    ->name('familia.actualizar');


Route::delete('/familia/eliminar/{id}', [FamiliaController::class, 'eliminar'])->name('familia.eliminar');


Route::get('/modulo/lista', [ModuloController::class, 'index'])->name('modulo.lista');
Route::get('/modulo/nuevo', [ModuloController::class, 'crear'])->name('modulo.nuevo');
Route::post('/modulo/insertar', [ModuloController::class, 'insertar'])->name('modulo.insertar');

Route::get('/modulo/editar/{id}', [ModuloController::class, 'editar'])->name('modulo.editar');
Route::put('/modulo/actualizar/{id}', [ModuloController::class, 'actualizar'])->name('modulo.actualizar');

Route::delete('/modulo/eliminar/{id}', [ModuloController::class, 'eliminar'])->name('modulo.eliminar');

// === PRODUCTOS ===
/* PRODUCTOS */
Route::get('producto/lista', [ProductoController::class, 'index'])->name('producto.lista');
Route::get('producto/nuevo', [ProductoController::class, 'crear'])->name('producto.nuevo');
Route::post('producto/insertar', [ProductoController::class, 'insertar'])->name('producto.insertar');

Route::get('producto/editar/{id}', [ProductoController::class, 'editar'])->name('producto.editar');

/* ESTA ES LA IMPORTANTE */
Route::put('producto/actualizar/{id}', [ProductoController::class, 'actualizar'])->name('producto.actualizar');

Route::delete('producto/eliminar/{id}', [ProductoController::class, 'eliminar'])->name('producto.eliminar');

/* TAREAS */
Route::prefix('tarea')->group(function () {

    Route::get('/lista', [TareaController::class, 'index'])->name('tarea.lista');
    Route::get('/nuevo', [TareaController::class, 'crear'])->name('tarea.nueva');
    Route::post('/insertar', [TareaController::class, 'insertar'])->name('tarea.insertar');

    Route::get('/editar/{id}', [TareaController::class, 'editar'])->name('tarea.editar');
    Route::post('/actualizar/{id}', [TareaController::class, 'actualizar'])->name('tarea.actualizar');

    Route::delete('/eliminar/{id}', [TareaController::class, 'eliminar'])->name('tarea.eliminar');
});


/* TAREAS */
Route::prefix('tarea')->group(function () {

    // LISTA
    Route::get('/lista', [TareaController::class, 'index'])
        ->name('tarea.lista');

    // NUEVO FORMULARIO
    Route::get('/nuevo', [TareaController::class, 'crear'])
        ->name('tarea.nueva');

    // INSERTAR
    Route::post('/insertar', [TareaController::class, 'insertar'])
        ->name('tarea.insertar');

    // EDITAR FORMULARIO
    Route::get('/editar/{id}', [TareaController::class, 'editar'])
        ->name('tarea.editar');

    // ACTUALIZAR (usa POST porque tu controlador usa POST)
    Route::post('/actualizar/{id}', [TareaController::class, 'actualizar'])
        ->name('tarea.actualizar');

    // ELIMINAR
    Route::delete('/eliminar/{id}', [TareaController::class, 'eliminar'])
        ->name('tarea.eliminar');

});



Route::prefix('inventario')->group(function () {

    // Cierre Diario
    Route::get('/cierre-diario', [CierreInventarioController::class, 'vistaCierreDiario'])
        ->name('inventario.cierre.diario');

    Route::post('/cierre-diario/ejecutar', [CierreInventarioController::class, 'ejecutarCierreDiario'])
        ->name('inventario.cierre.diario.ejecutar');

    // Cierre Mensual
    Route::get('/cierre-mensual', [CierreInventarioController::class, 'vistaCierreMensual'])
        ->name('inventario.cierre.mensual');

    Route::post('/cierre-mensual/ejecutar', [CierreInventarioController::class, 'ejecutarCierreMensual'])
        ->name('inventario.cierre.mensual.ejecutar');
});

 /*
    |--------------------------------------------------------------------------
    | Inventario
    |--------------------------------------------------------------------------
    */  
    Route::prefix('inventario')->name('inventario.')->group(function () {

        // Formulario automático
        Route::get('/automatico', [InventarioController::class, 'automatico'])
            ->name('automatico');

        // AJAX detalle compra
        Route::get('/compra/{id}/detalle', [InventarioController::class, 'detalleCompra'])
            ->name('detalle-compra');

        // Validar código lote
        Route::get('/verificar-codigo-lote', [InventarioController::class, 'verificarCodigoLote'])
            ->name('verificar-codigo-lote');

        // Guardar inventario
        Route::post('/automatico/guardar', [InventarioController::class, 'guardarAutomatico'])
            ->name('guardar-automatico');
    });

/*
    |--------------------------------------------------------------------------
    | COMPRAS
    |--------------------------------------------------------------------------
    */  

    Route::prefix('compras')->name('compras.')->group(function () {

        // LISTA PRINCIPAL (solo activas)
        Route::get('/', [CompraController::class, 'index'])->name('lista');

        // CREAR
        Route::get('/nueva', [CompraController::class, 'create'])->name('nueva');
        Route::post('/guardar', [CompraController::class, 'store'])->name('store');

        // DETALLE
        Route::get('/detalle/{id}', [CompraController::class, 'detalle'])->name('detalle');

        // ELIMINAR / ANULAR
        Route::post('/eliminar/{id}', [CompraController::class, 'eliminar'])->name('eliminar');

        // SOLO ADMIN – VER ANULADAS
        Route::get('/anuladas', [CompraController::class, 'anuladas'])
            ->middleware('admin')
            ->name('anuladas');

    });