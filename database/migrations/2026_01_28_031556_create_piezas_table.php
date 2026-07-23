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
Schema::create('piezas', function (Blueprint $table) {
    $table->integer('id_pieza')->autoIncrement(); //
    $table->integer('id_producto'); //
    $table->integer('id_empresa'); //
    $table->integer('id_lote'); //
    $table->string('codigo'); //
    $table->decimal('peso_libras_inicial', 12, 4); //
    $table->decimal('cantidad_metros_inicial', 12, 4); //
    $table->decimal('peso_libras_actual', 12, 4); //
    $table->decimal('cantidad_metros_actual', 12, 4); //
    $table->decimal('peso_libras_recortados', 12, 4); //
    $table->string('estado'); //
    $table->decimal('cantidad_metros_recortados', 12, 4); //
    $table->tinyInteger('retirado')->default(0); //
    $table->tinyInteger('finalizado')->default(0); //
    $table->tinyInteger('eliminado')->default(0); //
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piezas');
    }
};
