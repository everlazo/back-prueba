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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario autenticado
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identificador externo (por ejemplo, ID de PokéAPI)
            $table->string('external_id');

            // Datos principales del favorito
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();

            // Evitar duplicados por usuario + recurso externo
            $table->unique(['user_id', 'external_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
