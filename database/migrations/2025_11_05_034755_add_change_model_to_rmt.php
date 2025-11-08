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
        Schema::table('record_material_trans', function (Blueprint $table) {
            $table->integer('change_model')->after('cavity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_material_trans', function (Blueprint $table) {
            $table->dropColumn('change_model');
        });
    }
};
