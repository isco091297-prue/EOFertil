<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'active_ingredient_combination_product',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'active_ingredient_combination_id'
                );

                $table->unsignedBigInteger(
                    'product_id'
                );

                $table->timestamps();

                $table->unique(
                    [
                        'active_ingredient_combination_id',
                        'product_id',
                    ],
                    'aicp_unique'
                );

                $table->foreign(
                    'active_ingredient_combination_id',
                    'aicp_combination_fk'
                )
                    ->references('id')
                    ->on('active_ingredient_combinations')
                    ->cascadeOnDelete();

                $table->foreign(
                    'product_id',
                    'aicp_product_fk'
                )
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'active_ingredient_combination_product'
        );
    }
};
