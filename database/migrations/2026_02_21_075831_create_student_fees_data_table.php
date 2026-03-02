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
        Schema::create('student_fees_data', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('FeeRecNo');
            $table->string('StudentName');
            $table->integer('ScholarNo');
            $table->string('Class', 50);
            $table->string('Section', 50);
            $table->string('AccountName');
            $table->decimal('TotalAmount', 10);
            $table->string('PostedNoDt');
            $table->string('Terms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fees_data');
    }
};
