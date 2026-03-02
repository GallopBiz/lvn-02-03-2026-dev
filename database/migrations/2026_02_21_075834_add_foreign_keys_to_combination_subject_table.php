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
        Schema::table('combination_subject', function (Blueprint $table) {
            $table->foreign(['subject_combination_id'], 'fk_combination_subject_combination')->references(['id'])->on('subject_combinations')->onDelete('CASCADE');
            $table->foreign(['subject_id'], 'fk_combination_subject_subject')->references(['id'])->on('subjectmaster')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('combination_subject', function (Blueprint $table) {
            $table->dropForeign('fk_combination_subject_combination');
            $table->dropForeign('fk_combination_subject_subject');
        });
    }
};
