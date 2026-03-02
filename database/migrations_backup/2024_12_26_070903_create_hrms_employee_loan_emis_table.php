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
        Schema::create('hrms_employee_loan_emis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->date('emi_date');
            $table->decimal('emi_amount', 10, 2);
            $table->decimal('principal_component', 10, 2);
            $table->decimal('interest_component', 10, 2);
            $table->enum('status', ['pending', 'paid', 'paused'])->default('pending');
            $table->enum('payment_mode', ['salary', 'cash'])->nullable(); // Payment mode
            $table->decimal('paid_amount', 10, 2)->nullable(); // Actual amount paid (if in cash)
            $table->text('notes')->nullable(); // Reason for pause or comments
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('hrms_employee_loans')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_employee_loan_emis');
    }
};
