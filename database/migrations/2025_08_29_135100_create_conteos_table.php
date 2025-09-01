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
        Schema::create('conteos', function (Blueprint $table) {
            $table->id();
            $table->string('producto_codigo');       // FK a productos
            $table->integer('cantidad');             // Cantidad o stock
            $table->integer('diferencial')->nullable(); // Diferencia con el último registro
            $table->boolean('activo')->default(true);   // Último registro activo
            $table->timestamps();

            $table->foreign('producto_codigo')
                  ->references('codigo')
                  ->on('productos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conteos');
    }
};
