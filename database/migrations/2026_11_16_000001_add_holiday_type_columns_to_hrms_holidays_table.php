<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('hrms_holidays')) {
                    Schema::connection($conn)->table('hrms_holidays', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('hrms_holidays', 'for_hrms')) {
                            $table->boolean('for_hrms')->default(1)->after('HolidayDescription');
                        }
                        if (!Schema::connection($conn)->hasColumn('hrms_holidays', 'for_academic')) {
                            $table->boolean('for_academic')->default(0)->after('for_hrms');
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore connection-specific errors
            }
        }

        // Ensure all databases with hrms_holidays have the columns and existing rows are updated
        try {
            $databases = DB::select("SHOW DATABASES");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $hasTable = DB::select("SHOW TABLES FROM `{$dbName}` LIKE 'hrms_holidays'");
                    if (!empty($hasTable)) {
                        $hasForHrms = DB::select("SHOW COLUMNS FROM `{$dbName}`.hrms_holidays LIKE 'for_hrms'");
                        if (empty($hasForHrms)) {
                            DB::statement("ALTER TABLE `{$dbName}`.hrms_holidays ADD COLUMN for_hrms TINYINT(1) NOT NULL DEFAULT 1 AFTER HolidayDescription");
                        }
                        $hasForAcademic = DB::select("SHOW COLUMNS FROM `{$dbName}`.hrms_holidays LIKE 'for_academic'");
                        if (empty($hasForAcademic)) {
                            DB::statement("ALTER TABLE `{$dbName}`.hrms_holidays ADD COLUMN for_academic TINYINT(1) NOT NULL DEFAULT 0 AFTER for_hrms");
                        }
                        // Set existing holidays as applicable to both HRMS and Academic
                        DB::statement("UPDATE `{$dbName}`.hrms_holidays SET for_hrms = 1, for_academic = 1 WHERE for_hrms IS NULL OR for_academic IS NULL OR (for_hrms = 1 AND for_academic = 0 AND created_at < NOW())");
                    }
                } catch (\Throwable $e) {
                    // Ignore database-specific permission or connection errors
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('hrms_holidays')) {
                    Schema::connection($conn)->table('hrms_holidays', function (Blueprint $table) use ($conn) {
                        if (Schema::connection($conn)->hasColumn('hrms_holidays', 'for_academic')) {
                            $table->dropColumn('for_academic');
                        }
                        if (Schema::connection($conn)->hasColumn('hrms_holidays', 'for_hrms')) {
                            $table->dropColumn('for_hrms');
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
