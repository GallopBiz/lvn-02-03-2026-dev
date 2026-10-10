<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\MarksController();
$reflection = new \ReflectionClass($controller);
$accessibleAcademicExams = $reflection->getMethod('accessibleAcademicExams');
$accessibleAcademicExams->setAccessible(true);
$exams = $accessibleAcademicExams->invoke($controller);

echo "Academic Exams list:\n";
print_r($exams->take(10)->toArray());
