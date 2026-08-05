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
            | Ranking de Cashback
            |--------------------------------------------------------------------------
            |
            | true  = La campaña también tendrá ranking.
            | false = Solo genera cashback.
            |
            */

            $table->boolean('ranking_enabled')
                ->default(false)
                ->after('participant_type')
                ->comment('Activa el ranking de cashback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->dropColumn('ranking_enabled');
        });
    }
};
