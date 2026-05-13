<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('dynamic')->dropIfExists('academic_marksheet_template_assignments');
        Schema::connection('dynamic')->dropIfExists('academic_marksheet_templates');
        Schema::connection('dynamic')->dropIfExists('academic_marksheet_settings');

        if (
            Schema::connection('dynamic')->hasTable('academic_marksheets')
            && Schema::connection('dynamic')->hasColumn('academic_marksheets', 'template_id')
        ) {
            Schema::connection('dynamic')->table('academic_marksheets', function (Blueprint $table) {
                $table->dropColumn('template_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::connection('dynamic')->hasTable('academic_marksheets')
            && !Schema::connection('dynamic')->hasColumn('academic_marksheets', 'template_id')
        ) {
            Schema::connection('dynamic')->table('academic_marksheets', function (Blueprint $table) {
                $table->unsignedBigInteger('template_id')->nullable()->after('id');
            });
        }
    }
};
