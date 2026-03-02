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
        Schema::create('hrms_employee_loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id')->index('hrms_employee_loans_employee_id_foreign');
            $table->decimal('loan_amount', 10)->default(0);
            $table->decimal('emi_amount', 10)->default(0);
            $table->decimal('interest_rate', 5)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('duration_months');
            $table->integer('original_duration_months');
            $table->string('payment_mode', 50)->nullable();
            $table->string('refund_amount', 50)->nullable();
            $table->date('refund_date')->nullable();
            $table->string('status', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_loans');
    }
};
