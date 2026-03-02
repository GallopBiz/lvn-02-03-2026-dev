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
        Schema::create('hrms_employee_educations', function (Blueprint $table) {
            $table->id();
        $table->foreignId('employee_id')->constrained('hrms_employees')->onDelete('restrict');
        $table->string('qualification');
        $table->string('specialization')->nullable();
        $table->string('institution_name');
        $table->year('year_of_graduation');
        $table->string('certification')->nullable();
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
        Schema::dropIfExists('hrms_employee_educations');
    }
};
