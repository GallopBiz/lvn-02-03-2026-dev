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
        Schema::create('student_fees_exempted', function (Blueprint $table) {
            $table->integer('student_id', true);
            $table->integer('scholar_no')->unique('scholar_no');
            $table->integer('exempt_per')->nullable();
            $table->boolean('lum')->nullable();
            $table->boolean('lunch')->nullable();
            $table->boolean('lssm')->nullable();
            $table->boolean('sibling')->nullable();
            $table->boolean('staff')->nullable();
            $table->boolean('last_year_lssm')->nullable();
            $table->string('head_name', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fees_exempted');
    }
};
