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
        Schema::create('leaverequests', function (Blueprint $table) {
            $table->bigInteger('LeaveRequestID', true);
            $table->bigInteger('EmployeeID')->nullable()->default(0);
            $table->date('LeaveStartDate')->nullable();
            $table->date('LeaveEndDate')->nullable();
            $table->string('LeaveType', 200)->nullable();
            $table->string('Status', 200)->nullable();
            $table->integer('is_delete')->default(0)->comment('0 none, 1 delete');
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
        Schema::dropIfExists('leaverequests');
    }
};
