<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Stream;
use App\Models\ExamType;
use App\Models\Classname;
use App\Models\Exammaster;
use App\Models\CommanModel;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use App\Models\ExamSubjectMark;
use App\Models\SubjectCombination;
use App\Http\Controllers\Controller;
use App\Models\Student_registration;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Config;

class ExamMasterController extends Controller
{
    // public function index()
    // {
    //     $exammasters = Exammaster::where('is_delete', 0)->get();
    //     $exammasters = Exammaster::where('is_delete', 0)->distinct()->get();
    //     $studentclasses = DB::table('classes')->select('class_name')->get();//Student_registration::select('class_name')->distinct()->get();

    //     // $exammasters = Exammaster::All();
    //     $examtypelist = ExamType::select('examtype')->distinct()->get();

    //     return view('backend.AcademicsModules.exammaster', compact('exammasters', 'studentclasses','examtypelist'));
    // }


 public function index()
    {
        $exammasters = Exammaster::with(['Classname', 'examType', 'Stream'])->where('is_delete', 0)->get();
        $examtypelist = ExamType::where('is_delete', 0)->get();
        //$studentclasses = DB::table('classes')->select('class_name')->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $streams = Stream::where('is_delete',0)->get();
        return view('backend.AcademicsModules.exammaster', compact('exammasters', 'studentclasses', 'examtypelist', 'streams'));
    }



    public function create(Request $request)
    {
        $request->validate([
            'exam_name' => 'required|string',
            'exam_type' => 'required|integer',
            'classes' => 'required|integer',
            'min_marks' => 'required|array',
            'max_marks' => 'required|array',
        ]);
        $classId = $request->classes;
        $class = Classname::find($request->classes);
        $examType = $request->exam_type;
        $streamId = in_array($class->class_name, ['11', '12']) ? ($stream->id ?? null) : null;
        $existingExam = ExamMaster::where('class_id', $classId)
            ->where('exam_type', $examType)
            ->where('stream_id', $streamId)
            ->first();

        if ($existingExam) {
            return response()->json([
                'status' => 'error',
                'message' => 'An exam of this type already exists for the selected class.'
            ]);
        }
        // Save main exam master
        $exam = ExamMaster::create([
            'exam_name' => $request->exam_name,
            'exam_type' => $request->exam_type,
            'class_id' => $request->classes,
            'stream_id' => $request->stream,
        ]);
        // Loop through subjects
        foreach ($request->min_marks as $subjectId => $minMark) {
            $maxMark = $request->max_marks[$subjectId] ?? null;

            if ($maxMark !== null) {
                ExamSubjectMark::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectId,
                    'min_marks' => $minMark,
                    'max_marks' => $maxMark,
                ]);
            }
        }

        return response()->json([
                'status' => 'success','message' => 'Exam created successfully.']);
        //return 'success';
    }

    public function view($id)
    {
        $exam_master = Exammaster::whereId($id)->first();
        $exammasters = Exammaster::where('is_delete', 0)->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $examtypelist = ExamType::where('is_delete', 0)->get();
        $examsubjectMarks = ExamSubjectMark::where('exam_id', $id)->get();
        $streams = Stream::where('is_delete',0)->get();
        return view('backend.AcademicsModules.exammaster', compact('exam_master', 'exammasters', 'studentclasses','examtypelist', 'examsubjectMarks', 'streams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_name' => 'required|string',
            'min_marks' => 'required|array',
            'max_marks' => 'required|array',
        ]);

        // Fetch the exam record
        $exam = ExamMaster::find($request->id);
        if (!$exam) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exam not found.'
            ]);
        }

        // ❗ Only update exam name, keep the rest unchanged
        $exam->update([
            'exam_name' => $request->exam_name,
        ]);

        // Handle subject marks update or create
        foreach ($request->min_marks as $subjectId => $minMark) {
            $maxMark = $request->max_marks[$subjectId] ?? null;

            if ($maxMark === null) continue;

            $mark = ExamSubjectMark::where('exam_id', $exam->id)
                ->where('subject_id', $subjectId)
                ->first();

            if ($mark) {
                $mark->update([
                    'min_marks' => $minMark,
                    'max_marks' => $maxMark,
                ]);
            } else {
                ExamSubjectMark::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectId,
                    'min_marks' => $minMark,
                    'max_marks' => $maxMark,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Exam updated successfully.',
        ]);
        //return redirect()->route('exammaster')->with('success', ' Updated successfully.');

    }

    public function exam_master_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id)
    {
        $exammaster = Exammaster::findOrFail($id);
        $exammaster->delete();
        return redirect()->route('exammaster')->with('success', ' Deleted successfully.');
    }

    public function fetch_subjects_classwise_for_exam(Request $request)
    {
        $classId = $request->input('class_id');
        $streamId = $request->input('stream_id');
        $classSubjects = ClassSubject::with(['subject' => function ($query) {
                $query->where('is_delete', 0);
            }])
            ->where('class_id', $request->class_id);
        if (!empty($streamId)) {
            $classSubjects->where('stream_id', $streamId);
        } else {
            $classSubjects->whereNull('stream_id');
        }
        $classSubjects =  $classSubjects->get();
        $formatted = $classSubjects->map(function ($item) {
            return [
                'class_subject_id' => $item->id,
                'class_id' => $item->class_id,
                'subject_id' => $item->subject_id,
                'subject_name' => $item->subject->subject_name ?? 'N/A',
                'practical' => $item->subject->practical ?? 'No',
            ];
        });

        return response($formatted);
    }

}
