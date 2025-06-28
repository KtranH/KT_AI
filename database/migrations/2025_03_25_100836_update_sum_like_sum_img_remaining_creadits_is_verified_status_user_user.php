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
        //
        Schema::table('users', function (Blueprint $table) {
            $table->integer('sum_like')->default(0)->change();
            $table->integer('sum_img')->default(0)->change();
            $table->integer('remaining_creadits')->default(20)->change();
            $table->boolean('is_verified')->default(false)->change();
            $table->string('status_user')->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->integer('sum_like')->default(0)->change();
            $table->integer('sum_img')->default(0)->change();
            $table->integer('remaining_creadits')->default(20)->change();
            $table->boolean('is_verified')->default(true)->change();
            $table->string('status_user')->default('active')->change();
        });
    }
};
