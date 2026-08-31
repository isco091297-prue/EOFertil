<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'active_ingredient_combination_ingredient',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'active_ingredient_combination_id'
                );

                $table->unsignedBigInteger(
                    'active_ingredient_id'
                );

                $table->timestamps();

                $table->unique(
                    [
                        'active_ingredient_combination_id',
                        'active_ingredient_id',
                    ],
                    'aici_unique'
                );

                $table->foreign(
                    'active_ingredient_combination_id',
                    'aici_combination_fk'
                )
                    ->references('id')
                    ->on('active_ingredient_combinations')
                    ->cascadeOnDelete();

                $table->foreign(
                    'active_ingredient_id',
                    'aici_ingredient_fk'
                )
                    ->references('id')
                    ->on('active_ingredients')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'active_ingredient_combination_ingredient'
        );
    }
};
