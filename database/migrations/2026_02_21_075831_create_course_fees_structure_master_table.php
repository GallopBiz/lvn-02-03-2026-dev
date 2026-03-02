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
        Schema::create('course_fees_structure_master', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('class_name')->nullable();
            $table->string('session_name')->nullable();
            $table->string('fees_type_name')->nullable();
            $table->string('cast_category')->nullable();
            $table->string('batch')->nullable();
            $table->json('json_str')->nullable();
            $table->string('total_above_fees')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 none, 1 deleted');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_fees_structure_master');
    }
};
