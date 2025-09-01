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
        Schema::create('descargos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');   // Usuario que descargó
            $table->string('producto_codigo');
            $table->integer('cantidad');
            $table->text('motivo')->nullable();      // Ejemplo: mercancía dañada
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('producto_codigo')->references('codigo')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descargos');
    }
};
