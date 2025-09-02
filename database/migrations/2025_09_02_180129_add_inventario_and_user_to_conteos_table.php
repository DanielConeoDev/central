<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conteos', function (Blueprint $table) {
            $table->boolean('inventario')->default(false)->after('activo');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('conteos', function (Blueprint $table) {
            $table->dropColumn('inventario');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
