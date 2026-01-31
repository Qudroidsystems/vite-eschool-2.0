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
        Schema::table('schoolclass', function (Blueprint $table) {
            // 1. Change classcategoryid to allow comma-separated values + nullable
            $table->string('classcategoryid', 255)->nullable()->change();

            // 2. Make description nullable (optional field)
            $table->text('description')->nullable()->change();

            // 3. Make arm a proper foreign key (if not already)
            // First drop old column if it's string, then add unsignedBigInteger
            // (Only do this if your current 'arm' is string and NOT already a foreign key)
            // If 'arm' is already correct, comment out the next 3 lines
            // $table->dropColumn('arm');
            // $table->unsignedBigInteger('arm')->after('schoolclass');
            // $table->foreign('arm')
            //       ->references('id')
            //       ->on('schoolarm')
            //       ->onDelete('restrict'); // or 'cascade' if you prefer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schoolclass', function (Blueprint $table) {
            // Reverse changes (be careful - this will drop foreign key and change types back)
            $table->string('classcategoryid')->nullable(false)->change();
            $table->string('description')->nullable(false)->change();

            // If you added/changed arm column, reverse it here
            $table->dropForeign(['arm']);
            $table->dropColumn('arm');
            $table->string('arm')->after('schoolclass');
        });
    }
};
