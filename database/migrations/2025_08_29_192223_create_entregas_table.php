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
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');      // Usuario que entregó
            $table->string('factura');                  // Factura referenciada
            $table->enum('tipo_entrega', ['total', 'parcial']); // Tipo de entrega
            $table->enum('estado_entrega', ['iniciada', 'finalizada']); // Estado de entrega
            $table->string('referencia')->nullable();
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
