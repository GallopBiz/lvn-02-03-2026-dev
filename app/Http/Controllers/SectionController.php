<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Classname;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\CommanModel;
use App\Models\Student_registration;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    public function index(){
        $section = Section::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.section', compact('section'));
    }

    public function create(Request $request){
        Section::create($request->post());
        return redirect()->route('sectionmaster')->with('success',' has been created successfully.');
    }

    public function view($id){
        $section_master = Section::whereId($id)->get();
        $section = Section::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.section', compact('section_master','section'));
    }

    public function store(Request $request){
        $data = [
            'section' => $request->section,
            'remark' => $request->remark,
        ];
        Section::whereId($request->id)->update($data);
        return redirect()->route('sectionmaster')->with('success','Section has been Updated successfully.');
    }


    public function section_master_delete($id)
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

    public function delete($id){
        $section = Section::findOrFail($id);
        $section->delete();
        return redirect()->route('sectionmaster')->with('success','Deleted successfully.');
    }

    public function sectionAssign()
    {
        $preOrder = ['Nursery', 'KG1', 'KG2', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $classes = Classname::where('is_delete', 0)->get()
            ->sort(function ($a, $b) use ($preOrder) {
                $posA = array_search($a->class_name, $preOrder);
                $posB = array_search($b->class_name, $preOrder);
                if ($posA !== false && $posB !== false) return $posA <=> $posB;
                if ($posA !== false) return -1;
                if ($posB !== false) return 1;
                return strnatcasecmp($a->class_name, $b->class_name);
            })
            ->values();

        $students1 = Student_registration::query()
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 't');
            })
            ->get();

        // Group by class_name and section_name
        $classSectionCounts = $students1
            ->groupBy(function ($student) {
                $cn = strtoupper(trim($student->class_name ?? ''));
                if ($cn === 'KG1') return 'KG1';
                if ($cn === 'KG2') return 'KG2';
                return $student->class_name;
            })
            ->map(function ($classGroup) {
                $counts = [
                    'Aryabhatta' => 0,
                    'Kautilya' => 0,
                    'Ramanujan' => 0,
                    'D' => 0,
                ];
                foreach ($classGroup as $student) {
                    $sec = trim($student->section_name ?? '');
                    if (strcasecmp($sec, 'Aryabhatta') === 0) {
                        $counts['Aryabhatta']++;
                    } elseif (strcasecmp($sec, 'Kautilya') === 0) {
                        $counts['Kautilya']++;
                    } elseif (strcasecmp($sec, 'Ramanujan') === 0) {
                        $counts['Ramanujan']++;
                    } else {
                        $counts['D']++;
                    }
                }
                return collect($counts);
            });

        return view('backend.AcademicsModules.sectionAssign', compact('classes',  'classSectionCounts'));
    }

    public function fetch_section_student_data(Request $request)
    {
        $className = $request->class_name;
        if (strcasecmp($className, 'kg1') === 0) $className = 'KG1';
        if (strcasecmp($className, 'kg2') === 0) $className = 'KG2';

        $students = Student_registration::with('section')
            ->where(function ($query) use ($className) {
                $query->where('class_name', $className);
                if (in_array(strtoupper($className), ['KG1', 'KG2'])) {
                    $query->orWhere('class_name', ucfirst(strtolower($className)));
                }
            })
            ->where(function ($query) {
                $query->whereNull('section_name')
                    ->orWhere('section_name', '')
                    ->orWhere('section_name', 'D');
            })
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 't');
            })
            ->get();

        $allStudents = Student_registration::where(function ($query) use ($className) {
                $query->where('class_name', $className);
                if (in_array(strtoupper($className), ['KG1', 'KG2'])) {
                    $query->orWhere('class_name', ucfirst(strtolower($className)));
                }
            })
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 't');
            })
            ->get();

        $sectionCounts = [
            'Aryabhatta' => 0,
            'Kautilya' => 0,
            'Ramanujan' => 0,
            'D' => 0,
        ];

        foreach ($allStudents as $st) {
            $sec = trim($st->section_name ?? '');
            if (isset($sectionCounts[$sec])) {
                $sectionCounts[$sec]++;
            } else {
                $sectionCounts['D']++;
            }
        }

        return ['studentds' => $students, 'sectionCounts' => $sectionCounts];
    }

    public function updateStudentSectio(Request $request)
    {
        if ($request->has('bulk_assign')){
            foreach($request->assignments as $assign){
                $studentClass = $assign['studentclass'];
                if (strcasecmp($studentClass, 'kg1') === 0) $studentClass = 'KG1';
                if (strcasecmp($studentClass, 'kg2') === 0) $studentClass = 'KG2';

                $class = Classes::where('class_name', $studentClass)->where('section_name', $assign['section'])->where('is_delete', 0)->first();
                if(empty($class)){
                    $class = Classes::where('class_name', $studentClass)->where('section_name', $assign['section'])->first();
                }
                if(empty($class)){
                    return response()->json(['message' => 'Class section not found.'], 404);
                }
                $student = Student_registration::where(function($q) use ($studentClass) {
                    $q->where('class_name', $studentClass);
                    if (in_array(strtoupper($studentClass), ['KG1', 'KG2'])) {
                        $q->orWhere('class_name', ucfirst(strtolower($studentClass)));
                    }
                })->where('id',  $assign['student_id'])->first();

                if(empty($student)){
                    return response()->json(['message' => 'Student not found.'], 404);
                }
                $data = json_decode($student->json_str, true);
                if (!is_array($data)) {
                    $data = [];
                }
                $data['section_name'] = $assign['section'];
                $data['class_id'] = $class->id;
                $data['classname'] = $studentClass;
                $student->json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
                $student->class_id = $class->id;
                $student->section_name = $assign['section'];
                $student->class_name = $studentClass;
                $student->save();
            }
            return response()->json(['message' => 'Section assigned successfully']);
        }else{
            $studentClass = $request->studentclass;
            if (strcasecmp($studentClass, 'kg1') === 0) $studentClass = 'KG1';
            if (strcasecmp($studentClass, 'kg2') === 0) $studentClass = 'KG2';

            $class = Classes::where('class_name', $studentClass)->where('section_name', $request->section)->where('is_delete', 0)->first();
            if(empty($class)){
                $class = Classes::where('class_name', $studentClass)->where('section_name', $request->section)->first();
            }
            if(empty($class)){
                return response()->json(['message' => 'Class section not found.'], 404);
            }
            $student = Student_registration::where(function($q) use ($studentClass) {
                $q->where('class_name', $studentClass);
                if (in_array(strtoupper($studentClass), ['KG1', 'KG2'])) {
                    $q->orWhere('class_name', ucfirst(strtolower($studentClass)));
                }
            })->where('id',  $request->student_id)->first();

            if(empty($student)){
                return response()->json(['message' => 'Student not found.'], 404);
            }
            $data = json_decode($student->json_str, true);
            if (!is_array($data)) {
                $data = [];
            }
            $data['section_name'] = $request->section;
            $data['class_id'] = $class->id;
            $data['classname'] = $studentClass;
            $student->json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
            $student->class_id = $class->id;
            $student->section_name = $request->section;
            $student->class_name = $studentClass;
            $student->save();
            return response()->json(['message' => 'Section assigned successfully']);
        }
    }
    public function viewAssignSection($id, Request $request)
    {
        $sectionName = $request->query('section');
        $preOrder = ['Nursery', 'KG1', 'KG2', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $classes = Classname::where('is_delete', 0)->get()
            ->sort(function ($a, $b) use ($preOrder) {
                $posA = array_search($a->class_name, $preOrder);
                $posB = array_search($b->class_name, $preOrder);
                if ($posA !== false && $posB !== false) return $posA <=> $posB;
                if ($posA !== false) return -1;
                if ($posB !== false) return 1;
                return strnatcasecmp($a->class_name, $b->class_name);
            })
            ->values();

        $studentQuery = Student_registration::with('section')
            ->where(function ($query) use ($id) {
                $query->where('class_name', $id);
                if (in_array(strtoupper($id), ['KG1', 'KG2'])) {
                    $query->orWhere('class_name', ucfirst(strtolower($id)));
                }
            })
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 't');
            });

        if ($sectionName === 'D') {
            $studentQuery->where(function ($q) {
                $q->where('section_name', 'D')
                  ->orWhereNull('section_name')
                  ->orWhere('section_name', '');
            });
        } else {
            $studentQuery->where('section_name', $sectionName);
        }

        $students = $studentQuery->get();
        return view('backend.AcademicsModules.sectionAssign', compact('classes', 'students'));
    }
}
