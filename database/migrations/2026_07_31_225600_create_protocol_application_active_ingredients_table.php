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
            'protocol_application_active_ingredients',
            function (Blueprint $table) {

                $table->id();

                /*
                |----------------------------------------------------------
                | Aplicación del protocolo
                |----------------------------------------------------------
                */

                $table->unsignedBigInteger('protocol_application_id');

                $table->foreign(
                    'protocol_application_id',
                    'paai_application_fk'
                )
                    ->references('id')
                    ->on('protocol_applications')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                /*
                |----------------------------------------------------------
                | Ingrediente activo
                |----------------------------------------------------------
                */

                $table->unsignedBigInteger('active_ingredient_id');

                $table->foreign(
                    'active_ingredient_id',
                    'paai_ingredient_fk'
                )
                    ->references('id')
                    ->on('active_ingredients')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->timestamps();

                /*
                |----------------------------------------------------------
                | Evitar ingrediente duplicado
                |----------------------------------------------------------
                */

                $table->unique(
                    [
                        'protocol_application_id',
                        'active_ingredient_id',
                    ],
                    'paai_application_ingredient_unique'
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
            'protocol_application_active_ingredients'
        );
    }
};
