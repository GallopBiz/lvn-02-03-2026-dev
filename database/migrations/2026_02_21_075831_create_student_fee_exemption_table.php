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
        Schema::create('student_fee_exemption', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('scholar_no');
            $table->string('head_name', 50);
            $table->string('exemption_type', 50);
            $table->integer('percentage');
            $table->string('remark', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fee_exemption');
    }
};
