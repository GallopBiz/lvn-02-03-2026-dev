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
        Schema::create('feesreceiptchallan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id')->nullable();
            $table->string('student_dob', 100)->nullable();
            $table->string('recpt_chain', 200)->nullable();
            $table->string('due_upto', 200)->nullable();
            $table->string('name_student', 200)->nullable();
            $table->longText('str_json')->nullable();
            $table->text('class_name')->nullable();
            $table->integer('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->string('old_fee_rec_no', 100)->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feesreceiptchallan');
    }
};
