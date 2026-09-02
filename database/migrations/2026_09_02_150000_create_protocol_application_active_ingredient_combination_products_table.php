<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'protocol_application_active_ingredient_combination_products',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger(
                    'protocol_application_active_ingredient_combination_id'
                );

                $table->unsignedBigInteger('product_id');

                $table->decimal('dose', 10, 2);
                $table->string('unit', 30);
                $table->string('application_base', 50);

                $table->timestamps();

                $table->unique(
                    [
                        'protocol_application_active_ingredient_combination_id',
                        'product_id',
                    ],
                    'paaicp_combination_product_unique'
                );

                $table->foreign(
                    'protocol_application_active_ingredient_combination_id',
                    'paaicp_combination_fk'
                )
                    ->references('id')
                    ->on('protocol_application_active_ingredient_combinations')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign(
                    'product_id',
                    'paaicp_product_fk'
                )
                    ->references('id')
                    ->on('products')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'protocol_application_active_ingredient_combination_products'
        );
    }
};
