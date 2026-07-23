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
    Schema::create('cuentas', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 20)->unique();
        $table->string('nombre');
        $table->enum('tipo', [
            'activo',
            'pasivo',
            'patrimonio',
            'ingreso',
            'gasto'
        ]);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->boolean('es_movimiento')->default(true);
        $table->boolean('activo')->default(true);
        $table->timestamps();

        $table->foreign('parent_id')
            ->references('id')
            ->on('cuentas')
            ->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
