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
        Schema::create('late_fees_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('late_fees_amount', 155)->nullable();
            $table->string('from_amount', 155)->nullable();
            $table->string('to_amount', 155)->nullable();
            $table->string('upto', 155)->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->integer('is_delete')->default(0)->comment('0 no delete ,1 deleted');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('late_fees_master');
    }
};
