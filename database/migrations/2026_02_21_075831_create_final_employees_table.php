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
        Schema::create('final-employees', function (Blueprint $table) {
            $table->smallInteger('EmployeeID')->nullable();
            $table->string('FirstName', 16)->nullable();
            $table->string('LastName', 13)->nullable();
            $table->string('Email', 0)->nullable();
            $table->string('Phone', 0)->nullable();
            $table->string('Address', 0)->nullable();
            $table->string('DateOfBirth', 0)->nullable();
            $table->string('JoiningDate', 0)->nullable();
            $table->string('DepartureDate', 0)->nullable();
            $table->string('DepartmentID', 14)->nullable();
            $table->string('PositionID', 0)->nullable();
            $table->smallInteger('ess_emp_code')->nullable();
            $table->smallInteger('DeviceCode')->nullable();
            $table->string('Company', 7)->nullable();
            $table->string('Location', 6)->nullable();
            $table->string('Designation', 24)->nullable();
            $table->string('Grade', 7)->nullable();
            $table->string('Team', 7)->nullable();
            $table->string('Category', 7)->nullable();
            $table->string('EmploymentType', 9)->nullable();
            $table->string('Gender', 6)->nullable();
            $table->string('DOJ', 10)->nullable();
            $table->string('DOC', 10)->nullable();
            $table->string('CardNumber', 4)->nullable();
            $table->string('ShiftRoaster', 0)->nullable();
            $table->string('Status', 8)->nullable();
            $table->string('is_delete', 0)->nullable();
            $table->string('created_at', 0)->nullable();
            $table->string('updated_at', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('final-employees');
    }
};
