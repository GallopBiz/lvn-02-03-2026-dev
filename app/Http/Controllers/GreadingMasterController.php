<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classname;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Validation\ValidationException;


class GreadingMasterController extends Controller
{
    public function index(){
        $stream = Grade::where('is_delete',0)->get();
        $studentclasses = Classname::select('id', 'class_name')->where('is_delete', 0)->get();
        $subjectTypes = $this->subjectTypes();
        // return view('backend.AcademicsModules.greadingmaster');
        return view('backend.AcademicsModules.greadingmaster', compact('stream', 'studentclasses', 'subjectTypes'));
    }


    // public function create(Request $request){
    //     Grade::create($request->post());
    //     return redirect()->route('greadingmaster')->with('success','  greading has been created successfully.');
    // }


    public function create(Request $request)
    {
        foreach ($this->gradePayloads($request) as $payload) {
            Grade::create($payload);
        }
    
        return redirect()->route('greadingmaster')->with('success', 'greading has been created successfully.');
    }
    








    public function view($id){
        $stream_master = Grade::whereId($id)->get();
        $stream = Grade::where('is_delete', 0)->orderBy('id','desc')->get();
        $studentclasses = Classname::select('id', 'class_name')->where('is_delete', 0)->get();
        $subjectTypes = $this->subjectTypes();
        // return $stream_master;
        return view('backend.AcademicsModules.greadingmaster', compact('stream_master','stream', 'studentclasses', 'subjectTypes'));
    }

    public function store(Request $request)
    {
        $payloads = $this->gradePayloads($request);
    
        if ($request->has('id')) {
            Grade::whereId($request->id)->update($payloads[0]);

            foreach (array_slice($payloads, 1) as $payload) {
                Grade::create($payload);
            }
        } else {
            foreach ($payloads as $payload) {
                Grade::create($payload);
            }
        }
    
        return redirect()->route('greadingmaster')->with('success', 'greading has been Updated successfully.');
    }

    private function gradePayloads(Request $request): array
    {
        $request->validate([
            'grading_name' => 'required|string|max:255',
            'subject_type' => 'required|string|max:50',
            'grade_ranges' => 'required|array|min:1',
            'grade_ranges.*.min_per' => 'required|numeric',
            'grade_ranges.*.max_per' => 'required|numeric',
            'grade_ranges.*.grade' => 'required|string|max:50',
            'groups' => 'nullable|string',
        ]);

        foreach ($request->grade_ranges as $range) {
            if ((float) $range['max_per'] < (float) $range['min_per']) {
                throw ValidationException::withMessages([
                    'grade_ranges' => 'To Grade Point should be greater than or equal to From Grade Point.',
                ]);
            }
        }

        return collect($request->grade_ranges)
            ->map(fn ($range) => [
                'grading_name' => $request->grading_name,
                'subject_type' => $request->subject_type,
                'applicable' => null,
                'min_per' => $range['min_per'],
                'max_per' => $range['max_per'],
                'grade' => $range['grade'],
                'groups' => $request->groups ?: json_encode([]),
            ])
            ->values()
            ->all();
    }

    private function subjectTypes()
    {
        $types = Subject::query()
            ->whereNotNull('subject_type')
            ->where('subject_type', '!=', '')
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        return $types
            ->merge(['Academic', 'Non Academic', 'Optional', 'Skilled'])
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->unique(fn ($type) => strtolower($type))
            ->sort()
            ->values();
    }
    
    
    public function grade_master_delete($id)
    {
        $a = explode('-',$id);
        $b = $a[1];

        if (Grade::whereId($b)->update(['is_delete' => 1])) {
            return redirect()->back()->with('success', 'Record successfully removed');
        }

        return redirect()->back()->with('error', 'Record not removed');
    }

    public function delete($id){
        $stream = Grade::findOrFail($id);
        $stream->delete();
        return redirect()->route('greadingmaster')->with('success','Deleted successfully.');
    }






}
