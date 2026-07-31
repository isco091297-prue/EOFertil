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
        Schema::create('ranking_rewards', function (Blueprint $table) {

            $table->id();

            // Campaña de cashback
            $table->foreignId('cashback_campaign_id')
                ->constrained('cashback_campaigns')
                ->cascadeOnDelete();

            // Posición del ranking
            $table->unsignedTinyInteger('posicion');

            // Tipo de premio
            $table->foreignId('reward_type_id')
                ->constrained('reward_types')
                ->restrictOnDelete();

            // Nombre del premio
            $table->string('titulo');

            // Descripción opcional
            $table->text('descripcion')->nullable();

            // Valor económico referencial
            $table->decimal('valor_referencial', 10, 2)
                ->nullable();

            // Multiplicador cuando el premio es cashback
            $table->decimal('multiplicador', 5, 2)
                ->nullable();

            // Estado
            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            // Una posición solo puede tener un premio por campaña
            $table->unique([
                'cashback_campaign_id',
                'posicion'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_rewards');
    }
};
