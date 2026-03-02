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
            $table->id();
            $table->foreignId('hrms_staff_type_id')->constrained('hrms_staff_type')->onDelete('restrict');
            $table->foreignId('leave_type_id')->constrained('hrms_leave_types')->onDelete('restrict');
            $table->boolean('is_paid')->default(true); // Indicates if leave is paid
            $table->integer('max_allowed')->nullable(); // Max days allowed annually
            $table->integer('max_per_instance')->nullable(); // Max days allowed per leave
            $table->boolean('is_vacation')->default(false); // Indicates if leave is paid
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
