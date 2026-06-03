<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('dynamic')->table('student_registration', function (Blueprint $table) {
            if (!Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
                $table->string('transfer_status', 30)->default('active')->after('status')->index();
            }
            if (!Schema::connection('dynamic')->hasColumn('student_registration', 'transferred_at')) {
                $table->timestamp('transferred_at')->nullable()->after('transfer_status');
            }
            if (!Schema::connection('dynamic')->hasColumn('student_registration', 'tc_certificate_id')) {
                $table->unsignedBigInteger('tc_certificate_id')->nullable()->after('transferred_at')->index();
            }
        });
    }

    public function down()
    {
        Schema::connection('dynamic')->table('student_registration', function (Blueprint $table) {
            if (Schema::connection('dynamic')->hasColumn('student_registration', 'tc_certificate_id')) {
                $table->dropIndex(['tc_certificate_id']);
                $table->dropColumn('tc_certificate_id');
            }
            if (Schema::connection('dynamic')->hasColumn('student_registration', 'transferred_at')) {
                $table->dropColumn('transferred_at');
            }
            if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
                $table->dropIndex(['transfer_status']);
                $table->dropColumn('transfer_status');
            }
        });
    }
};
