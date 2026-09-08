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
        Schema::create('invoice_audits', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Factura afectada
            |--------------------------------------------------------------------------
            */

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Administrador que realizó la acción
            |--------------------------------------------------------------------------
            */

            $table->foreignId('admin_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Acción realizada
            |--------------------------------------------------------------------------
            |
            | aprobar
            | modificar
            | anular
            |
            */

            $table->string('accion', 30);

            /*
            |--------------------------------------------------------------------------
            | Motivo
            |--------------------------------------------------------------------------
            */

            $table->text('motivo')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado anterior y nuevo
            |--------------------------------------------------------------------------
            */

            $table->string('estado_anterior', 30)->nullable();

            $table->string('estado_nuevo', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Información completa antes/después
            |--------------------------------------------------------------------------
            |
            | Aquí podremos guardar valores como:
            | total_factura
            | total_productos_participantes
            | cashback
            | porcentaje
            | productos
            | etc.
            |
            */

            $table->json('datos_anteriores')->nullable();

            $table->json('datos_nuevos')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'invoice_id',
                'created_at',
            ]);

            $table->index([
                'admin_user_id',
                'created_at',
            ]);

            $table->index('accion');
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_audits');
    }
};
