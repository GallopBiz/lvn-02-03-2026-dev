<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

$sourceDb = '2026_2027';
$targetDb = '2027_2028';

echo "Initializing $targetDb from $sourceDb...\n";

// Ensure target db exists
DB::statement("CREATE DATABASE IF NOT EXISTS `$targetDb`");

// Get all tables from source
$tables = DB::select("SHOW TABLES FROM `$sourceDb`");
$tableKey = 'Tables_in_' . $sourceDb;

$copyDataTables = [
    'classes', 'states', 'cities', 'model_has_roles', 'roles', 
    'role_has_permissions', 'permissions', 'caste_name', 'presentlyschool',
    'fees_types_master', 'terms', 'course_fees_head_master', 'late_fees_master',
    'users'
];

$existingTargetTables = DB::select("SHOW TABLES FROM `$targetDb`");
$existingTargetNames = [];
$targetKey = 'Tables_in_' . $targetDb;
foreach ($existingTargetTables as $t) {
    $existingTargetNames[] = $t->$targetKey;
}

$copiedCount = 0;
foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    if (!in_array($tableName, $existingTargetNames)) {
        try {
            DB::statement("CREATE TABLE `$targetDb`.`$tableName` LIKE `$sourceDb`.`$tableName`");
            $copiedCount++;
            
            if (in_array($tableName, $copyDataTables)) {
                DB::statement("INSERT INTO `$targetDb`.`$tableName` SELECT * FROM `$sourceDb`.`$tableName`");
            }
        } catch (\Exception $e) {
            echo "Error on $tableName: " . $e->getMessage() . "\n";
        }
    }
}

echo "Created $copiedCount tables in $targetDb.\n";
$targetTablesAfter = DB::select("SHOW TABLES FROM `$targetDb`");
echo "Total tables in $targetDb now: " . count($targetTablesAfter) . "\n";
