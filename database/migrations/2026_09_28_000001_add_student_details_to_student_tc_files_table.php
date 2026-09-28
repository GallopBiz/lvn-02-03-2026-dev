<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::connection('dynamic')->hasTable('student_tc_files')) {
            Schema::connection('dynamic')->table('student_tc_files', function (Blueprint $table) {
                if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'student_name')) {
                    $table->string('student_name', 150)->nullable()->after('scholar_no');
                }
                if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'class_name')) {
                    $table->string('class_name', 100)->nullable()->after('student_name');
                }
                if (!Schema::connection('dynamic')->hasColumn('student_tc_files', 'section_name')) {
                    $table->string('section_name', 100)->nullable()->after('class_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('dynamic')->hasTable('student_tc_files')) {
            Schema::connection('dynamic')->table('student_tc_files', function (Blueprint $table) {
                $columns = [];
                if (Schema::connection('dynamic')->hasColumn('student_tc_files', 'student_name')) {
                    $columns[] = 'student_name';
                }
                if (Schema::connection('dynamic')->hasColumn('student_tc_files', 'class_name')) {
                    $columns[] = 'class_name';
                }
                if (Schema::connection('dynamic')->hasColumn('student_tc_files', 'section_name')) {
                    $columns[] = 'section_name';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
