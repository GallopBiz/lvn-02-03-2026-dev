<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "User model table: " . (new App\Models\User)->getTable() . "\n";
echo "User count: " . App\Models\User::count() . "\n";

if (class_exists('App\Models\Staff')) {
    echo "Staff model table: " . (new App\Models\Staff)->getTable() . "\n";
    echo "Staff count: " . App\Models\Staff::count() . "\n";
}

try {
    $columns = Schema::getColumnListing((new App\Models\User)->getTable());
    echo "User columns: " . json_encode($columns) . "\n";
} catch (\Exception $e) {
    echo "Error columns: " . $e->getMessage() . "\n";
}
