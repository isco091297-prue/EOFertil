<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashback_redemptions', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Usuario que solicita el canje
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Ubicación del usuario al momento de solicitar
            |--------------------------------------------------------------------------
            |
            | Se guardan como referencia histórica.
            | Si después el usuario cambia de almacén o sucursal,
            | el canje antiguo seguirá mostrando dónde pertenecía
            | cuando realizó la solicitud.
            |
            */

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Datos del pago
            |--------------------------------------------------------------------------
            */

            $table->string('identification', 20)->nullable();

            $table->string('bank', 100)->nullable();

            $table->string('account_type', 50)->nullable();

            $table->string('account_number', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Monto
            |--------------------------------------------------------------------------
            */

            $table->decimal('monto', 12, 2);

            /*
            |--------------------------------------------------------------------------
            | Estado de la solicitud
            |--------------------------------------------------------------------------
            */

            $table->enum('estado', [
                'pendiente',
                'pagado',
                'rechazado',
            ])->default('pendiente');

            /*
            |--------------------------------------------------------------------------
            | Telegram
            |--------------------------------------------------------------------------
            */

            $table->timestamp('telegram_enviado_at')
                ->nullable();

            $table->text('telegram_error')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Administración
            |--------------------------------------------------------------------------
            */

            $table->text('observacion')
                ->nullable();

            $table->timestamp('pagado_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('user_id');
            $table->index('warehouse_id');
            $table->index('branch_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_redemptions');
    }
};
