<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Classname;
use App\Models\Subject;
use App\Http\Controllers\MarksController;
use Illuminate\Http\Request;

echo "--- TESTING MARKS CONTROLLER CLASSSTUDENTDATA ---\n";

$classes = Classname::all();
foreach ($classes as $c) {
    echo "Class ID: {$c->id} -> Name: '{$c->class_name}'\n";
}

echo "\n--- TESTING SUBJECT COMBINATIONS IN subject_assign_student ---\n";
$combinations = DB::connection('dynamic')->table('subject_assign_student')->limit(5)->get();
print_r($combinations->toArray());

echo "\n--- TESTING TEACHER SUBJECT ASSIGNMENTS ---\n";
$tsa = DB::table('teacher_subject_assignments')->limit(10)->get();
if ($tsa->isEmpty()) {
    echo "teacher_subject_assignments is EMPTY in default connection!\n";
} else {
    print_r($tsa->toArray());
}

$tsaDyn = DB::connection('dynamic')->table('teacher_subject_assignments')->limit(10)->get();
if ($tsaDyn->isEmpty()) {
    echo "teacher_subject_assignments is EMPTY in dynamic connection!\n";
} else {
    print_r($tsaDyn->toArray());
}
