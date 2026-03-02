<?php

namespace App\Http\Controllers;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\rto_paper;
use DataTables;
use App\Models\CommanModel;

class RTOpaper extends Controller
{
    public function index(){
        return view('backend.Transport.RTOpapers');
    }
    public function savertopaper(Request $request){
        echo"<pre>";
        // echo 1;
        print_r($request->all());

        $file = $request->file('image');
        // print_r($file);
        // echo $file;
        $filename = time().'_'.$request->file('image')->getClientOriginalName();
        $data = $request->all();
        $data['image'] = $filename;  
        rto_paper::create($data);
         // File upload location
         $location = 'uploads/admin/rto_paper';
         // Upload file
         $file->move($location,$filename);
         echo "done";
        // partymaster::create($request->post());
        // echo"done";
        return redirect()->route('list-rto-paper')->with('success',' has been created successfully.');
    }
    public function rto_paper_list(){
        $listrtopaper= rto_paper::where('is_delete',0)->get();
        return view('backend.Transport.Rtopaperlist',compact('listrtopaper'));

    }
    public function view($id){
        $listrtopaper = rto_paper::whereId($id)->get();
        $listrtopaper = rto_paper::orderBy('id','desc')->paginate(5);
        // echo"<pre>";
        // print_r($listrtopaper);
        // exit;
        return view('backend.Transport.RTOpapers',compact('listrtopaper'));
    }
    public function RTO_paper_filer(Request $request){
        // echo"<pre>";
        // print_r($request->all());
        $RTO_Paper_Name = $request->post('RTO_Paper_Name');
        $Vehicle_No = $request->post('Vehicle_No');
        // echo"<pre>";
        // print_r($RTO_Paper_Name);
        // print_r($Vehicle_No);
        // exit;
        // Total records
        // $records = Inquiry::select('*');
        ## Add custom filter conditions || !empty($class_name) || !empty($gender) || !empty($studentname)
        if(!empty($RTO_Paper_Name)){
            $records1 = DB::table('rto_paper')->where("RTO_paper_Name","=", $RTO_Paper_Name);
        }
        if(!empty($Vehicle_No)){
            $records1->orwhere("Vehicle","=", $Vehicle_No);
        }
        $all_inquiry = $records1->get();
        // print_r($records1);
        echo"<pre>";
        print_r($all_inquiry);
        exit;
        return view('backend.student_registrations.index',compact('all_inquiry'));
    }
    public function store(Request $request){
        $file = $request->file('image');
        // print_r($file);
        // echo $file;
        $filename = time().'_'.$request->file('image')->getClientOriginalName();
        // $data = $request->all();
        // print_r($filename);
        // $data['image'] = $filename; 
        // print_r($data);
        // rto_paper::create($data);
        
        $data = [
            'Renewal_Date' => $request->Renewal_Date,
            'Next_Renewal_Date' => $request->Next_Renewal_Date,
            'Registration_Date' => $request->Registration_Date,
            'Vehicle' => $request->Vehicle,
            'RTO_Paper_Name' => $request->RTO_Paper_Name,
            'Transfer_date' => $request->Transfer_date,
            'Document' => $request->Document,
            'Reminder_Frequency' => $request->Reminder_Frequency,
            'image' => $filename,
        ];
        // $updatedata = $data;
        // echo"<pre>";
        // print_r($data);
        // echo"hyy";
        // exit;
        rto_paper::whereId($request->id)->update($data);
        // File upload location
        $location = 'uploads/admin/rto_paper';
        // Upload file
        $file->move($location,$filename);
        // echo "done";
        return redirect()->route('list-rto-paper')->with('success','party master has been Updated successfully.');
        // return view('backend.Transport.parymaster');
        // return view('backend.Transport.Rtopaperlist')->with('success','Data has been Updated successfully.');
    }
    public function rto_paper_delete(Request $request)
    {
        // print_r($request->all());
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
        rto_paper::where('id',$id)->delete();
         return redirect()->back();
        }
}
