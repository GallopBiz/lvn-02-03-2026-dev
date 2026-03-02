<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use App\Models\CommanModel;
use App\Models\Inquiry;
use App\Models\Call;     

use DataTables;

class InquiryController extends Controller
{

	public function index(Request $request)
    {
		
        if ($request->ajax()) {
  
            $data = Inquiry::latest()->get();
  
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
   
                        //    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editProduct">Edit</a>';
                           $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Call" class="Call btn btn-primary btn-sm callStudent">Call</a>';
                           $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->mobile_number.'" data-original-title="View" class="btn btn-success btn-sm ViewStudent">View</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('backend.inquiry');
    }

	public function view(Request $request)
    {
        $mobile_number = $request->post('mobile_number');

        if ($request->ajax()) {
  
            $data = Call::select('call_note')->where('mobile_number',$mobile_number)->latest()->get();
  
            return $data;
            
        }
        
        return view('backend.inquiry');
    }

	public function store(Request $request)
    {
        Call::updateOrCreate([
                    'id' => $request->product_id
                ],
                [
                    'mobile_number' => $request->mobile_number,
                    'call_tag' => $request->call_tag, 
                    'call_note' => $request->call_note
                ]);        
     
        return response()->json(['success'=>'Call saved successfully.']);
    }

	public function edit($id)
    {
        $product = Inquiry::find($id);
        return response()->json($product);
    }

	/*List All Inquiry*/
    public function show()
    {	
		$all_inquiry = CommanModel::fetchDataArr('inquiry_registration');
        return view('backend.inquiry.index',compact('all_inquiry'));
    }

	/*Edit Inquiry*/
    public function inquiry_edit($id)
    {	
		$inquiry_data = CommanModel::getRowWhere('inquiry_registration',['id'=>$id]);
        return view('backend.inquiry.edit',compact('inquiry_data'));
    }

	/*update Inquiry*/
    public function update_inquiry(Request $request)
    {	
    	$update_id = $request->update_id;

		$dataForUpdate = [
			'application_for'=>$request->application_for,
			'date_of_birth'=>$request->dob,
			'class_name'=>$request->class_name,
			'student_name'=>$request->student_name,
			'session_name'=>$request->session_name,
			'phone_number'=>$request->phone_number,
			'mobile_number'=>$request->mobile_number,
		];

		$inquiry_data = CommanModel::updateData('inquiry_registration',['id'=>$update_id],$dataForUpdate);

		return redirect('inquiry-data-show');
    }

    /*Add inquiry*/
    public function add_inquiry(){
        return view('backend.inquiry.add');
    }

    

}