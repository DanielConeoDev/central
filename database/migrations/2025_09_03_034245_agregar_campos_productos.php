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
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('cantidad_min')->nullable()->after('convertible'); // Cantidad mínima para alertas
            $table->integer('cantidad_max')->nullable()->after('cantidad_min'); // Cantidad máxima para control de stock
            $table->string('categoria')->nullable()->after('cantidad_max');     // Para agrupar productos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['cantidad_min', 'cantidad_max', 'categoria']);
        });
    }
};
