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
Schema::create('compra_producto', function (Blueprint $table) {
    $table->integer('id_compra_producto')->autoIncrement(); //
    $table->integer('id_producto'); //
    $table->integer('id_compra'); //
    $table->integer('id_empresa'); //
    $table->decimal('cantidad', 12, 4); //
    $table->decimal('precio_kg_eu', 12, 4); //
    $table->decimal('precio_kg_usd', 12, 4); //
    $table->decimal('peso_kg', 12, 4); //
    $table->decimal('peso_libra', 12, 4); //
    $table->decimal('importe_eu', 12, 4); //
    $table->decimal('importe_dolares', 12, 4); //
    $table->tinyInteger('eliminado')->default(0); //

    // --- ESTE ES EL AMARRE QUE TE FALTABA ---
    $table->integer('id_lote')->nullable();
    $table->foreign('id_lote')->references('id_lote')->on('lotes');
    // ----------------------------------------

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_productos');
    }
};
