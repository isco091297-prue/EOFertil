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
        Schema::create('campaign_user_rankings', function (Blueprint $table) {

            $table->id();

            $table->foreignId('cashback_campaign_id')
                ->constrained('cashback_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('zone_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Acumulados
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'sales_total',
                15,
                2
            )->default(0);

            $table->decimal(
                'cashback_total',
                15,
                2
            )->default(0);

            $table->unsignedInteger(
                'invoice_count'
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Ranking
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'position'
            )->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Un usuario solo puede existir una vez por campaña
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'cashback_campaign_id',
                'user_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'campaign_user_rankings'
        );
    }
};
