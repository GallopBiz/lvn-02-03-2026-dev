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
        Schema::create('inquiry_registration', function (Blueprint $table) {
            $table->integer('id', true)->comment('uniqe_id');
            $table->string('application_for')->nullable();
            $table->integer('form_number')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('class_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('session_name')->nullable();
            $table->longText('json_str')->nullable()->comment('contains hold fields');
            $table->integer('phone_number')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->string('inq_mode', 5)->nullable()->comment('on-online, off-offline');
            $table->string('status', 1)->nullable()->default('i')->comment('p-pre inq, i-inquiry, r-registration');
            $table->string('next_year', 5)->nullable();
            $table->string('type', 10)->nullable();
            $table->string('save_status', 50)->nullable();
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('section_name', 20)->nullable();
            $table->string('assign_calling', 20)->nullable();
            $table->integer('is_delete')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inquiry_registration');
    }
};
