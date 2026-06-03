<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hrms_payroll_department_attendance_configuration')) {
            Schema::create('hrms_payroll_department_attendance_configuration', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->unique();
                $table->boolean('biometric_required')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hrms_payroll_department_attendance_configuration');
    }
};
