<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Classname;

use App\Models\CommanModel;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;


class SubjectController extends Controller
{
    public function index(Request $request){
        $datas = $this->filteredSubjects($request)->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $streams = Stream::where('is_delete',0)->get();
        $filters = $request->only(['subject_type', 'evaluation', 'practical']);

        return view('backend.AcademicsModules.createsubject',compact('datas','studentclasses', 'streams', 'filters'));
    }

    public function exportExcel(Request $request)
    {
        $subjects = $this->filteredSubjects($request)->get();
        $rows = [];
        $rows[] = [
            'Sr.',
            'Subject Name',
            'Classes Name',
            'Subject Type',
            'Is Practical Type',
            'Special Subject Formatting',
            'Evaluation',
        ];

        foreach ($subjects as $index => $subject) {
            $rows[] = [
                $index + 1,
                $subject->subject_name,
                $this->formatSubjectClasses($subject),
                $subject->subject_type,
                $subject->practical,
                !empty($subject->do_not_print_in_main_scholastic_area) ? 'Yes' : 'No',
                $subject->evaluation,
            ];
        }

        $html = '<table border="1">';
        foreach ($rows as $rowIndex => $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $tag = $rowIndex === 0 ? 'th' : 'td';
                $html .= '<' . $tag . '>' . e($cell) . '</' . $tag . '>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        $filename = 'subject-master-' . date('Ymd_His') . '.xls';

        return Response::make($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function filteredSubjects(Request $request)
    {
        return Subject::with('Classname')
            ->where('is_delete', 0)
            ->when($request->filled('subject_type'), function ($query) use ($request) {
                $query->where('subject_type', $request->input('subject_type'));
            })
            ->when($request->filled('evaluation'), function ($query) use ($request) {
                $query->where('evaluation', $request->input('evaluation'));
            })
            ->when($request->filled('practical'), function ($query) use ($request) {
                if ($request->input('practical') === 'Yes') {
                    $query->where('practical', 'Yes');
                } elseif ($request->input('practical') === 'No') {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNull('practical')
                            ->orWhere('practical', '')
                            ->orWhere('practical', '!=', 'Yes');
                    });
                }
            })
            ->orderBy('id', 'desc');
    }

    private function formatSubjectClasses(Subject $subject)
    {
        return $subject->Classname->map(function ($class) {
            $streamName = Stream::find($class->pivot->stream_id)?->streams;
            $className = $class->class_name . ($class->section_name ? ' - ' . $class->section_name : '');

            return $streamName ? $className . ' (' . $streamName . ')' : $className;
        })->implode(', ');
    }

    // public function create(Request $request){
    //     Subject::create($request->post());
    //     return redirect()->route('subjectmaster')->with('success',' has been created successfully.');
    // }
    public function create(Request $request)
    {
        //dd($request);
        // Get subject name from the form
        $subjectName = $request->input('subject_name');

        // Check if the subject already exists
        $existingSubject = Subject::where('subject_name', $subjectName)->where('is_delete',0)->first();

        if ($existingSubject) {
            // Subject already exists, display an error message
            return redirect()->route('subjectmaster')->with('error', 'Subject already exists!');
        } else {
            // Subject does not exist, proceed with insertion
            $subject = Subject::create([
                "subject_name" => $request->input('subject_name'),
                "subject_type" => $request->input('subject_type'),
                "evaluation" => $request->input('evaluation'),
                "practical" => $request->input('practical'),
                "do_not_print_in_main_scholastic_area" => $request->boolean('special_subject_formatting'),
            ]);
            $classes = $request->input('classes');
            foreach ($classes as $classId) {
                $getClass = Classname::where('id', $classId)->where('is_delete', 0)->first();

                if ($getClass && str_contains($getClass->class_name, '11') || str_contains($getClass->class_name, '12')) {
                    foreach ($request->stream as $streamId) {
                        ClassSubject::create([
                            'subject_id' => $subject->id,
                            'class_id' => $classId,
                            'stream_id' => $streamId,
                        ]);
                    }
                } else {
                    ClassSubject::create([
                        'subject_id' => $subject->id,
                        'class_id' => $classId,
                        'stream_id' => null,
                    ]);
                }
            }
            return redirect()->route('subjectmaster')->with('success', 'Subject has been created successfully.');
        }
    }

    public function view($id){
        $subject_s = Subject::with('Classname')->whereId($id)->get();
        $datas = Subject::with('Classname')->orderBy('id','desc')->paginate(5);
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $selectedClasses = $subject_s[0]->Classname->pluck('id')->toArray();
        $streams = Stream::where('is_delete',0)->get();
        $selectedStreams = $subject_s[0]->Classname->pluck('pivot.stream_id')->filter()->unique()->toArray();

        return view('backend.AcademicsModules.createsubject',compact('subject_s','datas','studentclasses','selectedClasses','streams','selectedStreams'));
    }

    public function store(Request $request){
        $data = [
            'subject_name' => $request->subject_name,
            'subject_type' => $request->subject_type,
            'evaluation' => $request->evaluation,
            'practical' => $request->practical,
            'do_not_print_in_main_scholastic_area' => $request->boolean('special_subject_formatting'),
        ];
        $subject = Subject::findOrFail($request->id);
        $subject->update($data);

        // Track IDs for cleanup (optional)
        $updatedIds = [];

        foreach ($request->classes as $classId) {
            $class = Classname::where('id', $classId)->where('is_delete', 0)->first();
            if (!$class) continue;

            $isSeniorClass = str_contains($class->class_name, '11') || str_contains($class->class_name, '12');

            // Get applicable stream IDs or [null]
            $streamIds = $isSeniorClass ? $request->stream : [null];

            foreach ($streamIds as $streamId) {
                $classSubject = ClassSubject::firstOrNew([
                    'subject_id' => $subject->id,
                    'class_id'   => $classId,
                    'stream_id'  => $streamId,
                ]);

                // Save if new or force updated_at
                if (!$classSubject->exists || $classSubject->isDirty()) {
                    $classSubject->save();
                }

                $updatedIds[] = $classSubject->id;
            }
        }

        // Optional cleanup: delete removed combinations
        ClassSubject::where('subject_id', $subject->id)
            ->whereNotIn('id', $updatedIds)
            ->delete();
        return redirect()->route('subjectmaster')->with('success','Subject has been Updated successfully.');
    }

    public function subjects_delete($id){
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
}
