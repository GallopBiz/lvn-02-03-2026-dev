<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- TOTAL STUDENTS IN student_registration ---\n";
echo "Total rows: " . DB::connection('dynamic')->table('student_registration')->count() . "\n";

$archivedCounts = DB::connection('dynamic')->table('student_registration')
    ->select('is_archived', DB::raw('count(*) as cnt'))
    ->groupBy('is_archived')
    ->get();
print_r($archivedCounts->toArray());

$sample = DB::connection('dynamic')->table('student_registration')->limit(5)->get();
foreach ($sample as $s) {
    echo "ID: {$s->id} | Name: {$s->student_name} | is_archived: " . var_export($s->is_archived, true) . " | class_id: '{$s->class_id}' | class_name: '{$s->class_name}' | section_name: '{$s->section_name}'\n";
}
