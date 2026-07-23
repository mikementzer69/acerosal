<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Asiento;
use App\Models\AsientoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoContabilidadController extends Controller
{
    public function generarDemo()
    {
        $nuevasCuentas = 0;
        $nuevosAsientos = 0;

        DB::transaction(function () use (&$nuevasCuentas, &$nuevosAsientos) {

            // 1) Crear cuentas contables demo SOLO si no hay ninguna
            if (Cuenta::count() == 0) {

                $cuentasDemo = [
                    // ACTIVOS
                    ['codigo' => '1101', 'nombre' => 'Caja General',                    'tipo' => 'activo'],
                    ['codigo' => '1102', 'nombre' => 'Banco Agrícola',                  'tipo' => 'activo'],
                    ['codigo' => '1103', 'nombre' => 'Banco Cuscatlán',                 'tipo' => 'activo'],
                    ['codigo' => '1201', 'nombre' => 'Clientes Nacionales',             'tipo' => 'activo'],
                    ['codigo' => '1301', 'nombre' => 'Inventarios Mercadería',          'tipo' => 'activo'],

                    // PASIVOS
                    ['codigo' => '2101', 'nombre' => 'Proveedores Nacionales',          'tipo' => 'pasivo'],
                    ['codigo' => '2201', 'nombre' => 'Préstamos Bancarios',             'tipo' => 'pasivo'],
                    ['codigo' => '2301', 'nombre' => 'IVA por Pagar',                   'tipo' => 'pasivo'],

                    // PATRIMONIO
                    ['codigo' => '3101', 'nombre' => 'Capital Social',                  'tipo' => 'patrimonio'],

                    // INGRESOS
                    ['codigo' => '4101', 'nombre' => 'Ventas Locales',                  'tipo' => 'ingreso'],
                    ['codigo' => '4102', 'nombre' => 'Servicios Profesionales',         'tipo' => 'ingreso'],

                    // GASTOS
                    ['codigo' => '5101', 'nombre' => 'Gastos Administrativos',          'tipo' => 'gasto'],
                    ['codigo' => '5102', 'nombre' => 'Gastos de Venta',                 'tipo' => 'gasto'],
                    ['codigo' => '5103', 'nombre' => 'Alquiler de Oficina',             'tipo' => 'gasto'],
                    ['codigo' => '5104', 'nombre' => 'Servicios Básicos',               'tipo' => 'gasto'],
                ];

                foreach ($cuentasDemo as $c) {
                    Cuenta::create([
                        'codigo'        => $c['codigo'],
                        'nombre'        => $c['nombre'],
                        'tipo'          => $c['tipo'],
                        'parent_id'     => null,
                        'es_movimiento' => 1,
                        'activo'        => 1,
                    ]);
                    $nuevasCuentas++;
                }
            }

            // 2) Crear asientos demo usando cuentas existentes
            $cuentasActivosGastos  = Cuenta::whereIn('tipo', ['activo', 'gasto'])->get();
            $cuentasPasivoIngPatri = Cuenta::whereIn('tipo', ['pasivo', 'ingreso', 'patrimonio'])->get();

            // Si no hay suficientes cuentas, salimos
            if ($cuentasActivosGastos->count() == 0 || $cuentasPasivoIngPatri->count() == 0) {
                return;
            }

            // Crear, por ejemplo, 20 asientos demo
            for ($i = 1; $i <= 20; $i++) {

                $monto = rand(1000, 100000) / 100; // 10.00 a 1000.00

                $fecha = Carbon::now()->subDays(rand(0, 30))->format('Y-m-d');

                // Crear asiento
                $asiento = Asiento::create([
                    'fecha'        => $fecha,
                    'descripcion'  => 'Asiento demo #' . $i,
                    'total_debe'   => $monto,
                    'total_haber'  => $monto,
                    'activo'       => 1,
                ]);

                // Línea DEBE (activo o gasto)
                $cuentaDebe = $cuentasActivosGastos->random();
                AsientoDetalle::create([
                    'asiento_id'  => $asiento->id,
                    'cuenta_id'   => $cuentaDebe->id,
                    'descripcion' => 'Línea debe demo',
                    'debe'        => $monto,
                    'haber'       => 0,
                ]);

                // Línea HABER (pasivo, ingreso o patrimonio)
                $cuentaHaber = $cuentasPasivoIngPatri->random();
                AsientoDetalle::create([
                    'asiento_id'  => $asiento->id,
                    'cuenta_id'   => $cuentaHaber->id,
                    'descripcion' => 'Línea haber demo',
                    'debe'        => 0,
                    'haber'       => $monto,
                ]);

                $nuevosAsientos++;
            }
        });

        return redirect()
            ->route('asientos.index')
            ->with('msg', "Se generaron $nuevasCuentas cuentas demo y $nuevosAsientos asientos contables demo.");
    }
}
