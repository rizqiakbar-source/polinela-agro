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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('unit_id');
            $table->unsignedInteger('category_id');
            $table->string('nama_produk', 200);
            $table->string('slug', 220)->unique();
            $table->longText('deskripsi')->nullable();
            $table->decimal('harga', 12, 2)->default(0);
            $table->integer('berat_gram')->default(500);
            $table->string('satuan', 50)->default('pack');
            $table->integer('stok')->default(0);
            $table->integer('stok_min')->default(5);
            $table->string('gambar_utama', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->boolean('featured')->default(false);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('total_terjual')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
