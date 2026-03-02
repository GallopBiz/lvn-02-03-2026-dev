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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_id')->index('hrms_employee_loan_emis_loan_id_foreign');
            $table->date('emi_date');
            $table->decimal('emi_amount', 10);
            $table->decimal('principal_component', 10);
            $table->decimal('interest_component', 10);
            $table->enum('status', ['pending', 'paid', 'paused', 'recalculated'])->default('pending');
            $table->enum('payment_mode', ['salary', 'cash'])->nullable();
            $table->decimal('paid_amount', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->date('deleted_at')->nullable();
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
