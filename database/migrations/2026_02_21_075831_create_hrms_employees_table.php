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
        Schema::create('hrms_employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('father_name')->nullable();
            $table->string('marital_status');
            $table->string('spouse_name')->nullable();
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->date('date_of_joining');
            $table->date('confirmation_date')->nullable();
            $table->unsignedBigInteger('department_id')->index('hrms_employees_department_id_foreign');
            $table->unsignedBigInteger('shift_id')->index('hrms_employees_shift_id_foreign');
            $table->unsignedBigInteger('position_id')->index('hrms_employees_position_id_foreign');
            $table->boolean('is_vacation')->default(false);
            $table->string('profile_picture')->nullable();
            $table->decimal('gross_salary', 10)->nullable();
            $table->string('employee_status', 50)->nullable();
            $table->date('employee_status_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('staff_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employees');
    }
};
