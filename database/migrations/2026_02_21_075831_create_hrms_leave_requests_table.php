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
        Schema::create('hrms_leave_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_leave_requests_employee_id_foreign');
            $table->unsignedBigInteger('leave_type_id')->index('hrms_leave_requests_leave_type_id_foreign');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('is_half_day', 50)->nullable();
            $table->string('half_day_type', 50)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index('hrms_leave_requests_approved_by_foreign');
            $table->boolean('rollback_done')->nullable();
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
        Schema::dropIfExists('hrms_leave_requests');
    }
};
