<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$request->replace([
    'classname' => '01',
    'section_name' => 'A',
    'term_name' => 'PT 1',
    'report_type' => 'Consolidated',
    'exam_title' => 'PT 1 Examination',
]);

$controller = new \App\Http\Controllers\MarksController();

try {
    $response = $controller->show_report_markss($request);
    echo "show_report_markss executed successfully!\n";
} catch (\Exception $e) {
    echo "Execution Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
