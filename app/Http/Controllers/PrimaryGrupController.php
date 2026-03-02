<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Primarygroup;
use DB;
class PrimaryGrupController extends Controller
{
    public function index(){

        $stream = Primarygroup::where('is_delete',0)->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        return view('backend.AcademicsModules.primarygroup',compact('stream','classlist'));
    }

    public function create(Request $request){


        $selectedOptions = [];
        $class_name = $request->class_name; // Get the selected class
    
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
    

        $applicableTo = implode(',', $selectedOptions);

        $data = $request->post();
    
        
        $data['visibility'] = $request->has('visibility') ? 'Yes' : 'No';
    
        Primarygroup::create($data);
    
        return redirect()->route('primarygroup')->with('success', 'Primarygroup has been created successfully.');
    }
    

    public function view($id){
        $stream_master = Primarygroup::whereId($id)->get();
        // $stream = Primarygroup::orderBy('id', 'desc')->paginate(5);
        $stream = Primarygroup::where('is_delete',0)->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        return view('backend.AcademicsModules.primarygroup', compact('stream_master','stream','classlist'));
    }

    public function store(Request $request){
        $data = [
            'class_group' => $request->class_group,
            'primary_group_name' => $request->primary_group_name,
            'display_order' => $request->display_order,
            'visibility' => $request->visibility,
        ];
        // print_r($request->all());die();
        Primarygroup::whereId($request->id)->update($data);
        return redirect()->route('primarygroup')->with('success','Primarygroup has been Updated successfully.');
    }

    
    public function primarygroup_master_delete($id)
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
        $stream = Primarygroup::findOrFail($id);
        $stream->delete();
        return redirect()->route('primarygroup')->with('success','Deleted successfully.');
    }




}

