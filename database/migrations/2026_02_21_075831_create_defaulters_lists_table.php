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
            $table->integer('id', true);
            $table->integer('student_id')->nullable();
            $table->text('year')->nullable();
            $table->text('date_type')->nullable();
            $table->date('date')->nullable();
            $table->text('status')->nullable();
            $table->string('ac_head_name')->nullable();
            $table->text('next_yesr_fees')->nullable();
            $table->text('rte')->nullable();
            $table->text('staff_ward')->nullable();
            $table->string('session_name')->nullable();
            $table->text('scholarship')->nullable();
            $table->text('scholar_no')->nullable();
            $table->text('enrollment_no')->nullable();
            $table->text('student_name')->nullable();
            $table->text('class_name')->nullable();
            $table->text('section_name')->nullable();
            $table->text('account_name')->nullable();
            $table->text('balance_amount')->nullable();
            $table->text('min_date')->nullable();
            $table->text('max_date')->nullable();
            $table->text('student')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
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
