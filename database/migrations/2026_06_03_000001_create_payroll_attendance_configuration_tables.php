<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hrms_payroll_attendance_configuration')) {
            Schema::create('hrms_payroll_attendance_configuration', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('month_number')->unique();
                $table->boolean('biometric_required')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('hrms_payroll_staff_attendance_configuration')) {
            Schema::create('hrms_payroll_staff_attendance_configuration', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_type_id')->unique();
                $table->boolean('biometric_required')->default(true);
                $table->timestamps();
                $table->index('staff_type_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hrms_payroll_staff_attendance_configuration');
        Schema::dropIfExists('hrms_payroll_attendance_configuration');
    }
};
