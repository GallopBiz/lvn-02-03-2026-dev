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
        Schema::create('partymaster', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('Party_Name', 100);
            $table->string('Address', 100);
            $table->string('Tax', 100);
            $table->string('City', 100);
            $table->string('State', 100);
            $table->string('PinCode', 100);
            $table->string('locality', 100);
            $table->string('STDCode', 100);
            $table->string('REsidence_ph_no_1', 100);
            $table->string('Office_ph_no_1', 100);
            $table->string('REsidence_ph_no_2', 100);
            $table->string('Office_ph_no_2', 100);
            $table->string('Mobile', 100);
            $table->string('emailId', 100);
            $table->string('Fax_no_', 100);
            $table->string('Service_Tax_no_', 100);
            $table->string('PAN_no_', 100);
            $table->string('CST_no_', 100);
            $table->string('TIN_no_', 100);
            $table->string('TAN_no_', 100);
            $table->string('GST_no_', 100);
            $table->string('Party_Flag', 100);
            $table->string('Contactif', 225);
            $table->string('validUpto', 100);
            $table->string('Remarks', 200);
            $table->string('Person_Name', 100);
            $table->string('Mobile_NO_', 100);
            $table->string('Department_', 100);
            $table->string('Post', 100);
            $table->integer('is_delete')->default(0)->comment('0 none, 1 delete');
            $table->string('updated_at', 100);
            $table->string('created_at', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partymaster');
    }
};
