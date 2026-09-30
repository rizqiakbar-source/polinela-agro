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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode', 50)->unique();
            $table->string('nama', 150);
            $table->enum('tipe', ['fixed', 'persen'])->default('fixed');
            $table->decimal('diskon', 12, 2);
            $table->decimal('min_belanja', 12, 2)->default(0);
            $table->decimal('max_diskon', 12, 2)->nullable();
            $table->integer('kuota')->default(100);
            $table->integer('terpakai')->default(0);
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_berakhir')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
