<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studentregistration', function (Blueprint $table) {
            // $table->string('student_category', 50)->nullable()->after('statusId');
            $table->enum('student_status', ['Active', 'Inactive'])->default('Active')->after('student_category');
        });
    }

    public function down(): void
    {
        Schema::table('studentregistration', function (Blueprint $table) {
            $table->dropColumn(['student_category', 'student_status']);
        });
    }
};