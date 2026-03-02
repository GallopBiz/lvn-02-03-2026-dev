<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Group;
use App\Models\Primarygroup;
use DB;

class GrupController extends Controller
{
    public function index(){

        $stream = Group::where('is_delete',0)->get();
        $primarylist = Primarygroup::select('primary_group_name')->distinct()->get(); 
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        return view('backend.AcademicsModules.group',compact('stream','primarylist','classlist'));
    }

    
    public function create(Request $request)
    {
        $data = $request->post();
        $data['health_group'] = $request->has('health_group') ? 'Yes' : 'No';
    
    
        $data['entry_type'] = $request->input('entry_type');
    
        Group::create($data);
    
        return redirect()->route('groupmaster')->with('success', 'Groupmaster has been created successfully.');
    }


    public function view($id){
        $stream_master = Group::whereId($id)->get();
        $stream = Group::orderBy('id', 'desc')->paginate(5);
        $primarylist = Primarygroup::select('primary_group_name')->distinct()->get(); 
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();    
        return view('backend.AcademicsModules.group', compact('stream_master','stream','primarylist','classlist'));
    }

    public function store(Request $request)
    {
        $data = [
            'class_name' => $request->class_name,
            'primary_group_name' => $request->primary_group_name,
            'group_name' => $request->group_name,
            'display_order' => $request->display_order,
            
        
            'health_group' => $request->has('health_group') ? 'Yes' : 'No',

            'entry_type' => $request->input('entry_type'),
        ];

        Group::whereId($request->id)->update($data);

        return redirect()->route('groupmaster')->with('success', 'Groupmaster has been updated successfully.');
    }

    
    public function groupmaster_delete($id)
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
        $stream = Group::findOrFail($id);
        $stream->delete();
        return redirect()->route('groupmaster')->with('success','Deleted successfully.');
    }


}
