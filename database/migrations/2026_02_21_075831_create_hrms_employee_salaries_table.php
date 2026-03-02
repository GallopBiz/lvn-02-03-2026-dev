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
        Schema::create('hrms_employee_salaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_salaries_employee_id_foreign');
            $table->decimal('basic_salary', 10);
            $table->decimal('allowances', 10)->default(0);
            $table->decimal('deductions', 10)->default(0);
            $table->decimal('net_salary', 10)->default(0);
            $table->string('health_insurance')->nullable();
            $table->string('retirement_benefits')->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('is_active')->default(false);
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
        Schema::dropIfExists('hrms_employee_salaries');
    }
};
