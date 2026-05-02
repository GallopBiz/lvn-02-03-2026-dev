<?php
namespace App\Academic\Repositories;

use App\Models\Academic\Exam;
use App\Models\Academic\ExamClassSection;
use App\Models\Academic\ExamSubjectTeacher;

class ExamRepository
{
    public function createExam(array $data)
    {
        return Exam::create($data);
    }

    public function createExamClassSection(array $data)
    {
        return ExamClassSection::create($data);
    }

    public function createExamSubjectTeacher(array $data)
    {
        return ExamSubjectTeacher::create($data);
    }
}
