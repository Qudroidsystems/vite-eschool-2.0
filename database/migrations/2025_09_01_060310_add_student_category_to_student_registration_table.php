<?php

use Illuminate\Support\Facades\Schemaation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprintprint;
use Illuminate\Database\Migrations\Migrationchema;

     return new class extends Migration
     {
         public function up(): void
         {
             Schema::table('studentRegistration', function (Blueprint $table) {
                 $table->string('student_category', 50)->nullable()->after('statusId');
             });
         }

         public function down(): void
         {
             Schema::table('studentRegistration', function (Blueprint $table) {
                 $table->dropColumn('student_category');
             });
         }
     };