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
Schema::create('lotes', function (Blueprint $table) {
    $table->integer('id_lote')->autoIncrement(); //
    $table->integer('id_producto'); //
    $table->integer('id_empresa'); //
    $table->string('codigo'); //
    $table->date('fecha_ingreso'); //
    $table->decimal('peso_total_libras', 12, 4); //
    $table->string('unidad_medida_peso'); //
    $table->decimal('cantidad_total_metros', 12, 4); //
    $table->string('unidad_medida_longitud'); //
    $table->decimal('relacion_cantidad_peso', 12, 4); //
    $table->integer('total_piezas'); //
    $table->tinyInteger('eliminado')->default(0); //
    $table->timestamps();
});
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
