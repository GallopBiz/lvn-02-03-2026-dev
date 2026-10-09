<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Classname;

echo "--- TESTING FIXED QUERY (is_archived = 0 OR NULL) ---\n";

$classes = Classname::all();

foreach ($classes as $c) {
    $classId = $c->id;
    $className = $c->class_name;
    
    foreach (['Aryabhatta', 'Kautilya', 'Ramanujan'] as $section_name) {
        $studentsQuery = DB::connection('dynamic')->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)
                  ->orWhereNull('is_archived');
            })
            ->where(function ($query) use ($classId, $className) {
                $query->where('class_id', $classId)
                    ->orWhere('class_name', $className);
            })
            ->where(function ($query) use ($section_name) {
                $query->where('section_name', $section_name)
                    ->orWhereJsonContains('json_str->section_name', $section_name);
            });

        $count = $studentsQuery->count();
        if ($count > 0) {
            echo "SUCCESS: Class '$className' (ID: $classId) Sec '$section_name' -> FOUND $count active students!\n";
        }
    }
}
