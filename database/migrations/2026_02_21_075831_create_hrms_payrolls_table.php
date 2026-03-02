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
        Schema::create('hrms_payrolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_payrolls_employee_id_foreign');
            $table->decimal('gross_salary', 10);
            $table->decimal('basic_salary', 10)->nullable();
            $table->decimal('net_salary', 10);
            $table->json('deductions')->nullable();
            $table->decimal('hra', 10)->nullable();
            $table->decimal('da', 10)->nullable();
            $table->string('earn_salary', 25)->nullable();
            $table->float('epfa', 10)->nullable();
            $table->float('esic', 10)->nullable();
            $table->integer('approved_leaves');
            $table->integer('unapproved_leaves');
            $table->integer('lwp_days');
            $table->decimal('late_deduction_amount', 10)->nullable();
            $table->string('month');
            $table->string('year');
            $table->date('generated_at');
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
        Schema::dropIfExists('hrms_payrolls');
    }
};
