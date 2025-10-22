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
        Schema::create('record_material_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_material_trans_id')->constrained('record_material_trans');
            $table->string('po_item', 50);
            $table->string('material', 25);
            $table->string('material_desc', 70);
            $table->integer('rec_qty');
            $table->integer('act_qty');
            $table->integer('lcr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_material_lines');
    }
};
