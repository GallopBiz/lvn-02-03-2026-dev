<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::connection('dynamic')->hasTable('academic_teacher_remark_entries')) {
            Schema::connection('dynamic')->create('academic_teacher_remark_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('exam_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->string('section_name')->nullable();
                $table->unsignedBigInteger('remark_id')->nullable();
                $table->tinyInteger('is_delete')->default(0)->comment('0 : Active , 1 : Delete');
                $table->timestamps();

                $table->index(['teacher_id', 'class_id', 'section_name', 'exam_id'], 'teacher_remark_criteria_idx');
            });
        }

        if (!Schema::connection('dynamic')->hasTable('academic_student_remarks')) {
            Schema::connection('dynamic')->create('academic_student_remarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_remark_entry_id');
                $table->unsignedBigInteger('student_id');
                $table->string('roll_no', 50)->nullable();
                $table->string('scholar_no', 50)->nullable();
                $table->unsignedBigInteger('remark_id')->nullable();
                $table->string('remark_text', 255)->nullable();
                $table->timestamps();

                $table->unique(['teacher_remark_entry_id', 'student_id'], 'student_remark_entry_unique');
                $table->index('remark_id');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('dynamic')->dropIfExists('academic_student_remarks');
        Schema::connection('dynamic')->dropIfExists('academic_teacher_remark_entries');
    }
};
