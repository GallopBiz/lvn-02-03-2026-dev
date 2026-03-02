<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrms_employee_student_fee_emis', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_child_id')->after('student_id');
            $table->foreign('employee_child_id')->references('id')->on('hrms_employee_childrens')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employee_student_fee_emis', function (Blueprint $table) {
            $table->dropColumn('employee_child_id');
        });
    }
};
