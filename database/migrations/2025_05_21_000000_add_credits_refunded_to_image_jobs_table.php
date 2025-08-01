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
        Schema::table('image_jobs', function (Blueprint $table) {
            $table->boolean('credits_refunded')->default(false)->after('result_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_jobs', function (Blueprint $table) {
            $table->dropColumn('credits_refunded');
        });
    }
}; 