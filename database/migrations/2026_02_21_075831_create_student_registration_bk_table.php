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
        Schema::create('student_registration_bk', function (Blueprint $table) {
            $table->integer('id', true)->comment('uniqe_id');
            $table->string('application_for')->nullable();
            $table->string('form_number', 200)->nullable();
            $table->string('scholar_no', 50)->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('class_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('session_name')->nullable();
            $table->longText('json_str')->nullable()->comment('contains hold fields');
            $table->string('staff_name', 100)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->string('inq_mode', 5)->nullable()->comment('on-online, off-offline');
            $table->string('driver', 200)->nullable();
            $table->string('status', 1)->nullable()->comment('p-pre inq, i-inquiry, r-registration');
            $table->string('type', 100)->nullable();
            $table->string('password', 225)->nullable();
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
        Schema::dropIfExists('student_registration_bk');
    }
};
