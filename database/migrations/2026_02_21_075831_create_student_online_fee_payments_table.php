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
        Schema::create('student_online_fee_payments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_id');
            $table->integer('student_id')->index('fk_student_id');
            $table->string('fee_term');
            $table->string('fee_account');
            $table->decimal('fees_due', 10);
            $table->decimal('allocated_amount', 10)->nullable()->default(0);
            $table->decimal('remaining_amount', 10);
            $table->enum('status', ['pending', 'paid', 'refunded'])->nullable()->default('pending');
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_gateway_transaction_id', 150)->default('');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->integer('late_fee')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('lumsum')->default(0);
            $table->dateTime('settlement_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_online_fee_payments');
    }
};
