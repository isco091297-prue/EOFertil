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
        Schema::create('cashback_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('cashback_campaign_id')
                ->nullable()
                ->constrained('cashback_campaigns')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Movimiento
            |--------------------------------------------------------------------------
            */

            $table->string('tipo', 30)
                ->comment('factura, canje, bonificacion, ajuste, etc.');

            $table->enum('movimiento', [
                'ingreso',
                'egreso',
            ]);

            $table->decimal('valor', 12, 2);

            $table->decimal('saldo_despues', 12, 2);

            $table->text('descripcion')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('user_id');
            $table->index('invoice_id');
            $table->index('cashback_campaign_id');
            $table->index('tipo');
            $table->index('movimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashback_transactions');
    }
};
