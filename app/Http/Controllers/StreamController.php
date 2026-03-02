<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Stream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    // public function index(){
    //     return view('backend.AcademicsModules.stream');
    // }

    public function index(){
        $stream = Stream::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.stream', compact('stream'));
    }

    
    public function create(Request $request){
        Stream::create($request->post());
        return redirect()->route('streammaster')->with('success','  Stream has been created successfully.');
    }

    public function view($id){
        $stream_master = Stream::whereId($id)->get();
        $stream = Stream::orderBy('id','desc')->paginate(5);
        return view('backend.AcademicsModules.stream', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $data = [
            'streams' => $request->streams,
            'remark' => $request->remark,
        ];
        Stream::whereId($request->id)->update($data);
        return redirect()->route('streammaster')->with('success','Stream has been Updated successfully.');
    }

    
    public function stream_master_delete($id)
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
        $stream = Stream::findOrFail($id);
        $stream->delete();
        return redirect()->route('streammaster')->with('success','Deleted successfully.');
    }

}
