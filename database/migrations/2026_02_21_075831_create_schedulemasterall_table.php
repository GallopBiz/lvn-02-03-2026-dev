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
        Schema::create('schedulemasterall', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('schedule_name', 200);
            $table->text('schedule_check_two');
            $table->longText('schedule_check_one')->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('schedule_time_from', 20)->nullable();
            $table->string('schedule_time_to', 20)->nullable();
            $table->string('schedule_print_option', 20)->nullable();
            $table->string('schedule_point', 10)->nullable();
            $table->integer('schedule_order')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedulemasterall');
    }
};
