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
        Schema::create('cashback_campaign_scopes', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Campaña
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cashback_campaign_id')
                ->constrained('cashback_campaigns')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Alcance de la campaña
            |--------------------------------------------------------------------------
            |
            | Solo uno de estos tres campos deberá tener valor.
            |
            */

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('cashback_campaign_id');
            $table->index('warehouse_id');
            $table->index('zone_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashback_campaign_scopes');
    }
};
