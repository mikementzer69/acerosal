<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordenes_despacho_detalle', function (Blueprint $table) {
            $table->string('medida_solicitada', 200)->nullable();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->string('medida_solicitada', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_despacho_detalle', function (Blueprint $table) {
            $table->dropColumn('medida_solicitada');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropColumn('medida_solicitada');
        });
    }
};
