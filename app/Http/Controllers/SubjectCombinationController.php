<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Stream;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Classname;
use App\Models\CommanModel;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use App\Models\SubjectCombination;
use App\Http\Controllers\Controller;

class SubjectCombinationController extends Controller
{
    public function index()
    {
        $stream = SubjectCombination::where('is_delete', 0)->get();
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $subjects = Subject::all();
        $streamlist = Stream::where('is_delete', 0)->distinct()->get();
        $combinations = SubjectCombination::with(['class', 'subjects'])->where('is_delete', 0)->get();
        return view('backend.AcademicsModules.subjectcombination', compact('combinations','stream', 'streamlist', 'studentclasses', 'subjects'));
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'combination_name' => 'required',
            'alise_name' => 'required',
            'streams' => 'required',
        ]);

        SubjectCombination::create($data);
        return redirect()->route('subjectcombinatiomaster')->with('success', 'Subject combination master has been created successfully.');
    }

    public function view($id)
    {
        $studentclasses = Classname::select('id','class_name')->where('is_delete', 0)->distinct()->get();
        $subjects = Subject::all();
        $streamlist = Stream::select(['id','streams'])->distinct()->get();
        $single_combinations = SubjectCombination::with(['class', 'subjects'])->whereId($id)->first();
        //dd($single_combinations);
        return view('backend.AcademicsModules.subjectcombination', compact('single_combinations','studentclasses','streamlist'));
    }

    public function fetchSubjects(Request $request)
    {
        $classId = $request->input('class_id');
        $streamId = $request->input('stream_id');
        $query = ClassSubject::whereIn('class_id', $classId);
        if (!empty($streamId)) {
            $query->where(function ($q) use ($streamId) {
                $q->where('stream_id', $streamId)
                ->orWhereNull('stream_id');
            });
        } else {
            $query->whereNull('stream_id');
        }
        $subjectIds = $query->pluck('subject_id');
        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('is_delete', 0)
            ->get()
            ->groupBy('subject_type');
        return response()->json([
            'academic' => $subjects->get('Academic', []),
            'non_academic' => $subjects->get('Non Academic', []),
            'skilled' => $subjects->get('Skilled', []),
            'optional' => $subjects->get('Optional', []),
        ]);
    }


    public function store(Request $request)
    {
        //dd($request);
        $academicSubjects = json_decode($request->input('academic_subjects'), true);
        $nonAcademicSubjects = json_decode($request->input('non_academic_subjects'), true);
        $skilledSubjects = json_decode($request->input('skilled_subjects'), true);
        $optionalSubjects = json_decode($request->input('optional_subjects'), true);
        $allSubjects = [];

        foreach ($academicSubjects as $subject) {
            $subjectId = $subject['subject_id'];
            $order = $subject['order'] ?? null;
            $allSubjects[$subjectId] = ['subject_order' => $order];
        }

        foreach ($nonAcademicSubjects as $subject) {
            $subjectId = $subject['subject_id'];
            $order = $subject['order'] ?? null;
            $allSubjects[$subjectId] = ['subject_order' => $order];
        }

        foreach ($skilledSubjects as $subject) {
            $subjectId = $subject['subject_id'];
            $order = $subject['order'] ?? null;
            $allSubjects[$subjectId] = ['subject_order' => $order];
        }
        foreach ($optionalSubjects as $subject) {
            $subjectId = $subject['subject_id'];
            $order = $subject['order'] ?? null;
            $allSubjects[$subjectId] = ['subject_order' => $order];
        }
        if ($request->id) {
            $subjectCombination = SubjectCombination::find($request->id);

            if (!$subjectCombination) {

                return response()->json(['error' => "Subject combination not found"]);
            }
            $classIdString = implode(',', $request->classes);
            $subjectCombination->update([
                'combination_name' => $request->input('combination_name'),
                'class_id' => $classIdString,
                'streams' => $request->input('streams'),
            ]);
            $subjectCombination->subjects()->sync($allSubjects);
            return response()->json(['message' => "Subject combination updated successfully"]);
        } else {
            $existingCombination = $this->checkIfCombinationExists($allSubjects);

            if (!empty($existingCombination)) {
                return response()->json(['error' => 'A combination with the subjects already exists.'], 422);
            }
            $classIdString = implode(',', $request->classes);
            $newSubjectCombination = new SubjectCombination([
                'combination_name' => $request->input('combination_name'),
                'class_id' => $classIdString,
                'streams' => $request->input('streams'),
            ]);
            $newSubjectCombination->save();
            $newSubjectCombination->subjects()->attach($allSubjects);
            return response()->json(['message' => "Subject combination created successfully"]);
        }
    }

    public function checkIfCombinationExists(array $newSubjectIds)
    {
        $newSubjectIds = collect($newSubjectIds)->sort()->values()->toArray();

        $existingCombinations = SubjectCombination::with('subjects')->get();

        foreach ($existingCombinations as $combination) {
            $existingSubjectIds = $combination->subjects->pluck('id')->sort()->values()->toArray();

            if ($existingSubjectIds == $newSubjectIds) {
                return $combination;
            }
        }
        return null; // No matching combination
    }

    public function subjectcombinatio_master_delete($id)
    {
        $table = 'subject_combinations';
        $delete_resp = CommanModel::soft_delete($table, ['id' => $id]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function subject_delete(Request $request)
    {
        $table = $request->table_name;
        $delete_resp = CommanModel::soft_delete($table, ['id' => $request->delete_id]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id)
    {
        $stream = SubjectCombination::findOrFail($id);
        $stream->delete();
        return redirect()->route('subjectcombinatiomaster')->with('success', 'Deleted successfully.');
    }
}
