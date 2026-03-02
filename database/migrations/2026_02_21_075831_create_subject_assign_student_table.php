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
        Schema::create('subject_assign_student', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('class_name')->nullable();
            $table->text('section_name')->nullable();
            $table->text('assign_this_combtoall')->nullable();
            $table->json('students_details')->nullable();
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
        Schema::dropIfExists('subject_assign_student');
    }
};
