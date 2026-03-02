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
        Schema::create('previosly_saved_marks_entry', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('teacher_id')->nullable();
            $table->string('exam_id')->nullable();
            $table->string('class_id')->nullable();
            $table->string('section_name')->nullable();
            $table->string('Stream_id')->nullable();
            $table->string('subject_id')->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('previosly_saved_marks_entry');
    }
};
