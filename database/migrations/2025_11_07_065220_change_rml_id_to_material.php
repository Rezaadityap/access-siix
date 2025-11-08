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
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign(['record_material_lines_id']);
            $table->dropColumn('record_material_lines_id');
            $table->string('material')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->foreignId('record_material_lines_id')
                ->constrained('record_material_lines')
                ->cascadeOnDelete();
            $table->dropColumn('material');
        });
    }
};
