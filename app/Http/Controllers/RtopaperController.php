<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rtopaper;
use App\Models\CommanModel;

class RtopaperController extends Controller
{
    public function index(){
        $rtopapers = Rtopaper::where('is_delete',0)->get();
        return view('backend.Transport.Rtopapersmaster', compact('rtopapers'));
    }

    public function create(Request $request){
        Rtopaper::create($request->post());
        return redirect()->route('rtopaper')->with('success',' has been created successfully.');
    }

    public function view($id){
        $rto_papers = Rtopaper::whereId($id)->get();
        $rtopapers = Rtopaper::orderBy('id','desc')->paginate(5);
        return view('backend.Transport.Rtopapersmaster',compact('rto_papers','rtopapers'));
    }

    public function store(Request $request){
        $data = [
            'rto_paper_name' => $request->rto_paper_name,
            'remark' => $request->remark,
            'is_permit' => $request->is_permit,
        ];
        Rtopaper::whereId($request->id)->update($data);
        return redirect()->route('rtopaper')->with('success','Nature Of Work has been Updated successfully.');
    }
    public function rtopaper_delete($id)
    {
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
    public function delete($id){
        $rtopapers = Rtopaper::findOrFail($id);
        $rtopapers->delete();
        return redirect()->route('rtopaper')->with('success','Bus Stop has been Deleted successfully.');
    }
}
