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
    Schema::create('protocols', function (Blueprint $table) {
        $table->id();

        $table->string('code', 20)->unique();

        $table->foreignId('crop_id')
            ->constrained()
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->foreignId('problem_id')
            ->constrained()
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->boolean('is_active')->default(true);

        $table->timestamps();

        $table->unique(['crop_id', 'problem_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
