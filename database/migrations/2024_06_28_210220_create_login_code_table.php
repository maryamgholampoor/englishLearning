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
        Schema::create('login_code', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->dateTime('expiration_time')->nullable(false);
            $table->dateTime('used_time')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_code');
    }
};
