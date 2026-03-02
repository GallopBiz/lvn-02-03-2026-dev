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
        Schema::table('academic_class_subject', function (Blueprint $table) {
            $table->foreign(['class_id'], 'fk_class')->references(['id'])->on('classes')->onDelete('CASCADE');
            $table->foreign(['subject_id'], 'fk_subject')->references(['id'])->on('subjectmaster')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('academic_class_subject', function (Blueprint $table) {
            $table->dropForeign('fk_class');
            $table->dropForeign('fk_subject');
        });
    }
};
