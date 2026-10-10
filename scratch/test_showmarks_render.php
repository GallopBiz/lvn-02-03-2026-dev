<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

View::share('errors', new \Illuminate\Support\ViewErrorBag());

try {
    $rendered = view('backend.AcademicsModules.showmarks', [
        'classlist' => collect([(object)['class_name' => '01']]),
        'examslist' => collect([(object)['exam_name' => 'PT 1', 'exam_title' => 'PT 1 Examination']]),
    ])->render();
    echo "Blade rendered successfully! Length: " . strlen($rendered) . "\n";
    if (str_contains($rendered, 'PT 1 Examination')) {
        echo "Found 'PT 1 Examination' inside rendered exam_title dropdown!\n";
    }
} catch (\Exception $e) {
    echo "Render Error: " . $e->getMessage() . "\n";
}
