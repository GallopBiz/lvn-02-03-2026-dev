<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exammasters', function (Blueprint $table) {
            $table->id();
            $table->string('exam_name');
            $table->text('max_marks_theory');
            $table->text('max_marks_practical');
            $table->text('fail_if');
            $table->text('exam_type');
            $table->text('remarks');
            $table->text('class_name');
            $table->text('is_ser');
            $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exammasters');
    }
};
