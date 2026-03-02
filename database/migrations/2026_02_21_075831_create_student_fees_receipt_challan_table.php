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
        Schema::create('student_fees_receipt_challan', function (Blueprint $table) {
            $table->integer('challan_id', true);
            $table->integer('student_id');
            $table->string('challan_date', 10)->nullable();
            $table->decimal('total_received', 10);
            $table->text('remarks')->nullable();
            $table->integer('term_advance_balance')->default(0);
            $table->integer('old_fee_rec_no')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fees_receipt_challan');
    }
};
