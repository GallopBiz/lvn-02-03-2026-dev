<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id', 10068)->first();
if ($row) {
    echo "Due chart for 10068:\n";
    $outer = json_decode($row->json_str, true);
    print_r($outer);
} else {
    echo "No due chart for 10068\n";
}
