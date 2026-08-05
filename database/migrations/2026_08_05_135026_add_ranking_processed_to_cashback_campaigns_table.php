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

            $table->boolean('ranking_processed')
                ->default(false)
                ->after('ranking_enabled')
                ->comment('Indica si ya se procesó el ranking de la campaña');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->dropColumn('ranking_processed');
        });
    }
};
