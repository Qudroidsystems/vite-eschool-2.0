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
        Schema::table('classcategories', function (Blueprint $table) {
            $table->boolean('is_senior')->default(false)->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classcategories', function (Blueprint $table) {
            $table->dropColumn('is_senior');
        });
    }
};
