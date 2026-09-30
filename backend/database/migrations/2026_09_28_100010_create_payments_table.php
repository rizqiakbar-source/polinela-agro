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
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('metode', 50)->default('transfer');
            $table->string('bank', 50)->nullable();
            $table->string('no_rekening_tujuan', 100)->nullable();
            $table->string('atas_nama', 100)->nullable();
            $table->string('no_transaksi', 100)->nullable();
            $table->string('bukti_bayar', 255)->nullable();
            $table->enum('status', ['pending', 'menunggu_konfirmasi', 'lunas', 'ditolak'])->default('pending');
            $table->unsignedInteger('verified_by')->nullable();
            $table->datetime('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
