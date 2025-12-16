<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('cuentas', function (Blueprint $table) {
        $table->unsignedBigInteger('idEmpresa')->default( session('idEmpresa') ?? 1 );
    });

    Schema::table('asientos', function (Blueprint $table) {
        $table->unsignedBigInteger('idEmpresa')->default( session('idEmpresa') ?? 1 );
    });

    Schema::table('asiento_detalles', function (Blueprint $table) {
        $table->unsignedBigInteger('idEmpresa')->default( session('idEmpresa') ?? 1 );
    });
}


public function down()
{
    Schema::table('cuentas', function (Blueprint $table) {
        $table->dropColumn('idEmpresa');
    });

    Schema::table('asientos', function (Blueprint $table) {
        $table->dropColumn('idEmpresa');
    });

    Schema::table('asiento_detalles', function (Blueprint $table) {
        $table->dropColumn('idEmpresa');
    });
}

};
