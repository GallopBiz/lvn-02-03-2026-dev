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
        Schema::create('employee_thumb_attendance', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ess_emp_code');
            $table->date('log_date');
            $table->dateTime('in_time');
            $table->dateTime('out_time');

            $table->unique(['ess_emp_code', 'log_date'], 'unique_user_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_thumb_attendance');
    }
};
