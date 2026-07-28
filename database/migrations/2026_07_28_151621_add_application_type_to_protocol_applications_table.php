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
        Schema::table('protocol_applications', function (Blueprint $table) {
            $table->string('application_type', 100)
                ->nullable()
                ->after('application_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_applications', function (Blueprint $table) {
            $table->dropColumn('application_type');
        });
    }
};
