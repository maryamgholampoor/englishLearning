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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number', 11)->nullable(false);
            $table->tinyInteger('user_status')->nullable(false)->default(0);
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('sex',['female','male'])->nullable();
            $table->string('profile_pic')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
