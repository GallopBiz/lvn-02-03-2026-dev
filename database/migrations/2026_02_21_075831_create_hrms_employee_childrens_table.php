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
        Schema::create('hrms_employee_childrens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_childrens_employee_id_foreign');
            $table->integer('student_id')->index('hrms_employee_childrens_student_id_foreign');
            $table->decimal('fee_amount', 10);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('employee_salary_id')->index('hrms_employee_childrens_employee_salary_id_foreign');
            $table->decimal('emi_amount', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_childrens');
    }
};
