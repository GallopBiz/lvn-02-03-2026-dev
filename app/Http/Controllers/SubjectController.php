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
use Illuminate\Support\Facades\Schema;


class SubjectController extends Controller
{
    public function index(Request $request){
        $this->ensureSpecialFormattingColumn();
        $datas = $this->filteredSubjects($request)->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $streams = Stream::where('is_delete',0)->get();
        $filters = $request->only(['subject_type', 'evaluation', 'practical']);
        $selectedSpecialClasses = old('special_formatting_classes', []);

        return view('backend.AcademicsModules.createsubject',compact('datas','studentclasses', 'streams', 'filters', 'selectedSpecialClasses'));
    }

    public function exportExcel(Request $request)
    {
        $this->ensureSpecialFormattingColumn();
        $subjects = $this->filteredSubjects($request)->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->get()->pluck('class_name', 'id')->all();
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
            $specialFormatted = 'No';
            if (!empty($subject->do_not_print_in_main_scholastic_area)) {
                $spClasses = is_array($subject->special_formatting_classes)
                    ? $subject->special_formatting_classes
                    : (json_decode($subject->special_formatting_classes ?? '[]', true) ?: []);
                if (!empty($spClasses)) {
                    $classLabels = array_map(fn ($id) => $studentclasses[$id] ?? $id, $spClasses);
                    $specialFormatted = 'Yes (' . implode(', ', $classLabels) . ')';
                } else {
                    $specialFormatted = 'Yes (None)';
                }
            }

            $rows[] = [
                $index + 1,
                $subject->subject_name,
                $this->formatSubjectClasses($subject),
                $subject->subject_type,
                $subject->practical,
                $specialFormatted,
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

    public function create(Request $request)
    {
        $this->ensureSpecialFormattingColumn();
        $subjectName = $request->input('subject_name');

        // Check if the subject already exists
        $existingSubject = Subject::where('subject_name', $subjectName)->where('is_delete', 0)->first();

        if ($existingSubject) {
            // Subject already exists, display an error message
            return redirect()->route('subjectmaster')->with('error', 'Subject already exists!');
        } else {
            $isSpecial = $request->boolean('special_subject_formatting');
            $specialClasses = $isSpecial
                ? array_values(array_filter((array) $request->input('special_formatting_classes', [])))
                : [];

            // Subject does not exist, proceed with insertion
            $subject = Subject::create([
                "subject_name" => $request->input('subject_name'),
                "subject_type" => $request->input('subject_type'),
                "evaluation" => $request->input('evaluation'),
                "practical" => $request->input('practical'),
                "do_not_print_in_main_scholastic_area" => $isSpecial,
                "special_formatting_classes" => !empty($specialClasses) ? json_encode($specialClasses) : null,
            ]);

            $classes = $request->input('classes', []);
            if (!empty($classes)) {
                foreach ($classes as $classId) {
                    $getClass = Classname::where('id', $classId)->where('is_delete', 0)->first();
                    if (!$getClass) continue;

                    $isSeniorClass = str_contains($getClass->class_name, '11') || str_contains($getClass->class_name, '12');
                    $streamIds = ($isSeniorClass && !empty($request->stream)) ? $request->stream : [null];

                    foreach ($streamIds as $streamId) {
                        ClassSubject::create([
                            'subject_id' => $subject->id,
                            'class_id' => $classId,
                            'stream_id' => $streamId,
                        ]);
                    }
                }
            }
            return redirect()->route('subjectmaster')->with('success', 'Subject has been created successfully.');
        }
    }

    public function view($id){
        $this->ensureSpecialFormattingColumn();
        $subject_s = Subject::with('Classname')->whereId($id)->get();
        $datas = Subject::with('Classname')->orderBy('id','desc')->paginate(5);
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $selectedClasses = $subject_s[0]->Classname->pluck('id')->toArray();
        $streams = Stream::where('is_delete',0)->get();
        $selectedStreams = $subject_s[0]->Classname->pluck('pivot.stream_id')->filter()->unique()->toArray();

        $selectedSpecialClasses = [];
        if (!empty($subject_s[0])) {
            $rawSpecial = $subject_s[0]->special_formatting_classes;
            if (is_array($rawSpecial)) {
                $selectedSpecialClasses = $rawSpecial;
            } elseif (is_string($rawSpecial) && trim($rawSpecial) !== '') {
                $decoded = json_decode($rawSpecial, true);
                $selectedSpecialClasses = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $rawSpecial)));
            }
        }

        return view('backend.AcademicsModules.createsubject',compact('subject_s','datas','studentclasses','selectedClasses','streams','selectedStreams','selectedSpecialClasses'));
    }

    public function store(Request $request){
        $this->ensureSpecialFormattingColumn();
        $isSpecial = $request->boolean('special_subject_formatting');
        $specialClasses = $isSpecial
            ? array_values(array_filter((array) $request->input('special_formatting_classes', [])))
            : [];

        $data = [
            'subject_name' => $request->subject_name,
            'subject_type' => $request->subject_type,
            'evaluation' => $request->evaluation,
            'practical' => $request->practical,
            'do_not_print_in_main_scholastic_area' => $isSpecial,
            'special_formatting_classes' => !empty($specialClasses) ? json_encode($specialClasses) : null,
        ];
        $subject = Subject::findOrFail($request->id);
        $subject->update($data);

        // Track IDs for cleanup (optional)
        $updatedIds = [];

        if (!empty($request->classes)) {
            foreach ($request->classes as $classId) {
                $class = Classname::where('id', $classId)->where('is_delete', 0)->first();
                if (!$class) continue;

                $isSeniorClass = str_contains($class->class_name, '11') || str_contains($class->class_name, '12');

                // Get applicable stream IDs or [null]
                $streamIds = ($isSeniorClass && !empty($request->stream)) ? $request->stream : [null];

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
        }

        // Cleanup: delete removed combinations
        ClassSubject::where('subject_id', $subject->id)
            ->whereNotIn('id', $updatedIds)
            ->delete();
        return redirect()->route('subjectmaster')->with('success','Subject has been Updated successfully.');
    }

    public function subjects_delete($id){
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

    private function ensureSpecialFormattingColumn(): void
    {
        try {
            if (Schema::connection('dynamic')->hasTable('subjectmaster') && !Schema::connection('dynamic')->hasColumn('subjectmaster', 'special_formatting_classes')) {
                \Illuminate\Support\Facades\DB::connection('dynamic')->statement(
                    "ALTER TABLE `subjectmaster` ADD COLUMN `special_formatting_classes` TEXT NULL AFTER `do_not_print_in_main_scholastic_area`"
                );
            }
        } catch (\Throwable $e) {
            // Ignore if column already exists or table issue
        }
    }
}
