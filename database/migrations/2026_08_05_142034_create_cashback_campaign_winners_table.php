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
        Schema::create('cashback_campaign_winners', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cashback_campaign_id')
                ->constrained('cashback_campaigns')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Resultado del ranking
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('ranking_position');

            $table->decimal('sales_total', 12, 2)->default(0);

            $table->decimal('cashback_total', 12, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Premio entregado
            |--------------------------------------------------------------------------
            */

            $table->foreignId('reward_type_id')
                ->nullable()
                ->constrained('reward_types')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('ranking_reward_id')
                ->nullable()
                ->constrained('ranking_rewards')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('reward_title')->nullable();

            $table->decimal('reward_value', 12, 2)
                ->nullable();

            $table->decimal('reward_multiplier', 6, 2)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Procesamiento
            |--------------------------------------------------------------------------
            */

            $table->timestamp('processed_at');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('cashback_campaign_id');

            $table->index('user_id');

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
        Schema::dropIfExists(
            'cashback_campaign_winners'
        );
    }
};
