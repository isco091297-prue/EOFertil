<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {

            if (Schema::hasColumn('zones', 'warehouse_id')) {

                $table->dropForeign(['warehouse_id']);

                $table->dropUnique(['warehouse_id', 'code']);

                $table->dropUnique(['warehouse_id', 'name']);

                $table->dropColumn('warehouse_id');
            }
        });

        DB::statement('ALTER TABLE zones ADD UNIQUE (code)');
        DB::statement('ALTER TABLE zones ADD UNIQUE (name)');
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {

            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('id');
        });

        Schema::table('zones', function (Blueprint $table) {

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->dropUnique(['code']);
            $table->dropUnique(['name']);

            $table->unique(['warehouse_id', 'code']);
            $table->unique(['warehouse_id', 'name']);
        });
    }
};
