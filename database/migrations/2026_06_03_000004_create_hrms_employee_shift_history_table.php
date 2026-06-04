<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hrms_employee_shift_history')) {
            Schema::create('hrms_employee_shift_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('shift_id');
                $table->date('effective_from');
                $table->date('effective_to');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['employee_id', 'effective_from', 'effective_to'], 'hrms_shift_hist_emp_date_idx');
                $table->index('shift_id', 'hrms_shift_hist_shift_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hrms_employee_shift_history');
    }
};
