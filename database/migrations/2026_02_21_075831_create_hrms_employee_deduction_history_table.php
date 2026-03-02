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
        Schema::create('hrms_employee_deduction_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_deduction_history_employee_id_foreign');
            $table->unsignedBigInteger('employee_salary_id')->index('hrms_employee_deduction_history_employee_salary_id_foreign');
            $table->unsignedBigInteger('basic_deduction_id')->index('hrms_employee_deduction_history_basic_deduction_id_foreign');
            $table->decimal('amount', 10)->default(0);
            $table->integer('month');
            $table->string('year', 120);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_deduction_history');
    }
};
