<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentMarksTable = 'academic_students_marks';
try {
    $res = DB::connection('dynamic')
        ->table('previosly_saved_marks_entry')
        ->join($studentMarksTable, 'previosly_saved_marks_entry.id', '=', $studentMarksTable . '.marks_id')
        ->join('student_registration', $studentMarksTable . '.student_id', '=', 'student_registration.id')
        ->leftJoin('academic_exam', 'previosly_saved_marks_entry.exam_id', '=', 'academic_exam.id')
        ->leftJoin('classes', 'previosly_saved_marks_entry.class_id', '=', 'classes.id')
        ->leftJoin('subjectmaster', 'previosly_saved_marks_entry.subject_id', '=', 'subjectmaster.id')
        ->where('previosly_saved_marks_entry.is_delete', 0)
        ->select(
            'previosly_saved_marks_entry.*',
            $studentMarksTable . '.*',
            'student_registration.student_name',
            'student_registration.scholar_no',
            'subjectmaster.subject_name',
            'academic_exam.exam_name',
            'academic_exam.exam_title',
            DB::raw('COALESCE(academic_exam.max_marks_theory, 0) + COALESCE(academic_exam.max_marks_practical, 0) as max_marks')
        )->get();

    echo "Query executed successfully! Total rows: " . count($res) . "\n";
} catch (\Exception $e) {
    echo "Query Error: " . $e->getMessage() . "\n";
}
