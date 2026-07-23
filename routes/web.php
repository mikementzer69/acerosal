<?php


use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Inventario\OrdenDespachoController;
use App\Http\Controllers\Inventario\InventarioInicialController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventario\UbicacionController;
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
use App\Http\Controllers\Inventario\UsuarioController;
use App\Http\Controllers\Inventario\RolController;
use App\Http\Controllers\Inventario\ProveedorController;
use App\Http\Controllers\Inventario\InventarioProductoController;
use App\Http\Controllers\Inventario\InventarioAjusteController;
use App\Http\Controllers\Inventario\CompraCostoController;



Route::get('/demo-contabilidad', [DemoContabilidadController::class, 'generarDemo'])
     ->name('contabilidad.demo');

Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'procesarLogin']);
Route::get('/logout', [LoginController::class, 'logout']);


Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', function () {
        return view('inicio');
        })->name('dashboard'); // 👈 AGREGA ESTO AQUÍ
    });




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
// Usuarios
Route::get('/usuarios', fn() => 'lista usuarios')->name('usuarios.lista');
Route::get('/usuarios/nuevo', fn() => 'nuevo usuario')->name('usuarios.nuevo');


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
Route::get('/compra/nueva', fn() => 'Formulario: Nueva Compra')->name('compra.nueva');
Route::get('/inventario/nuevo', fn() => 'Formulario: Inventario')->name('inventario.nuevo');
Route::get('/inventario/manual', fn() => 'Formulario: Inventario Manual')->name('inventario.manual');



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

Route::get(
    'productos/por-familia/{id}',
    [ProductoController::class, 'porFamilia']
)->name('productos.porFamilia');

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

    // ACTUALIZAR (usa PUT)
    Route::put('/actualizar/{id}', [TareaController::class, 'actualizar'])
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

    Route::post('/inventario/reconstruir', [OrdenDespachoController::class, 'reconstruirInventario'])
    ->name('inventario.reconstruir');
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

    // CORREGIDO: Quitamos el prefijo repetido y cerramos el grupo
    Route::get('/producto/{idProducto}/siguiente-lote', [InventarioController::class, 'siguienteCodigoLote'])
        ->name('siguiente-lote');

                // Entrada (sidebar)
       Route::get(
    '/inventario/consulta',
    [InventarioProductoController::class, 'index']
)->name('inventario.consulta');

Route::get('/inventario/consulta', [InventarioProductoController::class, 'index'])
    ->name('inventario.productos.index');
                // Detalle producto
       Route::get(
    '/inventario/producto/{id_producto}',
    [InventarioProductoController::class, 'ver']
)->name('inventario.producto');


Route::get('inventario/ubicaciones', [UbicacionController::class, 'index'])->name('ubicaciones.index');
Route::get('inventario/ubicaciones/crear', [UbicacionController::class, 'create'])->name('ubicaciones.create');
Route::post('inventario/ubicaciones', [UbicacionController::class, 'store'])->name('ubicaciones.store');
Route::get('inventario/ubicaciones/{id}/editar', [UbicacionController::class, 'edit'])->name('ubicaciones.edit');
Route::put('inventario/ubicaciones/{id}', [UbicacionController::class, 'update'])->name('ubicaciones.update');
Route::delete('inventario/ubicaciones/{id}', [UbicacionController::class, 'destroy'])->name('ubicaciones.destroy');

Route::get(
    '/piezas-por-producto/{id_producto}',
    [OrdenDespachoController::class, 'piezasPorProducto']
)->name('piezas.producto');


Route::get(
    '/inventario/producto/piezas/{id_producto}',
    [InventarioProductoController::class, 'piezasPorProducto']
)->name('inventario.producto.piezas');

}); // <--- ESTO ES LO QUE TE FALTABA CERRAR (Línea 361)
/*
    |--------------------------------------------------------------------------
    | COMPRAS
    |--------------------------------------------------------------------------
    */



