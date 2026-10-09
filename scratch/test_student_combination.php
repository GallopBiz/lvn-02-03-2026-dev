<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Classname;
use App\Models\Subject;

echo "--- TESTING ALL SUBJECTS & COMBINATIONS ---\n";

$subjects = Subject::where('is_delete', 0)->get();
echo "Total Subjects: " . $subjects->count() . "\n";

foreach (['09', '10', '11', '12'] as $clsName) {
    foreach (['Aryabhatta', 'Kautilya', 'Ramanujan'] as $sec) {
        foreach ($subjects as $sub) {
            $ids = DB::connection('dynamic')->table('subject_assign_student as sas')
                ->join('combination_subject as cs', 'cs.subject_combination_id', '=', 'sas.assign_this_combtoall')
                ->where('sas.is_delete', 0)
                ->where('cs.subject_id', $sub->id)
                ->where('sas.class_name', $clsName)
                ->where(function ($query) use ($sec) {
                    $query->where('sas.section_name', $sec)
                        ->orWhereNull('sas.section_name')
                        ->orWhere('sas.section_name', '');
                })
                ->whereNotNull('sas.students_details')
                ->pluck('sas.students_details')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($ids->isNotEmpty()) {
                echo "Class '$clsName' Sec '$sec' Sub '{$sub->subject_name}' (ID: {$sub->id}) -> Found " . $ids->count() . " eligible students\n";
            }
        }
    }
}
