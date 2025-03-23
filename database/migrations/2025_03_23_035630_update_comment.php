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
        Schema::table('comments', function (Blueprint $table) {
            $table->integer('sum_like')->nullable();
            $table->json('list_like')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('sum_like');
            $table->dropColumn('list_like');
        });
    }
};
