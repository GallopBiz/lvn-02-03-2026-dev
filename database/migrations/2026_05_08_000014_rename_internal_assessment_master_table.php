<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('dynamic')->hasTable('internal_assessment_master') && !Schema::connection('dynamic')->hasTable('academic_internal_assessment_master')) {
            Schema::connection('dynamic')->rename('internal_assessment_master', 'academic_internal_assessment_master');
        }
    }

    public function down(): void
    {
        if (Schema::connection('dynamic')->hasTable('academic_internal_assessment_master') && !Schema::connection('dynamic')->hasTable('internal_assessment_master')) {
            Schema::connection('dynamic')->rename('academic_internal_assessment_master', 'internal_assessment_master');
        }
    }
};
