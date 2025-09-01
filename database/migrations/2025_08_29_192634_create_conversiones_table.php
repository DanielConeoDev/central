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
        Schema::create('conversiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       // Usuario que hizo la conversión
            $table->string('producto_origen');           // Producto origen
            $table->string('producto_destino');          // Producto destino
            $table->integer('cantidad_origen');          // Cantidad descontada
            $table->integer('cantidad_destino');         // Cantidad generada
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('producto_origen')->references('codigo')->on('productos')->onDelete('cascade');
            $table->foreign('producto_destino')->references('codigo')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversiones');
    }
};
