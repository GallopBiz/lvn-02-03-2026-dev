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
        Schema::create('leave_allocation', function (Blueprint $table) {
            $table->integer('Leave_Allocation_ID', true);
            $table->integer('Staff_Type_ID')->nullable()->index('Staff_Type_ID');
            $table->text('CL_Allocation')->nullable();
            $table->text('ML_Allocation')->nullable();
            $table->text('EL_Allocation')->nullable();
            $table->text('Max_CL_At_Time')->nullable();
            $table->text('Max_EL_At_Time')->nullable();
            $table->text('Max_ML_At_Time')->nullable();
            $table->text('DepartmentName')->nullable();
            $table->text('PositionName')->nullable();
            $table->text('leave_name')->nullable();
            $table->text('short_name')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_vacation')->default(true);
            $table->integer('is_delete')->default(0)->comment('0 for none, 1 for delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_allocation');
    }
};