Route::prefix('compras')->group(function () {

    Route::get('/', [CompraController::class, 'index'])
        ->name('compras.index');

    Route::get('/nueva', [CompraController::class, 'create'])
        ->name('compras.nueva');

    Route::post('/guardar', [CompraController::class, 'store'])
        ->name('compras.store');

    // 1. Ver el detalle de una compra (ESTA ES LA QUE TE FALTA)
    Route::get('/compras/{id}', [CompraController::class, 'show'])->name('compras.show');

    // 2. Formulario de edición
    Route::get('/compras/{id}/editar', [CompraController::class, 'edit'])->name('compras.edit');

    // 3. Guardar cambios de edición
    Route::put('/compras/{id}', [CompraController::class, 'update'])->name('compras.update');

});


    //USUARIOS
// 🔒 Cambiamos 'sesion' por el combo que detecta al usuario y luego valida al ADMIN
// 🔒 Cambiamos 'sesion' por el combo que detecta al usuario y luego valida al ADMIN
Route::middleware(['auth.custom', 'admin'])->group(function () {

    Route::get('/usuarios',           [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear',      [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios',           [UsuarioController::class, 'store'])->name('usuarios.store');

    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}',         [UsuarioController::class, 'update'])->name('usuarios.update');

    Route::delete('/usuarios/{usuario}',      [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

Route::get('/roles', [RolController::class, 'index'])
        ->name('roles.index');

    // CREAR
    Route::get('/roles/crear', [RolController::class, 'create'])
        ->name('roles.create');

    Route::post('/roles', [RolController::class, 'store'])
        ->name('roles.store');

    // EDITAR
    Route::get('/roles/{id}/editar', [RolController::class, 'edit'])
        ->name('roles.edit');

    Route::put('/roles/{id}', [RolController::class, 'update'])
        ->name('roles.update');

    // ELIMINAR (LÓGICO)
    Route::delete('/roles/{id}', [RolController::class, 'destroy'])
        ->name('roles.destroy');


});
//Roles


///¿PROVEEDORE

Route::middleware('sesion')->group(function () {

    // LISTADO
    Route::get('/proveedores', [ProveedorController::class, 'index'])
        ->name('proveedores.index');

    // CREAR
    Route::get('/proveedores/crear', [ProveedorController::class, 'create'])
        ->name('proveedores.create');

    Route::post('/proveedores', [ProveedorController::class, 'store'])
        ->name('proveedores.store');

    // EDITAR
    Route::get('/proveedores/{id}/editar', [ProveedorController::class, 'edit'])
        ->name('proveedores.edit');

    Route::put('/proveedores/{id}', [ProveedorController::class, 'update'])
        ->name('proveedores.update');

    // ELIMINAR (LÓGICO)
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])
        ->name('proveedores.destroy');

});

/////////////
Route::get(
    '/inventario/despacho/nuevo',
    [OrdenDespachoController::class, 'create']
)->name('inventario.despacho.create');

Route::post(
    '/inventario/despacho/guardar',
    [OrdenDespachoController::class, 'store']
)->name('inventario.despacho.store');

Route::get(
    '/inventario/despacho',
    [OrdenDespachoController::class, 'index']
)->name('inventario.despacho.index');

Route::get(
    '/inventario/despacho/{id}',
    [OrdenDespachoController::class, 'show']
)->name('inventario.despacho.show');

// Ruta para la misión de anulación
Route::post('/inventario/despacho/anular/{id}', [OrdenDespachoController::class, 'anular'])
    ->name('inventario.despacho.anular');

Route::get(
    '/inventario/productos-por-familia/{id_familia}',
    [App\Http\Controllers\Inventario\OrdenDespachoController::class, 'productosPorFamilia']
)->name('inventario.productos.familia');

Route::get(
    '/inventario/lotes-por-producto/{id_producto}',
    [App\Http\Controllers\Inventario\OrdenDespachoController::class, 'lotesPorProducto']
)->name('inventario.lotes.producto');

