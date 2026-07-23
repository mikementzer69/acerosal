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
Schema::create('compras', function (Blueprint $table) {
    $table->integer('id_compra')->autoIncrement(); //
    $table->integer('id_proveedor'); //
    $table->integer('id_empresa'); //
    $table->string('numero_factura', 50); //
    $table->date('fecha_ingreso'); //
    $table->date('fecha_emision_factura'); //
    $table->decimal('tasa_cambio', 10, 2); //
    $table->double('peso_total_libras'); //
    $table->double('peso_total_kg'); //
    $table->decimal('total_costos_adicionales', 10, 2); //
    $table->decimal('costos_adicionales_libra', 10, 2); //
    $table->decimal('importe_total_factura', 10, 2); //
    $table->decimal('total_factura', 10, 2); //
    $table->tinyInteger('nueva_compra')->default(0); //
    $table->tinyInteger('eliminado')->default(0); //
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
