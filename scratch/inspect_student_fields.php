<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- INSPECTING STUDENT RECORDS IN student_registration ---\n";

$students = DB::connection('dynamic')->table('student_registration')
    ->where('is_archived', 0)
    ->limit(20)
    ->get();

foreach ($students as $s) {
    echo "ID: {$s->id} | Name: {$s->student_name} | class_id: '{$s->class_id}' | class_name: '{$s->class_name}' | section_name: '{$s->section_name}'\n";
    if (!empty($s->json_str)) {
        $json = json_decode($s->json_str, true);
        echo "   JSON class_id: " . ($json['class_id'] ?? 'N/A') . " | JSON class_name: " . ($json['class_name'] ?? 'N/A') . " | JSON section_name: " . ($json['section_name'] ?? 'N/A') . "\n";
    }
}