Route::get(
    '/inventario/piezas-por-lote/{id_lote}',
    [App\Http\Controllers\Inventario\OrdenDespachoController::class, 'piezasPorLote']
)->name('inventario.piezas.lote');
////Inventario inicial

// Ruta para el reingreso de piezas nuevas desde retales
Route::post('/inventario/reingreso/store', [App\Http\Controllers\Inventario\InventarioAjusteController::class, 'storeReingreso'])->name('inventario.reingreso.store');



Route::resource('clientes', ClienteController::class);

// Esta ruta es solo para VER. No usa JS, no usa AJAX.
Route::get('/inventario/consulta-factura/{id}', [InventarioController::class, 'verFacturaTerminada'])->name('inventario.consulta');




// Usamos POST para mayor seguridad en la anulación
Route::post('/compras/{id}/anular', [CompraController::class, 'anular'])->name('compras.anular');

    Route::post('/compra/procesar-costos', [CompraCostoController::class, 'procesarCostos'])
    ->name('compra.procesar.costos');


    Route::get('/individual', [InventarioAjusteController::class, 'index'])->name('inventario.ajuste.index');

    // Rutas para la cascada de búsqueda (AJAX)
    Route::get('/lotes/{id_producto}', [InventarioAjusteController::class, 'getLotes']);
    Route::get('/piezas/{id_lote}', [InventarioAjusteController::class, 'getPiezas']);

    // Rutas de acción
    Route::get('/buscar', [InventarioAjusteController::class, 'buscarPieza'])->name('inventario.ajuste.buscar');
    Route::post('/guardar', [InventarioAjusteController::class, 'store'])->name('inventario.ajuste.store');

// Agrega esta línea en el grupo de rutas de inventario
Route::get('/piezas/por-lote/{id}', [OrdenDespachoController::class, 'piezasPorLote']);

// --- BLOQUE CORREGIDO PARA AJUSTES DE INVENTARIO ---
// --- BLOQUE ÚNICO Y DEFINITIVO PARA AJUSTES Y KARDEX ---
    Route::prefix('inventario/ajustes')->group(function () {


    Route::get('/inicial', [InventarioInicialController::class, 'index'])->name('inventario.inicial.index');
    Route::post('/inicial/store', [InventarioInicialController::class, 'store'])->name('inventario.inicial.store');

    // Formulario de Ajuste Individual
    Route::get('/individual', [InventarioAjusteController::class, 'index'])
        ->name('inventario.ajuste.index');
    // Agrégala cerca de las otras rutas de inventario inicial
    Route::get('/inventario/inicial/productos/{id_familia}', [App\Http\Controllers\Inventario\InventarioInicialController::class, 'getProductosPorFamilia']);

    // Búsqueda AJAX (Lotes y Piezas)
    Route::get('/lotes/{id_producto}', [InventarioAjusteController::class, 'getLotes']);
    Route::get('/piezas/{id_lote}', [InventarioAjusteController::class, 'getPiezas']);

    // Acciones de búsqueda y guardado
    Route::get('/buscar', [InventarioAjusteController::class, 'buscarPieza'])
        ->name('inventario.ajuste.buscar');

    Route::post('/guardar', [InventarioAjusteController::class, 'store'])
        ->name('inventario.ajuste.store');

    // EL KARDEX PARA EL DEMO
    Route::get('/kardex', [InventarioAjusteController::class, 'indexKardex'])
        ->name('inventario.ajuste.kardex');

    Route::get('/kardex/exportar/{id}', [InventarioAjusteController::class, 'exportarKardex'])
        ->name('inventario.ajuste.exportar');

        // Agrega esta línea para que el botón de PDF tenga "vida"
    Route::get('/inventario/ajustes/kardex/pdf', [App\Http\Controllers\Inventario\InventarioAjusteController::class, 'exportarKardexPdf'])
        ->name('inventario.ajuste.exportar_pdf');


});

// RUTAS DE APOYO PARA CONSULTA (Pégalas al final de web.php)
// RUTAS AJAX PARA CONSULTA - FUERA DE CUALQUIER GRUPO
Route::get('/ajax/productos/{id_familia}', [App\Http\Controllers\Inventario\OrdenDespachoController::class, 'productosPorFamilia'])
    ->name('ajax.consulta.productos');

