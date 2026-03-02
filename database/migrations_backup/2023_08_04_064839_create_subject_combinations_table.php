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
        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->id();
            $table->text('combination_name');
            $table->text('alise_name');
            $table->text('stream');
            $table->text('is_academic_comb');
            $table->text('combination_type');
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
        Schema::dropIfExists('subject_combinations');
    }
};
