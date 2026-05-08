<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('dynamic')->create('internal_assessment_master', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 30);
            $table->string('assessment_name', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_delete')->default(0);
            $table->timestamps();
            $table->unique(['assessment_code', 'is_delete'], 'ia_code_delete_unique');
        });

        DB::connection('dynamic')->table('internal_assessment_master')->insert([
            [
                'assessment_code' => 'NB',
                'assessment_name' => 'Notebook',
                'description' => 'Notebook internal assessment',
                'display_order' => 1,
                'is_active' => 1,
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assessment_code' => 'SE',
                'assessment_name' => 'Subject Enrichment',
                'description' => 'Subject enrichment internal assessment',
                'display_order' => 2,
                'is_active' => 1,
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assessment_code' => 'MA/MAS',
                'assessment_name' => 'Multiple Assessment',
                'description' => 'Multiple assessment internal assessment',
                'display_order' => 3,
                'is_active' => 1,
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'assessment_code' => 'PT',
                'assessment_name' => 'Periodic Test',
                'description' => 'Periodic test internal assessment',
                'display_order' => 4,
                'is_active' => 1,
                'is_delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::connection('dynamic')->dropIfExists('internal_assessment_master');
    }
};
