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
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Aceptación de documentos legales
            |--------------------------------------------------------------------------
            */

            $table->boolean('responsibility_accepted')
                ->default(false)
                ->after('privacy_accepted_at');

            $table->timestamp('responsibility_accepted_at')
                ->nullable()
                ->after('responsibility_accepted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'responsibility_accepted',
                'responsibility_accepted_at',
            ]);
        });
    }
};
