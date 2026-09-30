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
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_unit', 150);
            $table->string('slug', 150)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('pj_nama', 150)->nullable();
            $table->string('kontak', 50)->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add foreign key constraint to users.unit_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });
        Schema::dropIfExists('units');
    }
};
