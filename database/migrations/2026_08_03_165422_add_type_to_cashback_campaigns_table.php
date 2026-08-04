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
            | Tipo de campaña
            |--------------------------------------------------------------------------
            |
            | cashback            => Genera cashback por porcentaje.
            | ranking_cashback   => Duplica (o multiplica) el cashback.
            | ranking_accumulated=> Premio por compras acumuladas.
            |
            */

            $table->string('campaign_type', 40)
                ->default('cashback')
                ->after('descripcion')
                ->comment('cashback | ranking_cashback | ranking_accumulated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->dropColumn('campaign_type');
        });
    }
};
