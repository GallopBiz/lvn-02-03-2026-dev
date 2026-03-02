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
        Schema::create('course_fees_head_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('ac_head_name')->nullable();
            $table->string('remarks', 555)->nullable();
            $table->integer('order')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 none, 1 deleted');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_fees_head_master');
    }
};
