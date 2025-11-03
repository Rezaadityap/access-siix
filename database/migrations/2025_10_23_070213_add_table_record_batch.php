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
        Schema::create('record_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_material_lines_id')->constrained('record_material_lines')->cascadeOnDelete();
            $table->string('batch_wh', 20)->nullable();
            $table->string('batch_wh_desc')->nullable();
            $table->string('qty_batch_wh', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_batch');
    }
};
