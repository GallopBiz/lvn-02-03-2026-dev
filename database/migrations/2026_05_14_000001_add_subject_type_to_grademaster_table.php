<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::connection('dynamic')->hasTable('grademaster') && !Schema::connection('dynamic')->hasColumn('grademaster', 'subject_type')) {
            Schema::connection('dynamic')->table('grademaster', function (Blueprint $table) {
                $table->string('subject_type', 50)->nullable()->after('grading_name');
            });
        }
    }

    public function down()
    {
        if (Schema::connection('dynamic')->hasTable('grademaster') && Schema::connection('dynamic')->hasColumn('grademaster', 'subject_type')) {
            Schema::connection('dynamic')->table('grademaster', function (Blueprint $table) {
                $table->dropColumn('subject_type');
            });
        }
    }
};
