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
    Schema::create('asiento_detalles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('asiento_id')->constrained('asientos')->onDelete('cascade');
        $table->foreignId('cuenta_id')->constrained('cuentas')->onDelete('restrict');
        $table->string('descripcion')->nullable();
        $table->decimal('debe', 15, 2)->default(0);
        $table->decimal('haber', 15, 2)->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asiento_detalles');
    }
};
