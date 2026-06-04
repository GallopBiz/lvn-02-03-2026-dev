<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('hrms_employee_leave_balances')) {
            DB::statement('ALTER TABLE hrms_employee_leave_balances MODIFY balance DECIMAL(8,2) NOT NULL DEFAULT 0');
        }
    }

    public function down()
    {
        if (Schema::hasTable('hrms_employee_leave_balances')) {
            DB::statement('ALTER TABLE hrms_employee_leave_balances MODIFY balance FLOAT(10,0) NOT NULL DEFAULT 0');
        }
    }
};
