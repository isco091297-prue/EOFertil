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
            'protocol_application_active_ingredient_products',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'protocol_application_active_ingredient_id'
                );

                $table->unsignedBigInteger('product_id');

                $table->decimal('dose', 10, 2);

                $table->string('unit', 30);

                $table->string('application_base', 50);

                $table->timestamps();

                /*
                |----------------------------------------------------------
                | Evitar producto repetido dentro del mismo ingrediente
                |----------------------------------------------------------
                */

                $table->unique(
                    [
                        'protocol_application_active_ingredient_id',
                        'product_id',
                    ],
                    'paaip_ingredient_product_unique'
                );

                /*
                |----------------------------------------------------------
                | Ingrediente activo de la aplicación
                |----------------------------------------------------------
                */

                $table->foreign(
                    'protocol_application_active_ingredient_id',
                    'paaip_ingredient_fk'
                )
                    ->references('id')
                    ->on('protocol_application_active_ingredients')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                /*
                |----------------------------------------------------------
                | Producto recomendado
                |----------------------------------------------------------
                */

                $table->foreign(
                    'product_id',
                    'paaip_product_fk'
                )
                    ->references('id')
                    ->on('products')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'protocol_application_active_ingredient_products'
        );
    }
};
