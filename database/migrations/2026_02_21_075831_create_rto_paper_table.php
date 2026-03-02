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
        Schema::create('rto_paper', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('Renewal_Date', 200);
            $table->string('Next_Renewal_Date', 200);
            $table->string('Registration_Date', 200);
            $table->string('Vehicle', 200);
            $table->string('Transfer_date', 200);
            $table->string('RTO_paper_Name', 200);
            $table->string('Document', 200);
            $table->string('Reminder_Frequency', 200);
            $table->string('image', 200);
            $table->string('updated_at', 200);
            $table->string('created_at', 200);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rto_paper');
    }
};
