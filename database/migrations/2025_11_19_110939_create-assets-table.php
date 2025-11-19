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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name')->nullable();
            $table->string('asset_tag');
            $table->string('serial');
            $table->string('model');
            $table->string('category');
            $table->string('location');
            $table->string('sap_asset_number')->nullable();
            $table->string('hostname')->nullable();
            $table->integer('user_owner')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
