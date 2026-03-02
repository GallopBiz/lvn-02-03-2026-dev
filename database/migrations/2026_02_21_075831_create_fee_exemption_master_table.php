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
        Schema::create('fee_exemption_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('exmeption_type', 50);
            $table->string('description', 50);
            $table->integer('percentage');
            $table->string('head_name', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_exemption_master');
    }
};
