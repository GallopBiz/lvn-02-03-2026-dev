<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Head;
use App\Models\Group;
use DB;

class HeadController extends Controller
{
    public function index(){


        $stream = Head::where('is_delete',0)->get();
        $grouplist = Group::select('group_name')->distinct()->get(); 
        $subheadlist = Head::select('head_name')->distinct()->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // DB::table('classes')->select('class_name')->distinct()->get();
        return view('backend.AcademicsModules.head',compact('stream','grouplist','subheadlist','classlist'));
    }

    public function create(Request $request)
    {
        $selectedOptions = [];
        $class_name = $request->class_name;
        
        
        // Get the selected class
    
        // Loop through the checkbox options and check if they are selected
        foreach (['E1', 'E2', 'E3', 'E4'] as $option) {
            // Check if the selected class allows this option
            if (
                ($option === 'E1' || $option === 'E2') &&
                in_array($class_name, ['Nursery', 'Kg1', 'Kg2'])
            ) {
                if ($request->has($option)) {
                    $selectedOptions[] = $option;
                }
            } elseif ($request->has($option)) {
                $selectedOptions[] = $option;
            }
        }

        $data['class_name'] = $request->input('class_name');
        
        $applicableTo = implode(',', $selectedOptions);
    
        $data = $request->post();
        $data['applicable_to'] = $applicableTo;
        $data['class_name'] = $request->input('class_name');
        Head::create($data);
    
        return redirect()->route('headmaster')
        ->with('success', 'Headmaster has been created successfully.')
        ->with('class_name', 'class_name');
        }
    

        public function view($id){
            $stream_master = Head::whereId($id)->get();
            // $stream = Head::orderBy('id', 'desc')->paginate(5);
            $stream = Head::where('is_delete',0)->get();
            // $primarylist = Primarygroup::select('primary_group_name')->distinct()->get(); 
            $classlist = DB::table('classes')->select('class_name')->distinct()->get();
            $grouplist = Group::select('group_name')->distinct()->get();
            $subheadlist = Head::select('head_name')->distinct()->get();
            // return $stream_master;
            return view('backend.AcademicsModules.head', compact('stream_master','stream','classlist','grouplist','subheadlist'));
        }

        

    // public function store(Request $request)
    // {
    //     $selectedOptions = [];
        

    //     foreach (['E1', 'E2', 'E3','E4'] as $option) {
    //         if ($request->has($option)) {
    //             $selectedOptions[] = $option;
    //         }
    //     }
        
        
    //     $applicableTo = implode(',', $selectedOptions);
        
    //     $isElective = $request->has('is_elective') ? 'Yes' : 'No';
    
    //     $data = [

    //         'class_name' => $request->class_name,
    //         'group_name' => $request->group_name,
    //         'head_name' => $request->head_name,
    //         'display_order' => $request->display_order,
    //         'applicable_to' => $applicableTo,
    //         'is_elective' => $isElective,
    //     ];
    
    //     if ($request->has('id')) {
    //         Head::whereId($request->id)->update($data);
    //     } else {
    //         Head::create($data);
    //     }
    
    //     return redirect()->route('headmaster')->with('success', 'Headmaster has been updated successfully.');
    // }
    
    public function store(Request $request)
    {
        $selectedOptions = [];
    
        foreach (['E1', 'E2', 'E3', 'E4'] as $option) {
            if ($request->has($option)) {
                $selectedOptions[] = $option;
            }
        }
    
        $applicableTo = implode(',', $selectedOptions);        
    
        $isElective = $request->has('is_elective') ? 'Yes' : 'No';
    
        $class = $request->class_name; // Get the selected class
    
        if (in_array($class, ['Nursery', 'Kg1', 'Kg2'])) {
        
            if ($request->has('E1')) {
                // Handle E1-specific processing here
            }
            if ($request->has('E2')) {
                // Handle E2-specific processing here
            }
            // You can add more specific processing as needed.
        } else {
            // Handle other classes (2-12) and E3 and E4 evaluations
            if ($request->has('E3')) {
                // Handle E3-specific processing here
            }
            if ($request->has('E4')) {
                // Handle E4-specific processing here
            }
        
        }
    
        
        
        $data = [
            'class_name' => $request->class_name,
            'group_name' => $request->group_name,
            'head_name' => $request->head_name,
            'display_order' => $request->display_order,
            'applicable_to' => $applicableTo,
            'is_elective' => $isElective,
        ];
    
        if ($request->has('id')) {
            Head::whereId($request->id)->update($data);
        } else {
            Head::create($data);
        }
    
        return redirect()->route('headmaster')->with('success', 'Headmaster has been created or updated successfully.');
    }
    
    
    
    
    

    public function headmaster_delete($id)
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
        $stream = Head::findOrFail($id);
        $stream->delete();
        return redirect()->route('headmaster')->with('success','Deleted successfully.');
    }


}
