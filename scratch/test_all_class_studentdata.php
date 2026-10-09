<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Classname;
use App\Models\Subject;

$classes = Classname::all();
$subjects = Subject::where('is_delete', 0)->get();

echo "--- TESTING STUDENT DATA FETCH FOR ALL CLASSES & SECTIONS ---\n";

foreach ($classes as $c) {
    $classId = $c->id;
    $className = $c->class_name;
    
    foreach (['Aryabhatta', 'Kautilya', 'Ramanujan'] as $section_name) {
        $subjectId = $subjects->first()->id; // e.g. first subject

        $studentsQuery = DB::connection('dynamic')->table('student_registration')
            ->where('is_archived', 0)
            ->where(function ($query) use ($classId, $className) {
                $query->where('class_id', $classId)
                    ->orWhere('class_name', $className);
            })
            ->where(function ($query) use ($section_name) {
                $query->where('section_name', $section_name)
                    ->orWhereJsonContains('json_str->section_name', $section_name);
            });

        $count = $studentsQuery->count();
        if ($count == 0) {
            echo "FAILED: Class '$className' (ID: $classId) Sec '$section_name' -> 0 students found!\n";
        } else {
            echo "OK: Class '$className' (ID: $classId) Sec '$section_name' -> $count students found.\n";
        }
    }
}
