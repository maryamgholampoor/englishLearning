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
        Schema::create('word', function (Blueprint $table) {
            $table->id();
            $table->integer('word_category_id');
            $table->string('word');
            $table->string('music_path');
            $table->string('translate_word');
            $table->string('test_english');
            $table->string('test_persian');
            $table->string('test_voice');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word');
    }
};
