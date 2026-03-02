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
        Schema::create('hrms_employee_attendance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ess_emp_code')->nullable();
            $table->date('log_date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable()->index('hrms_employee_attendance_employee_id_foreign');
            $table->string('status', 50)->nullable();
            $table->string('entry_type', 50)->nullable();
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
        Schema::dropIfExists('hrms_employee_attendance');
    }
};
