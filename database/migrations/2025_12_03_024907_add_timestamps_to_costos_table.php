<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('costos', function (Blueprint $table) {
            // Agregar timestamps SOLO si no existen
            if (!Schema::hasColumn('costos', 'created_at')) {
                $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            }

            if (!Schema::hasColumn('costos', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            }
        });
    }

    public function down(): void
    {
        Schema::table('costos', function (Blueprint $table) {
            if (Schema::hasColumn('costos', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('costos', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
