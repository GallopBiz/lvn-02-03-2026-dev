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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('class_id')->nullable();
            $table->bigInteger('stream_id')->nullable();
            $table->text('section_name')->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->bigInteger('teacher_id')->nullable();
            $table->string('session_name')->nullable();
            $table->string('current_date', 50)->nullable();
            $table->integer('is_delete')->nullable()->default(0)->comment('0 : Active , 1 : Delete');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('role', 35);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
