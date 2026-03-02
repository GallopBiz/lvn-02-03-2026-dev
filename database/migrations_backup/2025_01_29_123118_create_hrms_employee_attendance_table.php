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
            $table->id(); 
            $table->string('ess_emp_code')->nullable();
            $table->date('log_date');
            $table->time('in_time');
            $table->time('out_time')->nullable();;
            $table->foreignId('employee_id')->constrained('hrms_employees')->onDelete('restrict');
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
        Schema::table('hrms_employee_attendance', function (Blueprint $table) {
            Schema::dropIfExists('employee_thumb_attendance');  
        });
    }
};
