<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {

            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 20);

            $table->string('name', 100);

            $table->string('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
            $table->unique(['warehouse_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
