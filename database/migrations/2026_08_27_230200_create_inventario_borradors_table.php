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
        Schema::create('inventario_borradores', function (Blueprint $table) {
            $table->id('id_borrador');
            $table->integer('id_compra');
            $table->integer('id_usuario');
            $table->integer('id_empresa');
            $table->longText('datos_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_borradores');
    }
};
