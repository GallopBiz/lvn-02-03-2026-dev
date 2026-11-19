<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrate default and dynamic connections
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                $this->addColumnsToConnection($conn);
            } catch (\Throwable $e) {
                // Ignore connection errors if not active
            }
        }

        // 2. Migrate across all session databases (e.g. 2025_2026, 2026_2027, etc.)
        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $this->addColumnsToDatabase($dbName);
                } catch (\Throwable $e) {
                    // Ignore database errors
                }
            }
        } catch (\Throwable $e) {
            // Ignore if SHOW DATABASES is unavailable
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'dynamic'];
        foreach ($connections as $conn) {
            try {
                $this->dropColumnsFromConnection($conn);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        try {
            $databases = DB::select("SHOW DATABASES LIKE '20%'");
            foreach ($databases as $dbObj) {
                $dbName = array_values((array) $dbObj)[0];
                try {
                    $this->dropColumnsFromDatabase($dbName);
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    private function addColumnsToConnection(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('student_registration')) {
            return;
        }

        Schema::connection($connection)->table('student_registration', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('student_registration', 'next_year')) {
                $table->tinyInteger('next_year')->default(0)->after('status')->index();
            }
            if (!Schema::connection($connection)->hasColumn('student_registration', 'next_year_section')) {
                $table->string('next_year_section', 191)->nullable()->after('next_year');
            }
            if (!Schema::connection($connection)->hasColumn('student_registration', 'next_year_stream')) {
                $table->string('next_year_stream', 191)->nullable()->after('next_year_section');
            }
            if (!Schema::connection($connection)->hasColumn('student_registration', 'next_year_subject_combination')) {
                $table->string('next_year_subject_combination', 191)->nullable()->after('next_year_stream');
            }
        });
    }

    private function addColumnsToDatabase(string $databaseName): void
    {
        $config = config('database.connections.dynamic');
        $config['database'] = $databaseName;
        config(['database.connections._migration_dynamic' => $config]);
        DB::purge('_migration_dynamic');

        $this->addColumnsToConnection('_migration_dynamic');

        DB::purge('_migration_dynamic');
    }

    private function dropColumnsFromConnection(string $connection): void
    {
        if (!Schema::connection($connection)->hasTable('student_registration')) {
            return;
        }

        Schema::connection($connection)->table('student_registration', function (Blueprint $table) use ($connection) {
            $colsToDrop = [];
            foreach (['next_year_subject_combination', 'next_year_stream', 'next_year_section', 'next_year'] as $col) {
                if (Schema::connection($connection)->hasColumn('student_registration', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }

    private function dropColumnsFromDatabase(string $databaseName): void
    {
        $config = config('database.connections.dynamic');
        $config['database'] = $databaseName;
        config(['database.connections._migration_dynamic' => $config]);
        DB::purge('_migration_dynamic');

        $this->dropColumnsFromConnection('_migration_dynamic');

        DB::purge('_migration_dynamic');
    }
};
