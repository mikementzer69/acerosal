<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoMensual extends Model
{
    protected $table = 'saldos_mensuales';

    protected $fillable = [
        'idEmpresa',
        'cuenta_id',
        'anio',
        'mes',
        'saldo_inicial',
        'total_debe',
        'total_haber',
        'saldo_final',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
