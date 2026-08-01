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
        Schema::table('protocol_application_products', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------
            | Unidad de la dosis
            |--------------------------------------------------------------
            |
            | Ejemplos:
            | cc
            | gr
            | ml
            |
            */

            $table->string('unit', 30)
                ->after('dose');

            /*
            |--------------------------------------------------------------
            | Base de aplicación
            |--------------------------------------------------------------
            |
            | Ejemplos:
            | litro
            | tanque
            |
            */

            $table->string('application_base', 50)
                ->after('unit');

            /*
            |--------------------------------------------------------------
            | Observaciones
            |--------------------------------------------------------------
            |
            | Ya no serán utilizadas.
            |
            */

            $table->dropColumn('observations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_application_products', function (Blueprint $table) {

            $table->text('observations')
                ->nullable();

            $table->dropColumn([
                'unit',
                'application_base',
            ]);
        });
    }
};
