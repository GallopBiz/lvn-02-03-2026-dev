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
        $table->id();
        $table->foreignId('employee_id')->constrained('hrms_employees')->onDelete('restrict');
        $table->foreignId('employee_salary_id')->constrained('hrms_employee_salaries')->onDelete('restrict');
        $table->foreignId('basic_deduction_id')->constrained('hrms_basic_deductions')->onDelete('restrict')->nullable();;
        $table->decimal('amount', 10, 2)->default(0);
        $table->date('month'); // Stores the month of deduction
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