Route::get('/ajax/piezas/{id_producto}', [App\Http\Controllers\Inventario\OrdenDespachoController::class, 'piezasPorProducto'])
    ->name('ajax.consulta.piezas');

// Esta ruta es NUEVA y solo para la consulta. No afecta al despacho.
Route::get('/inventario/ajax/piezas-con-medidas/{id_producto}', [App\Http\Controllers\Inventario\InventarioProductoController::class, 'piezasPorProducto'])
    ->name('consulta.piezas.medidas');

// RUTA PARA FILTRAR LA TABLA (Exclusiva de consulta)
Route::get('/inventario/consulta/filtrar', [App\Http\Controllers\Inventario\InventarioProductoController::class, 'filtrarInventario'])
    ->name('acerosal.filtrar');

    Route::get('/inventario/consulta/lotes/{id}', [App\Http\Controllers\Inventario\InventarioProductoController::class, 'piezasPorProducto'])->name('acerosal.lotes');

    Route::get('/inventario/consulta', [InventarioProductoController::class, 'index'])->name('inventario.inventario.consulta');
    // Esta es la pieza que le falta al rompecabezas
Route::get('/inventario/inventario/filtrar', [InventarioProductoController::class, 'filtrar'])->name('inventario.inventario.filtrar');

// 2. La ruta del detalle (Aseguramos que el URI sea el que tu Blade espera)
Route::get('/inventario/producto/{id}', [InventarioProductoController::class, 'ver'])->name('inventario.inventario.producto');


Route::get('/inventario/productos-familia/{id_familia}', [InventarioProductoController::class, 'productosPorFamilia'])->name('inventario.productos.familia');

// 2. Ruta para filtrar (Botón CONSULTAR)
Route::get('/inventario/filtrar', [InventarioProductoController::class, 'filtrar'])->name('inventario.filtrar');

// 3. Ruta para el detalle (El que daba 404)
Route::get('/inventario/producto/{id}', [InventarioProductoController::class, 'ver'])->name('inventario.inventario.producto');

// 4. Ruta para las piezas (Selector #3) - Asegúrate de que este nombre coincida con tu JS
Route::get('/inventario/piezas-medidas/{id_producto}', [InventarioProductoController::class, 'piezasPorProducto'])->name('consulta.piezas.medidas');

// Cambia 'AjusteController' por el nombre real de tu clase
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

Route::get('/reparar-stock', function () {
    // 1. Aumentamos el tiempo de ejecución por si son muchos datos
    set_time_limit(300);

    $resultados = [];

    // 2. Traemos todos los productos activos
    $productos = DB::table('productos')->where('eliminado', 0)->get();

    foreach ($productos as $prod) {
        // 3. Calculamos la realidad física consultando la tabla de PIEZAS
        // Solo contamos piezas que NO estén eliminadas y que tengan material (metros > 0)
        $datosReales = DB::table('piezas')
            ->where('id_producto', $prod->id_producto)
            ->where('eliminado', 0)
            ->where('cantidad_metros_actual', '>', 0) // Ignoramos las agotadas
            ->selectRaw('COUNT(*) as total_piezas, SUM(peso_libras_actual) as total_peso')
            ->first();

        $stockReal = $datosReales->total_piezas ?? 0;
        $pesoReal  = $datosReales->total_peso ?? 0;

        // 4. Actualizamos el maestro de productos
        DB::table('productos')
            ->where('id_producto', $prod->id_producto)
            ->update([
                'stock_actual' => $stockReal,
                'peso_total_libras' => $pesoReal
            ]);

        $resultados[] = "Producto {$prod->codigo}: Stock ajustado a {$stockReal} | Peso ajustado a {$pesoReal}";
    }


    return response()->json([
        'mensaje' => '¡Sincronización completada con éxito!',
        'detalles' => $resultados
    ]);



});
