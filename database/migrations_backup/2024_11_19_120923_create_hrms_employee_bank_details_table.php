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
        Schema::create('hrms_employee_bank_details', function (Blueprint $table) {
            $table->id();
        $table->foreignId('employee_id')->constrained('hrms_employees')->onDelete('restrict');
        $table->string('bank_name');
        $table->string('account_number')->unique();
        $table->string('ifsc_code');
        $table->string('branch_name');
        $table->string('pan_number')->nullable();
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
        Schema::dropIfExists('hrms_employee_bank_details');
    }
};
