<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('subjectmaster') && !Schema::connection($conn)->hasColumn('subjectmaster', 'special_formatting_classes')) {
                    Schema::connection($conn)->table('subjectmaster', function (Blueprint $table) {
                        $table->text('special_formatting_classes')->nullable()->after('do_not_print_in_main_scholastic_area');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore connection-specific errors
            }
        }

        // Also ensure all active academic session databases have the column
        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                $hasCol = DB::select("SHOW COLUMNS FROM `{$dbName}`.subjectmaster LIKE 'special_formatting_classes'");
                if (empty($hasCol)) {
                    DB::statement("ALTER TABLE `{$dbName}`.subjectmaster ADD COLUMN special_formatting_classes TEXT NULL AFTER do_not_print_in_main_scholastic_area");
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function down()
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('subjectmaster') && Schema::connection($conn)->hasColumn('subjectmaster', 'special_formatting_classes')) {
                    Schema::connection($conn)->table('subjectmaster', function (Blueprint $table) {
                        $table->dropColumn('special_formatting_classes');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};

