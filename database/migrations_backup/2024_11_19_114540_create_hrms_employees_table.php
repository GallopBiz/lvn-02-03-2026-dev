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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('father_name')->nullable();
            $table->string('marital_status');
            $table->string('spouse_name')->nullable();
            $table->string('contact_number');
            $table->string('email')->unique();
            $table->date('date_of_joining');
            
            // Foreign keys
            $table->foreignId('department_id')->constrained('hrms_departments')->onDelete('restrict');
            $table->foreignId('shift_id')->constrained('hrms_shifts')->onDelete('restrict');
            $table->foreignId('position_id')->constrained('hrms_positions')->onDelete('restrict');
            
            $table->string('employment_type'); // Permanent, Temporary, Guest
            $table->string(column: 'profile_picture')->nullable();
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
        Schema::dropIfExists('hrms_employees');
    }
};
