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
        Schema::create('student_fees_receipt_breakdown', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('challan_id')->index('challan_id');
            $table->string('fee_head');
            $table->integer('term_number')->nullable();
            $table->decimal('total_fee', 10)->default(0);
            $table->decimal('discount', 10)->nullable()->default(0);
            $table->decimal('net_total', 10);
            $table->decimal('allocated_amount', 10);
            $table->decimal('balance_amount', 10);
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fees_receipt_breakdown');
    }
};
