<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Classname;

echo "--- CLASSES ---\n";
$classes = Classname::all();
foreach ($classes as $c) {
    echo "ID: {$c->id} | Name: {$c->class_name}\n";
}

echo "\n--- STUDENTS SAMPLE ---\n";
$students = DB::connection('dynamic')->table('student_registration')
    ->limit(10)
    ->get(['id', 'student_name', 'class_id', 'class_name', 'section_name', 'is_archived']);
print_r($students->toArray());

echo "\n--- STUDENT COUNT PER CLASS & SECTION ---\n";
$grouped = DB::connection('dynamic')->table('student_registration')
    ->select('class_id', 'class_name', 'section_name', DB::raw('count(*) as total'))
    ->groupBy('class_id', 'class_name', 'section_name')
    ->get();
print_r($grouped->toArray());
