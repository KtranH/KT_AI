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

        Schema::create('interactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('image_id');
            $table->foreign('image_id')->references('id')->on('images');
            $table->enum('type_interaction', ["like","save","report"]);
            $table->text('content');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->enum('status_interaction', ["active","hidden","resolved"]);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
