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
        Schema::create('account_annual_defaulter_student', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('scholar_no', 50)->index('scholar_no');
            $table->string('class', 50);
            $table->string('section', 50);
            $table->string('student_name', 100);
            $table->decimal('admission_fees', 10)->nullable();
            $table->decimal('alumni_fees', 10)->nullable();
            $table->decimal('bus_fees', 10)->nullable();
            $table->decimal('caution_money', 10)->nullable();
            $table->decimal('lunch_fees', 10)->nullable();
            $table->decimal('tuition_fees', 10)->nullable();
            $table->decimal('total', 10)->nullable();
            $table->string('status', 50)->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_annual_defaulter_student');
    }
};
