<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CierreContable extends Model
{
    protected $table = 'cierres_contables';

    protected $fillable = [
        'idEmpresa',
        'anio',
        'mes',
        'cerrado',
    ];

    public static function estaCerrado(int $idEmpresa, \Carbon\Carbon|string $fecha): bool
    {
        $fecha = \Carbon\Carbon::parse($fecha);

        return static::where('idEmpresa', $idEmpresa)
            ->where('anio', $fecha->year)
            ->where('mes', $fecha->month)
            ->where('cerrado', true)
            ->exists();
    }
}
