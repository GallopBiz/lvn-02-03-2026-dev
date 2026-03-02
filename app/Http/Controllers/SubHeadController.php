<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\CommanModel;
use App\Models\Subhead;
use App\Models\Head;

class SubHeadController extends Controller
{
    public function index(){

        $stream = Subhead::where('is_delete',0)->get();

        $classlist = DB::table('classes')->select('class_name')->distinct()->get();

        $headlist = Head::select('head_name')->distinct()->get();
        return view('backend.AcademicsModules.subhead',compact('stream','classlist','headlist'));
    }

    // public function create(Request $request){
    //     $data = [
    //         'class_group' => $request->class_group,
    //         'head_name' => $request->head_name,
    //         'sub_head_name' => $request->sub_head_name,
    //         'display_order' => $request->display_order,
    //         'visibility' => $request->visibility,
    //         'e1' => $request->e1,
    //         'e2' => $request->e2,
    //         'e3' => $request->e3,
    //         'e4' => $request->e4,
    //     ];
    //     DB::table("subhead")->insert($data);
    //     return redirect()->route('subheadmaster')->with('success','Sub Head Create Success fully...');
    // }
    public function create(Request $request){
        
        $data = $request->all();
    

        $data['visibility'] = $request->has('visibility') ? 'on' : 'off';
    
        Subhead::create($data);
    
        
        return redirect()->route('subheadmaster')->with('success', 'subheadmaster has been created successfully.');
    }

    public function view($id){
        $stream_master = Subhead::whereId($id)->get();
        $stream = Subhead::orderBy('id','desc')->paginate(5);
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $headlist = Head::select('head_name')->distinct()->get();
        return view('backend.AcademicsModules.subhead', compact('stream_master','stream','classlist','headlist'));
    }

    public function store(Request $request){
        $data = [
            'class_name' => $request->class_name,
            'head_name' => $request->head_name,
            'sub_head_name' => $request->sub_head_name,
            'display_order' => $request->display_order,
            'visibility' => $request->has('visibility') ? 'on' : 'off',
            'entry_type_e1' => $request->entry_type_e1,
            'entry_type_e2' => $request->entry_type_e2,
            'entry_type_e3' => $request->entry_type_e3,
            'entry_type_e4' => $request->entry_type_e4,
        ];
    
        // Assuming that the ID of the record to update is provided in the request
        Subhead::whereId($request->id)->update($data);
    
        return redirect()->route('subheadmaster')->with('success','subheadmaster has been Updated successfully.');
    }

    public function subheadmaster_delete($id)
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
        $stream = Subhead::findOrFail($id);
        $stream->delete();
        return redirect()->route('subheadmaster')->with('success','Deleted successfully.');
    }


}
