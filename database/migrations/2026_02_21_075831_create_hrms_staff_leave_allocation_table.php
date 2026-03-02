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
        Schema::create('hrms_staff_leave_allocation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hrms_staff_type_id')->index('hrms_staff_leave_allocation_hrms_staff_type_id_foreign');
            $table->unsignedBigInteger('leave_type_id')->index('hrms_staff_leave_allocation_leave_type_id_foreign');
            $table->boolean('is_paid')->default(true);
            $table->integer('max_allowed')->nullable();
            $table->integer('max_per_instance')->nullable();
            $table->boolean('is_vacation')->default(false);
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
        Schema::dropIfExists('hrms_staff_leave_allocation');
    }
};
