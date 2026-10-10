<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Subject related tables in database:\n";
$tables = DB::connection('dynamic')->select('SHOW TABLES');
foreach ($tables as $t) {
    foreach ($t as $tableName) {
        if (str_contains($tableName, 'subj')) {
            echo " - " . $tableName . "\n";
        }
    }
}

try {
    echo "Columns of subjects table:\n";
    print_r(Schema::connection('dynamic')->getColumnListing('subjects'));
} catch (\Exception $e) {
    echo "subjects error: " . $e->getMessage() . "\n";
}
