<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAdmissionDateTypeInStudentRegistrationTable extends Migration
{
    public function up()
    {
        Schema::table('studentregistration', function (Blueprint $table) {
            // Change admission_date to date type
            $table->date('admission_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('studentregistration', function (Blueprint $table) {
            $table->string('admission_date', 255)->nullable()->change();
        });
    }
}