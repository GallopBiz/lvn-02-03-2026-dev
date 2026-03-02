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
        Schema::create('employees', function (Blueprint $table) {
            $table->bigInteger('EmployeeID', true);
            $table->string('FirstName', 200)->nullable();
            $table->string('LastName', 200)->nullable();
            $table->text('Father_Name')->nullable();
            $table->string('Email', 200)->nullable();
            $table->text('Phone')->nullable();
            $table->text('mobile')->nullable();
            $table->string('Address', 200)->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->date('JoiningDate')->nullable();
            $table->date('DepartureDate')->nullable();
            $table->bigInteger('DepartmentID')->nullable()->default(0);
            $table->text('PositionID')->nullable()->default('0');
            $table->text('ess_emp_code')->nullable();
            $table->text('DeviceCode')->nullable();
            $table->text('Company')->nullable();
            $table->text('Location')->nullable();
            $table->text('Designation')->nullable();
            $table->text('Grade')->nullable();
            $table->text('Team')->nullable();
            $table->text('Category')->nullable();
            $table->text('EmploymentType')->nullable();
            $table->text('Gender')->nullable();
            $table->date('DOJ')->nullable();
            $table->date('DOC')->nullable();
            $table->text('CardNumber')->nullable();
            $table->text('ShiftRoaster')->nullable();
            $table->text('Status')->nullable();
            $table->text('City')->nullable();
            $table->text('Taluka')->nullable();
            $table->text('District')->nullable();
            $table->text('PIN_Code')->nullable();
            $table->text('Experience')->nullable();
            $table->text('Educational_Qualification')->nullable();
            $table->text('Previous_Employer')->nullable();
            $table->text('Prevoius_Designation')->nullable();
            $table->text('Previous_Salary')->nullable();
            $table->text('Duration')->nullable();
            $table->text('Maratial_Status')->nullable();
            $table->text('Spouse_Name')->nullable();
            $table->text('Spouse_Contact_No')->nullable();
            $table->text('No_of_Childern')->nullable();
            $table->text('Serial_No')->nullable();
            $table->text('Joining_Designation')->nullable();
            $table->text('Joining_Grade')->nullable();
            $table->text('Current_Grade')->nullable();
            $table->text('Working_Shift')->nullable();
            $table->text('Default_In_Time')->nullable();
            $table->text('Default_Out_Time')->nullable();
            $table->text('Default_Total_Time')->nullable();
            $table->text('Bank_Name')->nullable();
            $table->text('Bank_Branch_Name')->nullable();
            $table->text('Account_No')->nullable();
            $table->text('Do_Not_Apply_EPF_Limit')->nullable();
            $table->text('ESI_No')->nullable();
            $table->text('PAN_No')->nullable();
            $table->text('Ward_Study_in_Institute')->nullable();
            $table->text('Is_Discontinued')->nullable();
            $table->text('Leaving_Date')->nullable();
            $table->text('Adhar_CardNo')->nullable();
            $table->text('UAN_No')->nullable();
            $table->text('IFSCCODE')->nullable();
            $table->text('AyushmanNo')->nullable();
            $table->text('IsFirstDoseVaccinated')->nullable();
            $table->text('FirstDoseVaccinatedDate')->nullable();
            $table->text('IsSecondDoseVaccinated')->nullable();
            $table->text('SecondDoseVaccinatedDate')->nullable();
            $table->text('IsBoosterDoseVaccinated')->nullable();
            $table->text('BoosterDoseVaccinatedDate')->nullable();
            $table->text('samagra_id')->nullable();
            $table->text('Police_Verification')->nullable();
            $table->text('EmpName_as_on_Adhaar')->nullable();
            $table->text('PersonalEmail')->nullable();
            $table->text('staff_type')->nullable();
            $table->text('shift_type')->nullable();
            $table->text('is_vacatation_staff')->nullable()->default('0');
            $table->integer('is_delete')->default(0)->comment('0 for none, 1 for delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
