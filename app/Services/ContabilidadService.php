<?php

namespace App\Services;

use App\Models\Asiento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContabilidadService
{
    public function regenerarPeriodoAsiento(Asiento $asiento): void
    {
        $empresa = $asiento->idEmpresa;
        $fecha = Carbon::parse($asiento->fecha);

        $this->regenerarPeriodo($empresa, $fecha->year, $fecha->month);
    }

    public function regenerarPeriodo(int $idEmpresa, int $anio, int $mes): void
    {
        DB::statement('CALL generar_saldos_mensuales(?, ?, ?)', [
            $idEmpresa,
            $anio,
            $mes,
        ]);
    }
}
