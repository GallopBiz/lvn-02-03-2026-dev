<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hrms_leave_requests', function (Blueprint $table) {
            $table->decimal('cl_days_deducted', 4, 1)->nullable()->after('is_half_day');
        });

        Schema::table('hrms_employee_attendance', function (Blueprint $table) {
            $table->decimal('cl_days_deducted', 4, 1)->nullable()->after('out_time');
            $table->decimal('lwp_days', 4, 1)->nullable()->after('cl_days_deducted');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hrms_employee_leave_balances MODIFY balance DECIMAL(10,1) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE hrms_payrolls MODIFY unapproved_leaves DECIMAL(10,1) NOT NULL, MODIFY lwp_days DECIMAL(10,1) NOT NULL');
        }
    }

    public function down()
    {
        Schema::table('hrms_leave_requests', function (Blueprint $table) {
            $table->dropColumn('cl_days_deducted');
        });

        Schema::table('hrms_employee_attendance', function (Blueprint $table) {
            $table->dropColumn(['cl_days_deducted', 'lwp_days']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hrms_employee_leave_balances MODIFY balance FLOAT(10,0) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE hrms_payrolls MODIFY unapproved_leaves INT NOT NULL, MODIFY lwp_days INT NOT NULL');
        }
    }
};
