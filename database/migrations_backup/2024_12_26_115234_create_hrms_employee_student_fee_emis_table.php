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
            $table->id();
            $table->unsignedBigInteger('employee_salary_id');
            $table->integer('student_id'); 
            $table->date('emi_date');
            $table->decimal('emi_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'paused'])->default('pending');
            $table->decimal('paid_in_cash', 10, 2)->nullable()->comment('Amount paid in cash if applicable');
            $table->timestamps();
            $table->softDeletes();
            // Foreign keys
            $table->foreign('employee_salary_id')->references('id')->on('hrms_employee_salaries')->onDelete('restrict');
            $table->foreign('student_id')->references('id')->on('student_registration')->onDelete('restrict');
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
