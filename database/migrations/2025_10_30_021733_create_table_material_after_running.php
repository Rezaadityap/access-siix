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
        Schema::create('record_batch_mar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_material_lines_id')->constrained('record_material_lines')->cascadeOnDelete();
            $table->string('batch_mar', 20);
            $table->string('batch_mar_desc');
            $table->string('qty_batch_mar', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_batch_mar');
    }
};
