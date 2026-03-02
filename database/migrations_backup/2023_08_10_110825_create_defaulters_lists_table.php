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
        Schema::create('defaulters_lists', function (Blueprint $table) {
            $table->id();
            $table->text('scholar_no.');
            $table->text('enrollment_no');
            $table->text('student_name');
            $table->text('class_name');
            $table->text('section_name');
            $table->text('account_name');
            $table->text('balance_amount');
            $table->text('min_date');
            $table->text('max_date');
            $table->text('student');
            $table->text('year');
            $table->text('date_type');
            $table->DATE('date');
            $table->smallint('status');
            $table->text('ac_head_name');
            $table->text('next_yesr_fees');
            $table->text('rte');
            $table->text('staff_ward');
            $table->text('session_name');
            $table->text('scholarship');
            $table->text('student_id');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('defaulters_lists');
    }
};
