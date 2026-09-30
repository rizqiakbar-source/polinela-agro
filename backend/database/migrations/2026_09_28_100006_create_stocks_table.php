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
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->enum('tipe', ['masuk', 'keluar', 'penyesuaian'])->default('masuk');
            $table->integer('qty');
            $table->integer('sisa_stok');
            $table->string('keterangan', 255)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->datetime('created_at')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
