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
            $table->unsignedInteger('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('feature_id');
            $table->foreign("feature_id")->references("id")->on("ai_features")->onDelete("cascade");
            $table->text('prompt');
            $table->integer('width')->default(512);
            $table->integer('height')->default(768);
            $table->bigInteger('seed');
            $table->string('style')->nullable();
            $table->string('main_image')->nullable();
            $table->string('secondary_image')->nullable();
            $table->string('result_image')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('comfy_prompt_id')->nullable();
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