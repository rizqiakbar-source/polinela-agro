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
        Schema::create('shippings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('kurir', 100);
            $table->string('layanan', 100)->nullable();
            $table->string('no_resi', 100)->nullable();
            $table->decimal('ongkir', 12, 2)->default(0);
            $table->string('estimasi', 50)->nullable();
            $table->string('penerima_nama', 150);
            $table->string('penerima_telepon', 25);
            $table->text('alamat_lengkap');
            $table->string('kota', 100);
            $table->string('kode_pos', 10)->nullable();
            $table->enum('status', ['pending', 'dikemas', 'dikirim', 'sampai'])->default('pending');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
