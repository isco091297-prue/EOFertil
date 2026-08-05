<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->enum(
                'participant_type',
                [
                    'all',
                    'warehouse',
                    'zone',
                    'branch',
                ]
            )
                ->default('all')
                ->after('campaign_type');
        });
    }

    public function down(): void
    {
        Schema::table('cashback_campaigns', function (Blueprint $table) {

            $table->dropColumn('participant_type');
        });
    }
};
