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
        Schema::create('padcast', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->time('time');
            $table->string('bulk');
            $table->unsignedBigInteger('padcastCategory_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padcast');
    }
};
