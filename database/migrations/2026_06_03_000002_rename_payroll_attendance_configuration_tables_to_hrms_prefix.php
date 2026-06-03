<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_attendance_configuration') && !Schema::hasTable('hrms_payroll_attendance_configuration')) {
            Schema::rename('payroll_attendance_configuration', 'hrms_payroll_attendance_configuration');
        }

        if (Schema::hasTable('payroll_staff_attendance_configuration') && !Schema::hasTable('hrms_payroll_staff_attendance_configuration')) {
            Schema::rename('payroll_staff_attendance_configuration', 'hrms_payroll_staff_attendance_configuration');
        }
    }

    public function down()
    {
        if (Schema::hasTable('hrms_payroll_staff_attendance_configuration') && !Schema::hasTable('payroll_staff_attendance_configuration')) {
            Schema::rename('hrms_payroll_staff_attendance_configuration', 'payroll_staff_attendance_configuration');
        }

        if (Schema::hasTable('hrms_payroll_attendance_configuration') && !Schema::hasTable('payroll_attendance_configuration')) {
            Schema::rename('hrms_payroll_attendance_configuration', 'payroll_attendance_configuration');
        }
    }
};
