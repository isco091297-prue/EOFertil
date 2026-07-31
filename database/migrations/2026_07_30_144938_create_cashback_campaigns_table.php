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
        Schema::create('cashback_campaigns', function (Blueprint $table) {
            $table->id();

            // Información general
            $table->string('nombre');
            $table->text('descripcion')->nullable();

            // Configuración del cashback
            $table->decimal('porcentaje', 5, 2)->comment('Porcentaje de cashback (Ej: 1.50 = 1.5%)');
            $table->decimal('valor_alerta_factura', 10, 2)->comment('Valor a partir del cual la factura requiere revisión');

            // Vigencia de la campaña
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // Estado
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashback_campaigns');
    }
};
