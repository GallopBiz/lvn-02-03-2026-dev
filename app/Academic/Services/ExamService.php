<?php
namespace App\Academic\Services;

use App\Academic\Repositories\ExamRepository;
use Illuminate\Support\Facades\DB;

class ExamService
{
    protected $repo;

    public function __construct(ExamRepository $repo)
    {
        $this->repo = $repo;
    }

    public function createExamWithDetails(array $data)
    {
        return DB::transaction(function () use ($data) {
            $exam = $this->repo->createExam([
                'exam_name'     => $data['exam_name'],
                'exam_type_id'  => $data['exam_type_id'],
                'session_year'  => $data['session_year'],
                'created_by'    => $data['created_by'] ?? null,
            ]);

            foreach ($data['class_sections'] as $cs) {
                $classSection = $this->repo->createExamClassSection([
                    'exam_id'    => $exam->id,
                    'class_id'   => $cs['class_id'],
                    'section_id' => $cs['section_id'],
                    'stream_id'  => $cs['stream_id'] ?? null,
                ]);

                foreach ($cs['subjects'] as $subject) {
                    $this->repo->createExamSubjectTeacher([
                        'exam_class_section_id' => $classSection->id,
                        'subject_id'            => $subject['subject_id'],
                        'teacher_id'            => $subject['teacher_id'],
                    ]);
                }
            }
            return $exam;
        });
    }
}
