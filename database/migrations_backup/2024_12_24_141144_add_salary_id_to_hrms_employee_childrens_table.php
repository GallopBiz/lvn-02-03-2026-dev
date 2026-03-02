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
        Schema::table('hrms_employee_childrens', function (Blueprint $table) {
            $table->foreignId('employee_salary_id')->constrained('hrms_employee_salaries')->onDelete('restrict')->after('is_active');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employee_childrens', function (Blueprint $table) {
            $table->dropColumn('employee_salary_id');

        });
    }
};
