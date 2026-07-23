<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Saldos mensuales
        Schema::create('saldos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEmpresa');
            $table->unsignedBigInteger('cuenta_id');
            $table->integer('anio');    // 2025
            $table->integer('mes');     // 1..12

            $table->decimal('saldo_inicial', 14, 2)->default(0);
            $table->decimal('total_debe',    14, 2)->default(0);
            $table->decimal('total_haber',   14, 2)->default(0);
            $table->decimal('saldo_final',   14, 2)->default(0);

            $table->timestamps();

            $table->unique(['idEmpresa', 'cuenta_id', 'anio', 'mes'], 'idx_saldos_emp_cta_mes');
        });

        // 2) Cierres contables
        Schema::create('cierres_contables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEmpresa');
            $table->integer('anio');
            $table->integer('mes');
            $table->boolean('cerrado')->default(false);
            $table->timestamps();

            $table->unique(['idEmpresa', 'anio', 'mes'], 'idx_cierres_emp_mes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos_mensuales');
        Schema::dropIfExists('cierres_contables');
    }
};
