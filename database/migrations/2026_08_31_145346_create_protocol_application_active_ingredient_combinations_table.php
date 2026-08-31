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
        Schema::create(
            'protocol_application_active_ingredient_combinations',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'protocol_application_id'
                );

                $table->unsignedBigInteger(
                    'active_ingredient_combination_id'
                );

                $table->string('dose')->nullable();

                $table->string('unit')->nullable();

                $table->string('application_base')->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Foreign keys
                |--------------------------------------------------------------------------
                */

                $table->foreign(
                    'protocol_application_id',
                    'pa_aic_protocol_fk'
                )
                    ->references('id')
                    ->on('protocol_applications')
                    ->cascadeOnDelete();

                $table->foreign(
                    'active_ingredient_combination_id',
                    'pa_aic_combination_fk'
                )
                    ->references('id')
                    ->on('active_ingredient_combinations')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Evitar repetir la misma combinación
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'protocol_application_id',
                        'active_ingredient_combination_id',
                    ],
                    'pa_aic_unique'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'protocol_application_active_ingredient_combinations'
        );
    }
};
