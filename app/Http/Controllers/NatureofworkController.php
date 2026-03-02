<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Natureofwork;
use App\Models\CommanModel;

class NatureofworkController extends Controller
{
    public function index(){
        $natureofworks = Natureofwork::where('is_delete',0)->get();
        return view('backend.Transport.Nature_of_work', compact('natureofworks'));
    }

    public function create(Request $request){
        Natureofwork::create($request->post());
        return redirect()->route('NatureOfWork')->with('success',' has been created successfully.');
    }

    public function view($id){
        $natureof_works = Natureofwork::whereId($id)->get();
        $natureofworks = Natureofwork::orderBy('id','desc')->paginate(5);
        return view('backend.Transport.Nature_of_work',compact('natureof_works','natureofworks'));
    }

    public function store(Request $request){
        $data = [
            'nature_of_work_name' => $request->nature_of_work_name,
            'nature_of_work_remarks' => $request->nature_of_work_remarks,
        ];
        Natureofwork::whereId($request->id)->update($data);
        return redirect()->route('NatureOfWork')->with('success','Nature Of Work has been Updated successfully.');
    }

    
    public function Natureofwork_delete(Request $request)
    {
        // print_r($request->all());
        // exit;
        $table = $request->table_name;
        $delete_resp = CommanModel::soft_delete($table,['id'=>$request->delete_id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
         // echo $id;
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
