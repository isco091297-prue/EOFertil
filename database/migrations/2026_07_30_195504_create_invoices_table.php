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
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cashback_campaign_id')
                ->constrained('cashback_campaigns')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Información de la factura
            |--------------------------------------------------------------------------
            */

            $table->string('numero_factura_original', 100);

            $table->string('numero_factura_normalizado', 100);

            $table->date('fecha_factura');

            /*
            |--------------------------------------------------------------------------
            | Totales
            |--------------------------------------------------------------------------
            */

            $table->decimal('total_factura', 12, 2)
                ->comment('Valor total de la factura');

            $table->decimal('total_productos_participantes', 12, 2)
                ->comment('Total de productos que participan en el cashback');

            $table->decimal('porcentaje_cashback', 5, 2)
                ->comment('Porcentaje aplicado al registrar la factura');

            $table->decimal('cashback_generado', 12, 2)
                ->comment('Valor de cashback generado');

            /*
            |--------------------------------------------------------------------------
            | Evidencia
            |--------------------------------------------------------------------------
            */

            $table->string('foto_factura')->nullable();

            $table->json('ocr_result')->nullable()
                ->comment('Respuesta original del OCR');

            /*
            |--------------------------------------------------------------------------
            | Registro
            |--------------------------------------------------------------------------
            */

            $table->enum('origen', [
                'manual',
                'ocr',
            ])->default('manual');

            $table->enum('estado', [
                'borrador',
                'procesando',
                'confirmada',
                'anulada',
            ])->default('borrador');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('cashback_campaign_id');
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('fecha_factura');
            $table->index('estado');

            /*
            |--------------------------------------------------------------------------
            | Restricción
            |--------------------------------------------------------------------------
            | No permitir el mismo número de factura
            | dentro de la misma sucursal.
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['branch_id', 'numero_factura_normalizado'],
                'invoice_branch_number_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
