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
        Schema::create('hrms_employee_student_fee_emis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_salary_id')->index('hrms_employee_student_fee_emis_employee_salary_id_foreign');
            $table->integer('student_id')->index('hrms_employee_student_fee_emis_student_id_foreign');
            $table->unsignedBigInteger('employee_child_id')->index('hrms_employee_student_fee_emis_employee_child_id_foreign');
            $table->date('emi_date');
            $table->decimal('emi_amount', 10);
            $table->enum('status', ['pending', 'paid', 'paused'])->default('pending');
            $table->decimal('paid_in_cash', 10)->nullable()->comment('Amount paid in cash if applicable');
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
        Schema::dropIfExists('hrms_employee_student_fee_emis');
    }
};
