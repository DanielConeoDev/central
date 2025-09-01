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
        Schema::create('productos', function (Blueprint $table) {
            $table->string('codigo')->primary();     // Código único y PK
            $table->string('nombre');                // Nombre del producto
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->boolean('estado')->default(true);     // Activo por defecto
            $table->boolean('convertible')->nullable(); // Puede ser null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
