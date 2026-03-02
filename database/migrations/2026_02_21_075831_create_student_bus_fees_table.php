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
        Schema::create('student_bus_fees', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('class', 10)->nullable();
            $table->string('section', 10)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('scholar_no', 50)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('father', 100)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('bus_fees_type', 50)->nullable();
            $table->decimal('installment', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_bus_fees');
    }
};
