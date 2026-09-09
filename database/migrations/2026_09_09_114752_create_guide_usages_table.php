<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('guide_usages', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Usuario que realizó la consulta
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cultivo seleccionado
            |--------------------------------------------------------------------------
            */
            $table->foreignId('crop_id')
                ->constrained('crops')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Problema seleccionado
            |--------------------------------------------------------------------------
            */
            $table->foreignId('problem_id')
                ->constrained('problems')
                ->restrictOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices para futuros reportes
            |--------------------------------------------------------------------------
            */
            $table->index(['user_id', 'created_at']);
            $table->index(['crop_id', 'created_at']);
            $table->index(['problem_id', 'created_at']);
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_usages');
    }
};
