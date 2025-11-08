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
            $table->string('remarks')->after('act_qty')->nullable();
            $table->dropColumn('lcr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_material_lines', function (Blueprint $table) {
            $table->integer('lcr')->after('act_qty')->nullable();
            $table->dropColumn('remarks');
        });
    }
};
