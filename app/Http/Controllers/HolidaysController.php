<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\Holidays;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HolidaysController extends Controller
{
    

    public function index(){
        $stream = Holidays::all();
        return view('backend.HRMS.holidays', compact('stream'));
    }

    
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'HolidayName' => 'required|string|max:255',
            'HolidayStartDate' => 'required|date',
            'HolidayEndDate' => 'required|date'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        Holidays::create($request->post());
        return redirect()->route('holidays')->with('success','  holidays has been created successfully.');
    }

    public function view($id){
        // $stream_master = Holidays::whereId($id)->get();
        $stream_master = Holidays::where('HolidayID',$id)->get();
        // $stream = Holidays::orderBy('HolidayID','desc')->paginate(5);
        $stream = Holidays::all();
        return view('backend.HRMS.holidays', compact('stream_master','stream'));
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'HolidayName' => 'required|string|max:255',
            'HolidayStartDate' => 'required|date',
            'HolidayEndDate' => 'required|date'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $data = [
            'HolidayName' => $request->HolidayName,
            'HolidayStartDate' => $request->HolidayStartDate,
            'HolidayEndDate' => $request->HolidayEndDate,
            'HolidayDescription' => $request->HolidayDescription
        ];
        // Attendance::whereId($request->id)->update($data);
        Holidays::where('HolidayID',$request->id)->update($data);
        return redirect()->route('holidays')->with('success','holidays has been Updated successfully.');
    }

    public function holidays_delete($id)
    {
        // echo $id;
        // exit;Position
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        // $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        $delete_resp = CommanModel::soft_delete($c,['HolidayID'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
        $stream = Holidays::where('HolidayID',$id);
        $stream->delete();
        return redirect()->route('holidays')->with('success','Deleted successfully.');
    }

}
