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
        Schema::table('images', function (Blueprint $table) {
            $table->string('title')->nullable()->default('Hiện chưa có title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
