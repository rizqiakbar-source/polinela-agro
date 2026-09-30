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
        Schema::create('sus_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('nama_responden', 150)->nullable();
            $table->string('role_responden', 50)->nullable();
            $table->tinyInteger('q1');
            $table->tinyInteger('q2');
            $table->tinyInteger('q3');
            $table->tinyInteger('q4');
            $table->tinyInteger('q5');
            $table->tinyInteger('q6');
            $table->tinyInteger('q7');
            $table->tinyInteger('q8');
            $table->tinyInteger('q9');
            $table->tinyInteger('q10');
            $table->decimal('total_score', 5, 2);
            $table->string('kategori', 50);
            $table->text('feedback')->nullable();
            $table->datetime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sus_responses');
    }
};
