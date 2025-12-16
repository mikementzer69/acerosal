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
    Schema::create('meses_cerrados', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('idEmpresa');
        $table->integer('anio');
        $table->integer('mes');
        $table->timestamp('cerrado_en')->nullable();
        $table->timestamps();

        $table->unique(['idEmpresa','anio','mes']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meses_cerrados');
    }
};
