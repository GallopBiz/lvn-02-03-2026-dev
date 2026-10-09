<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\MarksController();

$reflection = new \ReflectionClass($controller);
$activeGradeRanges = $reflection->getMethod('activeGradeRanges');
$activeGradeRanges->setAccessible(true);
$ranges = $activeGradeRanges->invoke($controller);

echo "Active Grade Ranges from DB:\n";
print_r($ranges->toArray());

$gradeFromMaster = $reflection->getMethod('gradeFromMaster');
$gradeFromMaster->setAccessible(true);

$testCases = [
    ['marks' => 95, 'max' => 100],
    ['marks' => 85, 'max' => 100],
    ['marks' => 75, 'max' => 100],
    ['marks' => 65, 'max' => 100],
    ['marks' => 55, 'max' => 100],
    ['marks' => 45, 'max' => 100],
    ['marks' => 35, 'max' => 100],
    ['marks' => 40, 'max' => 50],  // 80% -> A
    ['marks' => 48, 'max' => 50],  // 96% -> A1
];

echo "\nGrade Calculation Test Cases:\n";
foreach ($testCases as $tc) {
    $g = $gradeFromMaster->invoke($controller, (float) $tc['marks'], (float) $tc['max']);
    $pct = ($tc['max'] > 0) ? ($tc['marks'] / $tc['max']) * 100 : $tc['marks'];
    echo "Marks: {$tc['marks']}/{$tc['max']} ({$pct}%) => Grade: '{$g}'\n";
}
