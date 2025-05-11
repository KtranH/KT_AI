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
        Schema::create('image_jobs', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('prompt'); 
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->string('result_path')->nullable(); 
            $table->text('error_message')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_jobs');
    }
};
