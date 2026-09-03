<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('dynamic')->table('student_registration', function (Blueprint $table) {
            if (!Schema::connection('dynamic')->hasColumn('student_registration', 'is_archived')) {
                $table->boolean('is_archived')->default(0)->after('tc_certificate_id');
            }
        });
    }

    public function down()
    {
        Schema::connection('dynamic')->table('student_registration', function (Blueprint $table) {
            if (Schema::connection('dynamic')->hasColumn('student_registration', 'is_archived')) {
                $table->dropColumn('is_archived');
            }
        });
    }
};
