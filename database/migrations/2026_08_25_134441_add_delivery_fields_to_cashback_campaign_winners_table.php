<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashback_campaign_winners', function (Blueprint $table) {

            $table->boolean('reward_delivered')
                ->default(false)
                ->after('processed_at');

            $table->timestamp('reward_delivered_at')
                ->nullable()
                ->after('reward_delivered');
        });
    }

    public function down(): void
    {
        Schema::table('cashback_campaign_winners', function (Blueprint $table) {

            $table->dropColumn([
                'reward_delivered',
                'reward_delivered_at',
            ]);
        });
    }
};
