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
        Schema::table('ai_features', function (Blueprint $table) {
            $table->dropColumn('count_img');
        });

        Schema::table('ai_features', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('prompt_template')->nullable()->change();
            $table->integer('creadit_cost')->nullable()->change();
            $table->string('thumbnail_url')->nullable()->change();
            $table->string('input_requirements')->nullable()->change();
            $table->string('category')->nullable()->change();
            $table->integer('sum_img')->nullable()->change();
            $table->integer('average_processing_time')->nullable()->change();
            $table->string('status_feature')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_features', function (Blueprint $table) {
            $table->integer('count_img')->nullable();
        });

        Schema::table('ai_features', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->text('prompt_template')->nullable(false)->change();
            $table->integer('creadit_cost')->nullable(false)->change();
            $table->string('thumbnail_url')->nullable(false)->change();
            $table->string('input_requirements')->nullable(false)->change();
            $table->string('category')->nullable(false)->change();
            $table->integer('sum_img')->nullable(false)->change();
            $table->integer('average_processing_time')->nullable(false)->change();
            $table->string('status_feature')->nullable(false)->change();
        });
    }
};
