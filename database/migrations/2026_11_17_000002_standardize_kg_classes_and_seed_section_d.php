<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $classesToSeed = [
            'Nursery',
            'KG1',
            'KG2',
            '01',
            '02',
            '03',
            '04',
            '05',
            '06',
            '07',
            '08',
            '09',
            '10',
        ];

        // 1. Update current connection databases
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                $this->standardizeClassesOnConnection($conn);
                $this->seedSectionDOnConnection($conn, $classesToSeed);
            } catch (\Throwable $e) {
                // Ignore connection errors if not configured
            }
        }

        // 2. Discover all session databases (e.g. 2025_2026, 2026_2027, 2027_2028)
        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $this->standardizeClassesOnDatabase($dbName);
                    $this->seedSectionDOnDatabase($dbName, $classesToSeed);
                } catch (\Throwable $e) {
                    // Ignore database errors
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }

    private function standardizeClassesOnConnection(string $conn): void
    {
        if (Schema::connection($conn)->hasTable('classes')) {
            DB::connection($conn)->table('classes')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('classes')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
            DB::connection($conn)->table('classes')
                ->where('class_name', '03')
                ->where('section_name', 'Aryabhatta')
                ->where('is_delete', 1)
                ->update(['is_delete' => 0]);
        }

        if (Schema::connection($conn)->hasTable('class_name')) {
            DB::connection($conn)->table('class_name')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('class_name')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('course_fees_structure_master')) {
            DB::connection($conn)->table('course_fees_structure_master')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('course_fees_structure_master')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('generate_duechartstatus')) {
            DB::connection($conn)->table('generate_duechartstatus')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('generate_duechartstatus')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('inquiry_registration')) {
            DB::connection($conn)->table('inquiry_registration')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('inquiry_registration')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('subject_assign_student')) {
            DB::connection($conn)->table('subject_assign_student')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('subject_assign_student')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('student_registration')) {
            DB::connection($conn)->table('student_registration')->where('class_name', 'Kg1')->update(['class_name' => 'KG1']);
            DB::connection($conn)->table('student_registration')->where('class_name', 'Kg2')->update(['class_name' => 'KG2']);
        }

        if (Schema::connection($conn)->hasTable('academic_student_roll_no')) {
            DB::connection($conn)->table('academic_student_roll_no')->where('class_id', 'Kg1')->update(['class_id' => 'KG1']);
            DB::connection($conn)->table('academic_student_roll_no')->where('class_id', 'Kg2')->update(['class_id' => 'KG2']);
        }
    }

    private function seedSectionDOnConnection(string $conn, array $classes): void
    {
        if (!Schema::connection($conn)->hasTable('classes')) {
            return;
        }

        foreach ($classes as $className) {
            $existing = DB::connection($conn)->table('classes')
                ->where('class_name', $className)
                ->where('section_name', 'D')
                ->first();

            if ($existing) {
                if ($existing->is_delete == 1) {
                    DB::connection($conn)->table('classes')->where('id', $existing->id)->update(['is_delete' => 0]);
                }
            } else {
                DB::connection($conn)->table('classes')->insert([
                    'class_name' => $className,
                    'section_name' => 'D',
                    'is_delete' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function standardizeClassesOnDatabase(string $dbName): void
    {
        $tables = [
            'classes' => 'class_name',
            'class_name' => 'class_name',
            'course_fees_structure_master' => 'class_name',
            'generate_duechartstatus' => 'class_name',
            'inquiry_registration' => 'class_name',
            'subject_assign_student' => 'class_name',
            'student_registration' => 'class_name',
            'academic_student_roll_no' => 'class_id',
        ];

        foreach ($tables as $tbl => $col) {
            try {
                $check = DB::select("SHOW TABLES FROM `{$dbName}` LIKE '{$tbl}'");
                if (!empty($check)) {
                    DB::statement("UPDATE `{$dbName}`.`{$tbl}` SET `{$col}` = 'KG1' WHERE `{$col}` = 'Kg1'");
                    DB::statement("UPDATE `{$dbName}`.`{$tbl}` SET `{$col}` = 'KG2' WHERE `{$col}` = 'Kg2'");
                }
            } catch (\Throwable $e) {
                // Ignore table-level errors
            }
        }

        try {
            $checkClasses = DB::select("SHOW TABLES FROM `{$dbName}` LIKE 'classes'");
            if (!empty($checkClasses)) {
                DB::statement("UPDATE `{$dbName}`.classes SET is_delete = 0 WHERE class_name = '03' AND section_name = 'Aryabhatta' AND is_delete = 1");
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    private function seedSectionDOnDatabase(string $dbName, array $classes): void
    {
        $check = DB::select("SHOW TABLES FROM `{$dbName}` LIKE 'classes'");
        if (empty($check)) {
            return;
        }

        foreach ($classes as $className) {
            $existing = DB::select("SELECT id, is_delete FROM `{$dbName}`.classes WHERE class_name = ? AND section_name = 'D' LIMIT 1", [$className]);
            if (!empty($existing)) {
                if ($existing[0]->is_delete == 1) {
                    DB::statement("UPDATE `{$dbName}`.classes SET is_delete = 0 WHERE id = ?", [$existing[0]->id]);
                }
            } else {
                DB::statement("INSERT INTO `{$dbName}`.classes (class_name, section_name, is_delete, created_at, updated_at) VALUES (?, 'D', 0, NOW(), NOW())", [$className]);
            }
        }
    }
};
