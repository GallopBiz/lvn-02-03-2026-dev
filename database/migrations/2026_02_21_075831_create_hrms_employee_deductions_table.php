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
        Schema::create('hrms_employee_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_deductions_employee_id_foreign');
            $table->unsignedBigInteger('basic_deduction_id')->index('hrms_employee_deductions_basic_deduction_id_foreign');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('employee_salary_id')->index('hrms_employee_deductions_employee_salary_id_foreign');
            $table->integer('manual_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_deductions');
    }
};
