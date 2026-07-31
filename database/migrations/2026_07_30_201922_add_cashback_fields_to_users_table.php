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
        Schema::table('users', function (Blueprint $table) {

            $table->decimal('cashback_total', 12, 2)
                ->default(0)
                ->after('account_number')
                ->comment('Total histórico generado');

            $table->decimal('cashback_claimed', 12, 2)
                ->default(0)
                ->after('cashback_total')
                ->comment('Total canjeado');

            $table->decimal('cashback_available', 12, 2)
                ->default(0)
                ->after('cashback_claimed')
                ->comment('Saldo disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'cashback_total',
                'cashback_claimed',
                'cashback_available',
            ]);
        });
    }
};
