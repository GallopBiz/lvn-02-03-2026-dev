<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('academic_attendance_collectives')) {
            Schema::create('academic_attendance_collectives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->string('class_name')->nullable();
                $table->string('section_name')->nullable();
                $table->unsignedBigInteger('exam_id')->nullable();
                $table->string('exam_name')->nullable();
                $table->string('academic_session')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedInteger('working_days')->default(0);
                $table->unsignedInteger('student_count')->default(0);
                $table->integer('is_delete')->default(0);
                $table->timestamps();
                $table->index(['class_id', 'section_name', 'academic_session'], 'academic_collective_lookup');
            });
        }

        if (!Schema::hasTable('academic_attendance_collective_details')) {
            Schema::create('academic_attendance_collective_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('collective_id');
                $table->unsignedBigInteger('student_id')->nullable();
                $table->string('student_name')->nullable();
                $table->string('scholar_no')->nullable();
                $table->decimal('present_days', 6, 2)->default(0);
                $table->unsignedInteger('working_days')->default(0);
                $table->decimal('attendance_percentage', 6, 2)->default(0);
                $table->boolean('is_below_minimum')->default(false);
                $table->unique(['collective_id', 'student_id'], 'academic_collective_student_unique');
                $table->index(['student_id', 'attendance_percentage'], 'academic_collective_student_lookup');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('academic_attendance_collective_details');
        Schema::dropIfExists('academic_attendance_collectives');
    }
};
