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
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Tipo de cálculo del ranking
            |--------------------------------------------------------------------------
            |
            | cashback = Mayor cashback generado.
            | sales    = Mayor valor comprado.
            |
            */

            $table->enum(
                'ranking_type',
                [
                    'cashback',
                    'sales',
                ]
            )->default('cashback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->dropColumn('ranking_type');
        });
    }
};
