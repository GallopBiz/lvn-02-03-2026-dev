<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Marks;
use App\Models\Subject;
use App\Models\Teachers;
use App\Models\Classname;
use App\Models\Tablemarks;
use App\Models\CommanModel;
use Illuminate\Http\Request;
use App\Models\TeacherSubject;
use App\Models\ExamSubjectMark;
use App\Models\InternalAssessmentMaster;
use App\Models\SubjectCombination;
use App\Http\Controllers\Controller;
use App\Models\SubjectAssignStudent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class MarksController extends Controller
{
    private ?array $marksColumns = null;

    // public function index(){T
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $staffEmployeeId = $this->staffEmployeeId();

        $stream = $this->accessibleMarksQuery()->get();//Marks::where('is_delete',0)->get();
        //$teacherlist = Teachers::select('teacher_name')->distinct()->get();
        $examslist = $this->accessibleAcademicExams();
        if ($this->isStaffMarksUser()) {
            $classlist = $this->accessibleTeacherSubjects()
                ->with('Class')
                ->groupBy('class_id')
                ->get()
                ->pluck('Class')
                ->filter()
                ->values();
            $subjectlist = $this->accessibleTeacherSubjects()
                ->with('Subject')
                ->groupBy('subject_id')
                ->get()
                ->pluck('Subject')
                ->filter()
                ->values();
        } else {
            $classlist = Classname::select('id', 'class_name')->where('is_delete', 0)->get();
            $subjectlist = Subject::select('id', 'subject_name', 'subject_type')->where('is_delete', 0)->get();
        }
        $teacherlist = $this->accessibleTeacherSubjects()->with('Teacher.biometricDetails')->groupBy('teacher_id')->get();
        $isStaffMarksUser = $this->isStaffMarksUser();
        $internalAssessments = $this->activeInternalAssessments();
        $gradeRanges = $this->activeGradeRanges();
        return view('backend.AcademicsModules.marks', compact('classlist','stream','teacherlist','examslist','subjectlist', 'isStaffMarksUser', 'staffEmployeeId', 'internalAssessments', 'gradeRanges'));
    }


    public function create(Request $request){
        $validator = $this->validateMarksRequest($request);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessAssignment($teacherId, $request->class_name, $request->section_name, $request->subject_name)) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to enter marks for this class/subject.'], 403);
        }
        if ($this->isStaffExamLocked($request->exam_name)) {
            return response()->json(['status' => 'error', 'message' => $this->lockedExamMessage()], 403);
        }
        if ($message = $this->validateStudentMarksAgainstExamConfig($request)) {
            return response()->json(['status' => 'error', 'message' => $message], 422);
        }
        $this->applyPtMarksConversionToRequest($request);
        $this->applyTotalMarksRoundingToRequest($request);
        $this->applyGradesToRequest($request);

        $data = [
            "teacher_id" => $teacherId,
            "class_id" => $request->class_name,
            "section_name" => $request->section_name,
            "exam_id" => $request->exam_name,
            "subject_id" => $request->subject_name,
            "Stream_id" => $request->stream_id ?? null,

        ];

        $matchingEntries = $this->sameMarksEntryQuery($teacherId, $request->class_name, $request->section_name, $request->exam_name, $request->subject_name)
            ->orderBy('id')
            ->get();
        $mark = $matchingEntries->first();
        if ($mark) {
            $mark->update($data);
            $duplicateIds = $matchingEntries->skip(1)->pluck('id');
            if ($duplicateIds->isNotEmpty()) {
                Marks::whereIn('id', $duplicateIds)->update(['is_delete' => 1]);
            }
        } else {
            $mark = Marks::create($data);
        }
        $id = $mark->id;

        $marksdata = $request->students;

        $this->saveStudentMarks($id, $marksdata);
        $response = ["status" => "success", "marks_id" => $id, "mode" => $mark->wasRecentlyCreated ? "created" : "updated"];
        return response()->json($response);


        // return "success";
        // Marks::create($request->post());
        // return redirect()->route('marks')->with('success','  marks has been created successfully.');
    }

    public function classstudentdata(Request $request){
        $section_name = $request->section_name;
        $subjectId = $request->subject_id;
        $classId = $request->class;

        if (!$this->canAccessAssignment($this->resolveTeacherId($request), $classId, $section_name, $subjectId)) {
            return response()->json(['students' => [], 'message' => 'You are not allowed to access this class/subject.'], 403);
        }
        if ($this->isStaffExamLocked($request->exam_id ?? $request->exam_name)) {
            return response()->json(['students' => [], 'message' => $this->lockedExamMessage()], 403);
        }

        $className = Classname::where('id', $classId)->value('class_name');
        if (!$className) {
            return ['students' => []];
        }

        $classLevel = $this->classLevel($className);
        $eligibleStudentIds = collect();
        $shouldApplyCombinationFilter = false;

        if ($classLevel >= 9) {
            $eligibleStudentIds = $this->studentIdsForSubjectCombination($className, $section_name, $subjectId);
            $hasSubjectAssignments = $eligibleStudentIds->isNotEmpty();

            if ($classLevel >= 11) {
                if (!$hasSubjectAssignments) {
                    return ['students' => []];
                }
                $shouldApplyCombinationFilter = true;
            } else {
                $shouldApplyCombinationFilter = $hasSubjectAssignments;
            }
        }

        $studentsQuery = DB::connection('dynamic')->table('student_registration')
            ->where(function ($query) use ($classId, $className) {
                $query->where('class_id', $classId)
                    ->orWhere('class_name', $className);
            })
            ->where(function ($query) use ($section_name) {
                $query->where('section_name', $section_name)
                    ->orWhereJsonContains('json_str->section_name', $section_name);
            });

        if ($shouldApplyCombinationFilter) {
            $studentsQuery->whereIn('id', $eligibleStudentIds);
        }

        $students = $studentsQuery->orderBy('student_name')->get();
        $rollMap = $this->studentRollMap($className, $section_name, $request->exam_id ?? $request->exam_name);
        $data['students'] = $students->map(function ($student) use ($rollMap) {
            $student->roll_no = $rollMap->get($student->id, '');
            return $student;
        });

        return $data;
    }

    public function checkMarksEntryStatus(Request $request)
    {
        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessAssignment($teacherId, $request->class_name, $request->section_name, $request->subject_name)) {
            return response()->json(['exists' => false, 'message' => 'You are not allowed to access this class/subject.'], 403);
        }

        $entry = $this->sameMarksEntryQuery($teacherId, $request->class_name, $request->section_name, $request->exam_name, $request->subject_name)->first();
        $isLocked = $this->isStaffExamLocked($request->exam_name);

        return response()->json([
            'exists' => (bool) $entry,
            'is_locked' => $isLocked,
            'marks_id' => $entry->id ?? null,
            'edit_url' => $entry ? url($this->marksRoutePrefix() . 'view-marks/' . $entry->id) : null,
            'message' => $isLocked ? $this->lockedExamMessage() : ($entry ? 'Marks entry is already completed for the selected class, section, exam and subject.' : null),
        ]);
    }

    public function grade_percentage(Request $request) {
        $percentage = $request->post('percentage');

        $arr = DB::connection('dynamic')->table('grademaster')->where('is_delete','=',0)->get();
        $data = "";

        foreach ($arr as $range) {
            $min = $range->min_per;
            $max = $range->max_per;
            $grade = $range->grade;

            if ($percentage >= $min && $percentage <= $max) {
                $data = $grade;
                break;  // Break out of the loop once a match is found
            }
        }

        return json_encode($data);
    }


    public function view($id){
        $stream_master = $this->accessibleMarksQuery()->where('id', $id)->firstOrFail();//Marks::where('is_delete',0)->get();
        $staffEmployeeId = $this->staffEmployeeId();
        $stream = $this->accessibleMarksQuery()->get();
        $teacherlist = $this->accessibleTeacherSubjects()->with('Teacher.biometricDetails')->groupBy('teacher_id')->get();
        $examslist = $this->accessibleAcademicExams();
        //$classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $classlist = Classname::select('class_name','id')->where('id',$stream_master->class_id)->first();
        $subjectlist = $this->accessibleTeacherSubjects()
            ->with('Subject')
            ->groupBy('subject_id')
            ->get()
            ->pluck('Subject')
            ->filter()
            ->values();
        $studentMarksTable = $this->studentMarksTable();
        $marksData = DB::connection('dynamic')->table($studentMarksTable)
            ->join('student_registration', $studentMarksTable . '.student_id', '=', 'student_registration.id')
            ->where($studentMarksTable . '.marks_id', $stream_master->id)
            ->select($studentMarksTable . '.*', 'student_registration.*') // Add fields as needed
            ->get();
        $rollMap = $this->studentRollMap($classlist->class_name ?? '', $stream_master->section_name ?? '', $stream_master->exam_id ?? null);
        $marksData = $marksData->map(function ($marks) use ($rollMap) {
            $marks->roll_no = $rollMap->get($marks->student_id, '');
            return $marks;
        });
            //dd($marksData);
        // return $stream_master;
        //dd($classlist);
        $isStaffMarksUser = $this->isStaffMarksUser();
        $internalAssessments = $this->activeInternalAssessments();
        $gradeRanges = $this->activeGradeRanges();
        return view('backend.AcademicsModules.marks', compact('marksData', 'classlist','stream_master','stream','teacherlist','examslist','subjectlist', 'isStaffMarksUser', 'staffEmployeeId', 'internalAssessments', 'gradeRanges'));
    }

    public function store(Request $request){
        $validator = $this->validateMarksRequest($request, true);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $marksId = $request->marks_id;
        $existingEntry = $this->accessibleMarksQuery()->where('id', $marksId)->first();
        if (!$existingEntry) {
            return response()->json(['status' => 'error', 'message' => 'Marks entry not found or access denied.'], 403);
        }
        if ($this->isStaffMarksUser() && $this->isMarksEntryLocked($existingEntry)) {
            return response()->json(['status' => 'error', 'message' => $this->lockedMarksEntryMessage()], 403);
        }

        $teacherId = $this->resolveTeacherId($request);
        if (!$this->canAccessAssignment($teacherId, $request->class_name, $request->section_name, $request->subject_name)) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to update marks for this class/subject.'], 403);
        }
        if ($this->isStaffExamLocked($request->exam_name)) {
            return response()->json(['status' => 'error', 'message' => $this->lockedExamMessage()], 403);
        }
        if ($message = $this->validateStudentMarksAgainstExamConfig($request)) {
            return response()->json(['status' => 'error', 'message' => $message], 422);
        }
        $this->applyPtMarksConversionToRequest($request);
        $this->applyTotalMarksRoundingToRequest($request);
        $this->applyGradesToRequest($request);

        $mainData = [
            "teacher_id" => $teacherId,
            "class_id" => $request->class_name,
            "section_name" => $request->section_name,
            "exam_id" => $request->exam_name,
            "subject_id" => $request->subject_name,
            "Stream_id" => $request->stream_id ?? null,
        ];
    
        Marks::where('id', $marksId)->update($mainData);
    
        $this->saveStudentMarks($marksId, $request->students);
    
        return response()->json(["status" => "success"]);
    }

    public function marks_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];
        $c = $a[0];
        $entry = $this->accessibleMarksQuery()->where('id', $b)->first();
        if (!$entry) {
            abort(403, 'You are not allowed to delete this marks entry.');
        }
        if ($this->isStaffMarksUser() && $this->isMarksEntryLocked($entry)) {
            return redirect()->route('staff.marks')->with('error', $this->lockedMarksEntryMessage());
        }
        if ($this->isStaffExamLocked($entry->exam_id)) {
            return redirect()->route('staff.marks')->with('error', $this->lockedExamMessage());
        }
        if ($c !== 'previosly_saved_marks_entry') {
            abort(400, 'Invalid marks table.');
        }

        $updated = DB::connection('dynamic')->table($c)->where('id', $b)->update(['is_delete' => 1]);
        if($updated){
            return redirect()->route($this->isStaffMarksUser() ? 'staff.marks' : 'marks')->with('success', 'Record successfully removed');
        }

        return redirect()->route($this->isStaffMarksUser() ? 'staff.marks' : 'marks')->with('error', 'Record not removed');
    }

    public function delete($id){
        $stream = $this->accessibleMarksQuery()->where('id', $id)->firstOrFail();
        $stream->delete();
        return redirect()->route('marks')->with('success','Deleted successfully.');
    }

    public function lockEntry($id)
    {
        if (!$this->isStaffMarksUser()) {
            abort(403, 'Only staff can lock marks entries.');
        }

        $entry = $this->accessibleMarksQuery()->where('id', $id)->firstOrFail();
        if ($this->isStaffExamLocked($entry->exam_id)) {
            return redirect()->route('staff.marks')->with('error', $this->lockedExamMessage());
        }

        $entry->update([
            'is_locked' => 1,
            'locked_at' => now(),
            'locked_by' => Auth::guard('staff')->id(),
        ]);

        return redirect()->route('staff.marks')->with('success', 'Marks entry locked successfully. Contact admin if changes are needed.');
    }

    public function unlockEntry($id)
    {
        if ($this->isStaffMarksUser()) {
            abort(403, 'Only admin can unlock marks entries.');
        }

        $entry = $this->accessibleMarksQuery()->where('id', $id)->firstOrFail();
        $entry->update([
            'is_locked' => 0,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return redirect()->route('marks')->with('success', 'Marks entry unlocked successfully.');
    }

    public function showmarks(){
        if ($this->isStaffMarksUser()) {
            $classIds = $this->accessibleClassIds();
            $classlist = Classname::select('id','class_name')->whereIn('id', $classIds)->get();
            $examslist = $this->accessibleAcademicExams()->unique('exam_name')->values();
        } else {
            $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
            $examslist = $this->accessibleAcademicExams()->unique('exam_name')->values();
        }
        return view('backend.AcademicsModules.showmarks',compact('classlist','examslist'));
    }

    public function show_report_markss(Request $request){
        $section_name = $request->post('section_name');
        $class_name = $request->post('classname');
        $term_name = $request->post('term_name');
        $report_type = $request->post('report_type');
        $exam_title = $request->post('exam_title');
        $best_of_two = $request->post('best_of_two');
        $pdf_check = $request->post('pdf_check');
        if ($this->isStaffMarksUser() && !$this->canAccessClassSectionByName($class_name, $section_name)) {
            abort(403, 'You are not allowed to view marks for this class/section.');
        }
        if (!empty($pdf_check)){
            echo "yes";
        } else {
            echo "no";
        }
        die();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $examslist = $this->accessibleAcademicExams()->unique('exam_name')->values();
        $studentmarkss = DB::connection('dynamic')
                ->table('previosly_saved_marks_entry')
                ->join($this->studentMarksTable(), 'previosly_saved_marks_entry.id', '=', $this->studentMarksTable() . '.marks_id')
                ->where('previosly_saved_marks_entry.class_name', '=', $class_name)
                ->where('section_name', '=', $section_name)
                ->Where('exam_name', '=', $term_name)
                ->get();
        $class_teacher = DB::connection('dynamic')->table('teacher_subjects')->select('teacher_name')
        ->where('class_name','=',$class_name)
        ->where('role','=','Class Teacher')
        ->first();
        $studentmarks = [];
        $studentmarks_grade = [];
        $studentmarks_total = [];
        $subject = [];
        $total = 0;
        $sum_marks = 0;
        // echo '<pre>';
        $studentmarks_total_sum = [];
        $studentmarks_total_sum_max = [];
        $student_grade = [];
        foreach($studentmarkss as $key => $student){
            $subject[$student->subject_name] = $student->max_marks;
            $studentmarks[$student->student_name][$student->subject_name] = $student->total_marks;
            $studentmarks_grade[$student->student_name][$student->subject_name] = $student->grade;
            if($student->subject_name != 'Sanskrit' && $student->subject_name != 'Computer Science'){
                $studentmarks[$student->student_name]['total'][] = $student->total_marks;
                $studentmarks[$student->student_name]['max_total'][] = $student->max_marks;
            }
            $studentmarks[$student->student_name]['scholar_no'] = $student->scholar_no;
            $studentmarks_total_sum[$student->student_name] = array_sum($studentmarks[$student->student_name]['total']);
            $studentmarks_total_sum_max[$student->student_name] = array_sum($studentmarks[$student->student_name]['max_total']);
            $student_grade[$student->student_name] = $this->check_grade(($studentmarks_total_sum[$student->student_name] / $studentmarks_total_sum_max[$student->student_name]) * 100);
            $studentmarks_total[$student->student_name][$student->subject_name] = $student->max_marks;
        }
        // print_r($studentmarks);die();
        // print_r($studentmarks_total_sum_max);

        return view('backend.AcademicsModules.showmarks',compact('report_type','term_name','class_name','section_name','class_teacher','studentmarks_grade','student_grade','studentmarks_total_sum_max','subject','classlist','examslist','studentmarks'));
    }

    public function check_grade($number){
        $arr = DB::connection('dynamic')->table('grademaster')->where('is_delete','=','0')->get();
        $data = "";

        foreach ($arr as $range) {
            $min = $range->min_per;
            $max = $range->max_per;
            $grade = $range->grade;

            if ($number >= $min && $number <= $max) {
                $data = $grade;
                break;  // Break out of the loop once a match is found
            }
        }
        return $data;
    }

    private function isStaffMarksUser(): bool
    {
        return Auth::guard('staff')->check() && !Auth::guard('web')->check();
    }

    private function marksRoutePrefix(): string
    {
        return $this->isStaffMarksUser() ? 'staff/' : '';
    }

    private function isStaffExamLocked($examId): bool
    {
        if (!$this->isStaffMarksUser() || empty($examId)) {
            return false;
        }

        if (!Schema::connection('dynamic')->hasTable('academic_exam') || !Schema::connection('dynamic')->hasColumn('academic_exam', 'is_locked')) {
            return false;
        }

        return DB::connection('dynamic')->table('academic_exam')
            ->where('id', $examId)
            ->where('is_locked', 1)
            ->exists();
    }

    private function lockedExamMessage(): string
    {
        return 'This exam is locked. Please contact admin to unlock it before editing or deleting marks.';
    }

    private function isMarksEntryLocked($entry): bool
    {
        return (bool) ($entry->is_locked ?? false);
    }

    private function lockedMarksEntryMessage(): string
    {
        return 'This marks entry is locked. Please contact admin to unlock it before editing or deleting marks.';
    }

    private function staffEmployeeId(): ?int
    {
        if (!$this->isStaffMarksUser()) {
            return null;
        }

        return (int) Auth::guard('staff')->user()->employee_id;
    }

    private function accessibleTeacherSubjects()
    {
        $query = TeacherSubject::where('is_delete', 0);

        if ($this->isStaffMarksUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        return $query;
    }

    private function accessibleMarksQuery()
    {
        $query = Marks::where('is_delete', 0);

        if ($this->isStaffMarksUser()) {
            $query->where('teacher_id', $this->staffEmployeeId());
        }

        return $query;
    }

    private function sameMarksEntryQuery($teacherId, $classId, $sectionName, $examId, $subjectId)
    {
        return $this->accessibleMarksQuery()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('section_name', $sectionName)
            ->where('exam_id', $examId)
            ->where('subject_id', $subjectId);
    }

    private function resolveTeacherId(Request $request): ?int
    {
        return $this->isStaffMarksUser()
            ? $this->staffEmployeeId()
            : (int) $request->teacher_name;
    }

    private function canAccessAssignment(?int $teacherId, $classId, $sectionName, $subjectId): bool
    {
        if (!$this->isStaffMarksUser()) {
            return true;
        }

        if (!$teacherId || !$classId || !$sectionName || !$subjectId) {
            return false;
        }

        return TeacherSubject::where('is_delete', 0)
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('section_name', 'All');
            })
            ->where('subject_id', $subjectId)
            ->exists();
    }

    private function validateMarksRequest(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'teacher_name' => $this->isStaffMarksUser() ? 'nullable' : 'required|integer',
            'class_name' => 'required|integer',
            'section_name' => 'required|string',
            'exam_name' => 'required|integer',
            'subject_name' => 'required|integer',
            'max_marks_theory' => 'nullable|numeric|min:0',
            'max_marks_practical' => 'nullable|numeric|min:0',
            'max_marks' => 'nullable|numeric|min:0',
            'students' => 'required|array',
            'students.*.student_id' => 'required|integer',
            'students.*.roll_no' => 'nullable|string|max:50',
            'students.*.scholar_no' => 'nullable|string|max:50',
            'students.*.marks' => 'nullable|numeric|min:0',
            'students.*.mark_theory' => 'nullable|numeric|min:0',
            'students.*.mark_practical' => 'nullable|numeric|min:0',
            'students.*.total_marks' => 'nullable|numeric|min:0',
            'students.*.grade' => 'nullable|string|max:20',
            'students.*.result' => 'nullable|string|in:D,S',
            'students.*.internal_assessments' => 'nullable|array',
            'students.*.internal_assessments.*' => 'nullable|numeric|min:0',
            'students.*.internal_assessment_marks' => 'nullable|string',
        ];

        if ($isUpdate) {
            $rules['marks_id'] = 'required|integer';
        }

        return Validator::make($request->all(), $rules);
    }

    private function validateStudentMarksAgainstExamConfig(Request $request): ?string
    {
        $maxTheory = $request->filled('max_marks_theory') ? (float) $request->max_marks_theory : null;
        $maxPractical = $request->filled('max_marks_practical') ? (float) $request->max_marks_practical : null;

        if ($maxTheory !== null || $maxPractical !== null) {
            foreach ($request->students as $student) {
                $theoryMarks = (float) ($student['mark_theory'] ?? 0);
                $practicalMarks = (float) ($student['mark_practical'] ?? 0);

                if ($maxTheory !== null && $maxTheory > 0 && $theoryMarks > $maxTheory) {
                    return 'Entered marks should not be greater than maximum marks.';
                }

                if ($maxPractical !== null && $maxPractical > 0 && $practicalMarks > $maxPractical) {
                    return 'Entered marks should not be greater than maximum marks.';
                }

                $configuredTotal = (float) ($maxTheory ?? 0) + (float) ($maxPractical ?? 0);
                if ($configuredTotal > 0 && ($theoryMarks + $practicalMarks) > $configuredTotal) {
                    return 'Entered marks should not be greater than maximum marks.';
                }
            }

            return null;
        }

        if ($request->filled('max_marks')) {
            $maxMarks = (float) $request->max_marks;
        } else {
            $maxMarks = null;
        }

        if ($maxMarks !== null && $maxMarks <= 0) {
            return null;
        }

        $subjectMark = ExamSubjectMark::where('exam_id', $request->exam_name)
            ->where('subject_id', $request->subject_name)
            ->first();

        if ($maxMarks === null && !$subjectMark) {
            $examMax = DB::connection('dynamic')->table('academic_exam')->where('id', $request->exam_name)->first();
            $maxMarks = (int) (($examMax->max_marks_theory ?? 0) + ($examMax->max_marks_practical ?? 0));
            if ($maxMarks <= 0) {
                return null;
            }
        } elseif ($maxMarks === null) {
            $maxMarks = (int) $subjectMark->max_marks;
        }

        foreach ($request->students as $student) {
            $marks = (float) ($student['mark_theory'] ?? 0) + (float) ($student['mark_practical'] ?? 0);
            if ($marks <= 0) {
                $marks = $student['marks'] ?? 0;
            }
            if ($marks > $maxMarks) {
                return 'Entered marks should not be greater than maximum marks.';
            }
        }

        return null;
    }

    private function applyPtMarksConversionToRequest(Request $request): void
    {
        $exam = DB::connection('dynamic')->table('academic_exam')->where('id', $request->exam_name)->first();
        if (!$exam || !$this->isPtExamName($exam->exam_name ?? '')) {
            return;
        }

        $maxMarks = $request->filled('max_marks')
            ? (float) $request->max_marks
            : (float) (($exam->max_marks_theory ?? 0) + ($exam->max_marks_practical ?? 0));

        if ($maxMarks <= 0) {
            return;
        }

        $students = collect($request->students ?? [])->map(function ($student) use ($maxMarks) {
            $obtainedMarks = (float) ($student['mark_theory'] ?? 0) + (float) ($student['mark_practical'] ?? 0);
            $student['total_marks'] = $this->roundMark(($obtainedMarks / $maxMarks) * 5);
            return $student;
        })->all();

        $request->merge(['students' => $students]);
    }

    private function isPtExamName(?string $examName): bool
    {
        return (bool) preg_match('/\bPT\s*[-]?\s*[12]\b/i', (string) $examName);
    }

    private function roundMark(float $value): float
    {
        return round($value, 0, PHP_ROUND_HALF_UP);
    }

    private function applyTotalMarksRoundingToRequest(Request $request): void
    {
        $students = collect($request->students ?? [])->map(function ($student) {
            if (is_numeric($student['total_marks'] ?? null)) {
                $student['total_marks'] = $this->roundMark((float) $student['total_marks']);
            }

            return $student;
        })->all();

        $request->merge(['students' => $students]);
    }

    private function applyGradesToRequest(Request $request): void
    {
        $className = Classname::where('id', $request->class_name)->value('class_name');
        $subjectType = Subject::where('id', $request->subject_name)->value('subject_type');

        $students = collect($request->students ?? [])->map(function ($student) use ($className, $subjectType) {
            $marks = is_numeric($student['total_marks'] ?? null)
                ? (float) $student['total_marks']
                : (float) (($student['mark_theory'] ?? 0) + ($student['mark_practical'] ?? 0));

            $student['grade'] = $this->gradeFromMaster($marks, $className, $subjectType);
            return $student;
        })->all();

        $request->merge(['students' => $students]);
    }

    private function gradeFromMaster(float $marks, ?string $className, ?string $subjectType): string
    {
        $subjectType = trim((string) $subjectType);
        if ($subjectType === '') {
            return '';
        }

        return $this->activeGradeRanges()
            ->first(function ($range) use ($marks, $className, $subjectType) {
                $classes = collect(json_decode($range->groups ?? '[]', true) ?: []);
                $classMatches = $classes->isEmpty() || $classes->contains($className);
                $subjectMatches = strcasecmp(trim((string) $range->subject_type), $subjectType) === 0;

                return $classMatches
                    && $subjectMatches
                    && $marks >= (float) $range->min_per
                    && $marks <= (float) $range->max_per;
            })
            ->grade ?? '';
    }

    private function accessibleAcademicExams()
    {
        if (!Schema::connection('dynamic')->hasTable('academic_exam')) {
            return collect();
        }

        $query = DB::connection('dynamic')->table('academic_exam as ae')
            ->leftJoin('classes as c', 'c.id', '=', 'ae.class_id')
            ->leftJoin('class_name as cn', 'cn.class_name', '=', 'c.class_name')
            ->select(
                'ae.*',
                'c.class_name as class_name_text',
                'cn.id as marks_class_id'
            );

        if (Schema::connection('dynamic')->hasColumn('academic_exam', 'deleted_at')) {
            $query->whereNull('ae.deleted_at');
        }

        if ($this->isStaffMarksUser()) {
            $query->whereIn('cn.id', $this->accessibleClassIds());
        }

        return $query
            ->orderBy('ae.id', 'desc')
            ->get();
    }

    private function accessibleClassIds()
    {
        return $this->accessibleTeacherSubjects()
            ->select('class_id')
            ->distinct()
            ->pluck('class_id')
            ->filter()
            ->values();
    }

    private function canAccessClassSectionByName(?string $className, ?string $sectionName): bool
    {
        $classId = Classname::where('class_name', $className)->value('id');

        if (!$classId || !$sectionName) {
            return false;
        }

        return TeacherSubject::where('is_delete', 0)
            ->where('teacher_id', $this->staffEmployeeId())
            ->where('class_id', $classId)
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('section_name', 'All');
            })
            ->exists();
    }

    private function classLevel(?string $className): int
    {
        $normalized = strtolower(trim((string) $className));

        if (preg_match('/\d+/', $normalized, $matches)) {
            return (int) $matches[0];
        }

        return match ($normalized) {
            'nursery', 'pre nursery', 'pre-nursery', 'kg1', 'kg 1', 'lkg' => -2,
            'kg2', 'kg 2', 'ukg' => -1,
            default => 0,
        };
    }

    private function studentIdsForSubjectCombination(string $className, string $sectionName, $subjectId)
    {
        return DB::connection('dynamic')->table('subject_assign_student as sas')
            ->join('combination_subject as cs', 'cs.subject_combination_id', '=', 'sas.assign_this_combtoall')
            ->where('sas.is_delete', 0)
            ->where('cs.subject_id', $subjectId)
            ->where('sas.class_name', $className)
            ->where(function ($query) use ($sectionName) {
                $query->where('sas.section_name', $sectionName)
                    ->orWhereNull('sas.section_name')
                    ->orWhere('sas.section_name', '');
            })
            ->whereNotNull('sas.students_details')
            ->pluck('sas.students_details')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    private function studentRollMap(string $className, string $sectionName, $examId)
    {
        if (!Schema::connection('dynamic')->hasTable('academic_student_roll_no')) {
            return collect();
        }

        return DB::connection('dynamic')->table('academic_student_roll_no')
            ->where('class_id', $className)
            ->where('section_id', $sectionName)
            ->where('session_id', $this->activeSessionName())
            ->when($examId, fn ($query) => $query->where('exam_id', $examId))
            ->pluck('roll_no', 'student_id');
    }

    private function activeSessionName(): string
    {
        return request()->session()->get('selectedYear')
            ?? request()->cookie('selectedYear')
            ?? config('database.connections.dynamic.database');
    }

    private function filterMarksColumns(array $payload): array
    {
        if ($this->marksColumns === null) {
            $this->marksColumns = Schema::connection('dynamic')->getColumnListing($this->studentMarksTable());
        }

        $filtered = [];
        foreach ($payload as $column => $value) {
            if (in_array($column, $this->marksColumns, true)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function saveStudentMarks(int $marksId, ?array $marksdata): void
    {
        if (empty($marksdata)) {
            return;
        }

        $studentMarksTable = $this->studentMarksTable();
        $existingRows = DB::connection('dynamic')->table($studentMarksTable)
            ->where('marks_id', $marksId)
            ->whereIn('student_id', collect($marksdata)->pluck('student_id')->filter()->values())
            ->pluck('id', 'student_id');

        $insertRows = [];
        DB::connection('dynamic')->transaction(function () use ($marksId, $marksdata, $existingRows, $studentMarksTable, &$insertRows) {
            foreach ($marksdata as $mdata) {
                $internalAssessmentMarks = $this->normalizeInternalAssessmentMarks($mdata);
                $studentMark = $this->filterMarksColumns([
                    "marks_id" => $marksId,
                    "student_id" => $mdata['student_id'],
                    "roll_no" => $mdata['roll_no'] ?? null,
                    "scholar_no" => $mdata['scholar_no'] ?? null,
                    "is_absent" => $mdata['is_absent'] ?? 0,
                    "is_absent_pr" => $mdata['is_absent_pr'] ?? 0,
                    "subject_marks" => $mdata['total_marks'] ?? $mdata['marks'] ?? 0,
                    "mark_theory" => $mdata['mark_theory'] ?? 0,
                    "mark_practical" => $mdata['mark_practical'] ?? 0,
                    "total_marks" => $mdata['total_marks'] ?? 0,
                    "grade" => $mdata['grade'] ?? null,
                    "result" => $mdata['result'] ?? null,
                    "overall_grade" => $mdata['grade'] ?? null,
                    "internal_assessment_marks" => $internalAssessmentMarks,
                ]);

                $existingId = $existingRows->get($mdata['student_id']);
                if ($existingId) {
                    DB::connection('dynamic')->table($studentMarksTable)->where('id', $existingId)->update($studentMark);
                } else {
                    $insertRows[] = $studentMark;
                }
            }

            if (!empty($insertRows)) {
                DB::connection('dynamic')->table($studentMarksTable)->insert($insertRows);
            }
        });
    }

    private function normalizeInternalAssessmentMarks(array $mdata): ?string
    {
        $marks = $mdata['internal_assessments'] ?? null;

        if (empty($marks) && !empty($mdata['internal_assessment_marks'])) {
            $decoded = json_decode($mdata['internal_assessment_marks'], true);
            $marks = is_array($decoded) ? $decoded : null;
        }

        if (empty($marks) || !is_array($marks)) {
            return null;
        }

        $normalized = [];
        foreach ($marks as $code => $value) {
            $normalized[$code] = $value === '' ? null : $value;
        }

        return json_encode($normalized);
    }

    private function studentMarksTable(): string
    {
        return Schema::connection('dynamic')->hasTable('academic_students_marks')
            ? 'academic_students_marks'
            : 'marks';
    }

    private function activeInternalAssessments()
    {
        if (!Schema::connection('dynamic')->hasTable('academic_internal_assessment_master')) {
            return collect();
        }

        return InternalAssessmentMaster::where('is_delete', 0)
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('assessment_name')
            ->get();
    }

    private function activeGradeRanges()
    {
        if (!Schema::connection('dynamic')->hasTable('grademaster')) {
            return collect();
        }

        return DB::connection('dynamic')->table('grademaster')
            ->where('is_delete', 0)
            ->orderByRaw('CAST(min_per AS DECIMAL(10,2)) DESC')
            ->get();
    }
}
