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
        Schema::disableForeignKeyConstraints();

        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('features_id');
            $table->foreign('features_id')->references('id')->on('ai_features');
            $table->text('prompt');
            $table->string('image_url');
            $table->integer('sum_like');
            $table->integer('sum_comment');
            $table->enum('privacy_status', ["public","private"]);
            $table->json('metadata');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->enum('status_image', ["processing","completed","failed"]);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
