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
    Schema::create('asientos', function (Blueprint $table) {
        $table->id();
        $table->date('fecha');
        $table->string('descripcion')->nullable();
        $table->decimal('total_debe', 15, 2)->default(0);
        $table->decimal('total_haber', 15, 2)->default(0);
        $table->boolean('activo')->default(1);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asientos');
    }
};
