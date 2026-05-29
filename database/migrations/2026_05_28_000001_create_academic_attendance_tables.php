<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dailattandence') && !Schema::hasTable('academic_attendance')) {
            Schema::rename('dailattandence', 'academic_attendance');
        }

        if (!Schema::hasTable('academic_attendance')) {
            Schema::create('academic_attendance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->string('class_name')->nullable();
                $table->string('section_name')->nullable();
                $table->date('attendance_date')->nullable();
                $table->string('academic_session')->nullable();
                $table->unsignedInteger('total_students')->default(0);
                $table->unsignedInteger('present_count')->default(0);
                $table->unsignedInteger('absent_count')->default(0);
                $table->unsignedInteger('leave_count')->default(0);
                $table->unsignedInteger('half_day_count')->default(0);
                $table->integer('is_delete')->default(0)->comment('0 none, 1 delete');
                $table->timestamps();
                $table->unique(['class_id', 'section_name', 'attendance_date', 'academic_session'], 'academic_attendance_unique_day');
                $table->index(['attendance_date', 'class_id', 'section_name'], 'academic_attendance_report_lookup');
            });
        } else {
            Schema::table('academic_attendance', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_attendance', 'teacher_id') && Schema::hasColumn('academic_attendance', 'Teacher_id')) {
                    DB::statement('ALTER TABLE academic_attendance CHANGE Teacher_id teacher_id BIGINT UNSIGNED NULL');
                }
                if (!Schema::hasColumn('academic_attendance', 'attendance_date') && Schema::hasColumn('academic_attendance', 'Attandence_date')) {
                    DB::statement('ALTER TABLE academic_attendance CHANGE Attandence_date attendance_date DATE NULL');
                }
                if (!Schema::hasColumn('academic_attendance', 'class_name')) {
                    $table->string('class_name')->nullable()->after('class_id');
                }
                if (!Schema::hasColumn('academic_attendance', 'academic_session')) {
                    $table->string('academic_session')->nullable()->after('attendance_date');
                }
                if (!Schema::hasColumn('academic_attendance', 'total_students')) {
                    $table->unsignedInteger('total_students')->default(0)->after('academic_session');
                }
                if (!Schema::hasColumn('academic_attendance', 'present_count')) {
                    $table->unsignedInteger('present_count')->default(0)->after('total_students');
                }
                if (!Schema::hasColumn('academic_attendance', 'absent_count')) {
                    $table->unsignedInteger('absent_count')->default(0)->after('present_count');
                }
                if (!Schema::hasColumn('academic_attendance', 'leave_count')) {
                    $table->unsignedInteger('leave_count')->default(0)->after('absent_count');
                }
                if (!Schema::hasColumn('academic_attendance', 'half_day_count')) {
                    $table->unsignedInteger('half_day_count')->default(0)->after('leave_count');
                }
            });
        }

        if (Schema::hasTable('academic_student_daily_attendance') && !Schema::hasTable('academic_attendance_details')) {
            Schema::rename('academic_student_daily_attendance', 'academic_attendance_details');
        }

        if (!Schema::hasTable('academic_attendance_details')) {
            Schema::create('academic_attendance_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attendance_id');
                $table->unsignedBigInteger('student_id');
                $table->string('status', 20);
                $table->string('remarks')->nullable();
                $table->unique(['attendance_id', 'student_id'], 'academic_attendance_details_unique_student');
                $table->index(['student_id', 'status'], 'academic_attendance_details_student_status');
            });
        } else {
            Schema::table('academic_attendance_details', function (Blueprint $table) {
                if (!Schema::hasColumn('academic_attendance_details', 'remarks')) {
                    $table->string('remarks')->nullable()->after('status');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('academic_attendance_details') && !Schema::hasTable('academic_student_daily_attendance')) {
            Schema::rename('academic_attendance_details', 'academic_student_daily_attendance');
        }

        if (Schema::hasTable('academic_attendance') && !Schema::hasTable('dailattandence')) {
            Schema::rename('academic_attendance', 'dailattandence');
        }
    }
};
