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
        Schema::create('ai_features', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->string('description');
            $table->string('prompt_template');
            $table->integer('creadit_cost')->default(0);
            $table->integer('count_img')->default(0);
            $table->string('thumbnail_url');
            $table->json('input_requirements');
            $table->string('category');
            $table->integer('sum_img');
            $table->integer('average_processing_time');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->enum('status_feature', ["active","inactive","beta"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_features');
    }
};
