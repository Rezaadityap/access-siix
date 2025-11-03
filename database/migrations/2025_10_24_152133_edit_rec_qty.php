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
        Schema::table('record_material_lines', function (Blueprint $table) {
            $table->string('rec_qty')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_material_lines', function (Blueprint $table) {
            $table->integer('rec_qty')->change();
        });
    }
};
