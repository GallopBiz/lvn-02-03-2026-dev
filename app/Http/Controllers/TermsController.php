<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Terms;

class TermsController extends Controller
{
    public function index(){
        $Terms = Terms::where('is_delete',0)->get();
        return view('backend.Fees-module.Terms',compact('Terms'));
    }
    public function create(Request $request){
        Terms::create($request->post());
        return redirect()->route('terms')->with('success',' has been created successfully.');
    }
    public function view($id){
        $Edit_Terms = Terms::whereId($id)->get();
        $Terms = Terms::orderBy('id','desc')->paginate(5);
        return view('backend.Fees-module.Terms',compact('Edit_Terms','Terms'));
    }
    public function store(Request $request){
        $data = [
            'terms' => $request->terms,
        ];
        Terms::whereId($request->id)->update($data);
        return redirect()->route('terms')->with('success','Nature Of Work has been Updated successfully.');
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
