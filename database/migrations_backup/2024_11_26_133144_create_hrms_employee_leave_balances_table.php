<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hrms_employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->float('balance', 3, 2)->default(0); // Remaining leave balance
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('employee_id')->references('id')->on('hrms_employees')->onDelete('restrict');
            $table->foreign('leave_type_id')->references('id')->on('hrms_leave_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_leave_balances');
    }
};
