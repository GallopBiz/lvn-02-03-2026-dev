<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use App\Models\CommanModel;
use DataTables;
use App\Http\Requests\StoreFileRequest;
use PDF;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class FeesTypesMasterController extends Controller
{
    public function __construct(Request $request)
    {
        if (isset($_COOKIE['selectedYear'])) {
            $selectedYear = $_COOKIE['selectedYear'];
            // Now you can use $selectedYear in your PHP code
            $selectedYear;
        }
        
        if (!empty($request->session)){
            $db_name = $request->session; 
        } else if (!empty($request->session_name)){
            $db_name = $request->session_name;
        } else {
            if (isset($_COOKIE['selectedYear'])) {
                $db_name = $_COOKIE['selectedYear'];
            } else {
                $db_name = "2023_2024";
            }
            
        }
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    // print_r($db_name);die();
    }

    public function index(Request $request)
    {
        $fees_types_masterArr = DB::connection('dynamic')->table('fees_types_master')->where('is_delete', 0)->get();
        return view('backend.FeesTypeMaster.index',compact('fees_types_masterArr'));
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

    public function create(Request $request)
    {

        $insertArr = $request->all();
        unset($insertArr['_token']);
        // check exist

        $request->validate([
            'fees_type' => 'required',
            'session' => 'required',
        ]);

        $isExist =  DB::connection('dynamic')->table('fees_types_master')->select('id')->where('fees_type', $request->fees_type)->exists();    
        
        // if(!$isExist){DB::connection('dynamic')
        DB::connection('dynamic')->table('fees_types_master')->insert($insertArr);
            // CommanModel::insertData('fees_types_master',$insertArr);

        // }else{

        //     CommanModel::updateData('fees_types_master',['id'=>$isExist->id],$insertArr);
        // }

        return redirect()->back()->with('success', 'Fees type master created successfully');  
    }

    public function edit($id)
    {
        $fees_types_masterArr =  DB::connection('dynamic')->table('fees_types_master')->where('id', $id)->first();
        //CommanModel::getRowWhere('fees_types_master',['id'=>$id]);
        return view('backend.FeesTypeMaster.edit',compact('fees_types_masterArr'));
    }

    public function delete(Request $request)
    {
        print_r($request->all());die();
        $table = $request->table_name;
        $delete_resp = DB::connection('dynamic')->table($table)->where('id', $request->delete_id);//CommanModel::soft_delete($table,['id'=>$request->delete_id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function update(Request $request)
    {   
        $update_id = $request->update_id;

        $updateArr = $request->all();
        unset($updateArr['_token']);
        unset($updateArr['update_id']);

        $inquiry_data = DB::connection('dynamic')->table('fees_types_master')->where('id', $update_id)->update($updateArr);
        //CommanModel::updateData('fees_types_master',['id'=>$update_id],$updateArr);

        return redirect('fees-types-master')->with('success', 'Fees type master updated successfully');
    }

    public function student_ledger(){

        // $challan_data = CommanModel::fetchDataArr('feesreceiptchallan');

        $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id','scholar_no')->where('status','=','r')->get();

        // print_r($challan_data);
        // die();


        return view('backend.FeesTypeMaster.ledgershow',compact('data_student_name'));
    }
    public function cancle_student_ledger(){

        // $challan_data = CommanModel::fetchDataArr('feesreceiptchallan');

        // $data_student_name = DB::connection('dynamic')->table('inquiry_registration')->select('student_name','id')->get();

        $response = DB::table('inquiry_registration')
        ->select('inquiry_registration.*', 'student_registration.scholar_no')
        ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number')
        ->get();
    
        $data_student_name = $response;

        // print_r($challan_data);
        // die();


        return view('backend.FeesTypeMaster.canclestudentledgershow',compact('data_student_name'));
    }

	public function search_student_ledger(Request $request){


        $student_id = $request->student_id; 

        $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no',$request->scholar_number)->get();
        
        $feesreceiptchallan_student_data = DB::connection('dynamic')->table('feesreceiptchallan')->select('str_json')->where('student_id',$student_id)->get();

        if(!empty($feesreceiptchallan_student_data[0])){
            $feesreceiptchallan_student_data_json = json_decode($feesreceiptchallan_student_data[0]->str_json);
            // $name_formno = $feesreceiptchallan_student_data_json->name_formno;
        }

        $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_id)->get();

        $student_data = DB::connection('dynamic')->table('student_registration')->where('id', $student_id)->first();

        //$student_data_opening = DB::connection('dynamic')->table('opning_balance')->where('scholar_no', $student_data->scholar_no)->first();
        $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id','scholar_no')->get();
		
		// Fetching the course fees structure based on the class of the student
		$course_fees_data = DB::connection('dynamic')->table('course_fees_structure_master')
        ->where('class_name', $student_data->class_name) // Assuming the class name is in the student_registration table
        ->where('is_delete', 0) // Assuming you want to avoid deleted records
        ->get();
        
        
        $course_fees_data = DB::connection('dynamic')->table('generate_duechartstatus')
        ->where('student_id', $student_id)->get();

		// Now, to handle the JSON string from the course_fees_structure_master table, we can decode it.
		$decoded_course_fees_data = [];
		foreach ($course_fees_data as $course_data) {
        $json_data = json_decode($course_data->json_str, true); // Decoding JSON string
        $json_data_main = json_decode($json_data[0]['json_str'], true); // Decoding JSON string
        $decoded_course_fees_data[] = $json_data_main;
		}

        $mergedRowsNew = $this->mergeLedger($decoded_course_fees_data,$challan_data,$student_data,$totalnextyear);
        $total_tuition_fees = $mergedRowsNew['total_tuition_fees'];
        $mergedRows = $mergedRowsNew['mergedRows'];
        return view('backend.FeesTypeMaster.ledgershow',compact('mergedRows','data_student_name','challan_data','student_data', 'student_id','totalnextyear', 'decoded_course_fees_data','total_tuition_fees' ));
    }

    private function mergeLedger($decoded_course_fees_data,$challan_data,$student_data,$totalnextyear) {
        $groupedFees = [];
        $oneTimeFeeHeads = ['ADMISSION FEES','CUATION MONEY', 'ALUMNI FEES'];
        $oneTimeTotal = 0;
        $oneTimeRemarks = [];

        // get discount
        // Step 1: Get exemption data
        // hack is used to remove exempt logic, as now we are using 
        // generateduechart so we can remove exempt code from here 
        $exemption = DB::connection('dynamic')->table('student_fees_exempted')
        ->where('scholar_no', '0000')
        ->where('head_name', 'TUITION FEES')
        ->first();

        $exemptPercent = $exemption && $exemption->exempt_per ? floatval($exemption->exempt_per) : 0;

        // Step 2: Prepare discount description if exemption is found
        $discountDescriptions = [];
        if ($exemption) {
        $discountMap = [
            'lum'     => 'Lump Sum',
            'lunch'   => 'Lunch Fee',
            'lssm'    => 'LSSM',
            'sibling' => 'Sibling',
            'staff'   => 'Staff',
        ];

        foreach ($discountMap as $key => $label) {
            if (!empty($exemption->$key)) {
                $discountDescriptions[] = $label;
            }
        }
        }

        $discountNote = $discountDescriptions ? 'Discount applied: ('.$exemptPercent. ')' . implode(', ', $discountDescriptions) : null;


        if (isset($decoded_course_fees_data) && !empty($decoded_course_fees_data)) {
            foreach ($decoded_course_fees_data as $course_data) {
                foreach ($course_data['fees'] as $index => $fee) {
                    $head = strtoupper($course_data['account_name'][$index]);
                    $term = $course_data['term'][$index];
                    $date = $course_data['fees_date'][$index];
                    $due = $course_data['due_date'][$index];
                    $date_key = $date."__rest";
                    if($head=="BUS FEES"){
                        $date_key = $date."__bus";
                    }   
                    
                    
                    $originalFee = floatval($fee);
                    $discountAmount = 0;
                    $finalFee = $originalFee;
        
                    // Apply exemption for TUITION FEES
                    if ($head === 'TUITION FEES' && isset($exemptPercent) && $exemptPercent > 0) {
                        $discountAmount = $originalFee * ($exemptPercent / 100);
                        $finalFee = $originalFee - $discountAmount;
                    }
        
                    // Prepare discount note if available
                    $remark = $head . ' (' . $term . ')';
                    if ($discountAmount > 0 && !empty($discountNote)) {
                        $remark .= ' — ' . $discountNote;
                    }
        
                    if (in_array($head, $oneTimeFeeHeads)) {
                        $oneTimeTotal += $originalFee;
                        $oneTimeRemarks[] = $remark;
                    } else {
                        if (!isset($groupedFees[$date_key])) {
                            $groupedFees[$date_key] = [
                                'total_fee' => 0,              // Original fee
                                'discount_fee' => 0,           // Final after discount
                                'total_fee_discount' => 0,     // Total discount applied
                                'details' => [],
                                'due_date' => $due,
                            ];
                        }
        
                        $groupedFees[$date_key]['total_fee'] += $originalFee;
                        $groupedFees[$date_key]['discount_fee'] += $finalFee;
                        $groupedFees[$date_key]['total_fee_discount'] += $discountAmount;
                        $groupedFees[$date_key]['details'][] = $remark;
                    }
                }
            }
        }
        

        $mergedRows = [];
        
        // Add one-time fee at top
        $json = json_decode($student_data->json_str, true);
        $batch = !empty($json['batch']) ? $json['batch'] : null;
       
        if ($oneTimeTotal > 0 && $batch=="2025_2026") {
            $mergedRows[] = [
                'type' => 'dr',
                'due_date' => '-',
                'entry_date' => '01-04-2025',
                'amount_dr' => $oneTimeTotal,
                'amount_cr' => '',
                'remarks' => implode(', ', array_unique($oneTimeRemarks)),
            ];
            
        }else{
            $mergedRows[] = [
                'type' => 'cr',
                'due_date' => '-',
                'entry_date' => '2025-04-01',
                'amount_dr' => '',
                'amount_cr' => '',
                'refundable_cr' => "3000",
                'remarks' => 'Opening Balance',
            ];
        }
        if ($totalnextyear->isNotEmpty()) {
            $mergedRows[] = [
                'type' => 'cr',
                'due_date' => '-',
                'entry_date' => \Carbon\Carbon::parse($totalnextyear->first()?->fees_date)->format('d-m-Y'),
                'amount_dr' => '',
                'amount_cr' => $totalnextyear->sum('fees')-3000,
                'refundable_cr' => "3000",
                'remarks' => 'Opening Balance',//$totalnextyear->pluck('account_name')->unique()->implode(', '),
            ];
        }
        
        

        // Add grouped fee (Dr)
        foreach ($groupedFees as $entry_date_key => $data) {
            $entry_date_arr = explode('__', $entry_date_key);
            $entry_date = $entry_date_arr[0];
            $mergedRows[] = [
                'type' => 'dr',
                'due_date' => $data['due_date'],
                'entry_date' => $entry_date,
                'amount_dr' => $data['total_fee'],
                'amount_dr_after_discount' => $data['discount_fee'],
                'amount_dr_discount' => $data['total_fee_discount'],
                'amount_cr' => '',
                'remarks' => "Installment :".implode(', ', $data['details']),
            ];
        }
        $total_tuition_fees = 0;
        // Add challan data (Cr)
        if (!empty($challan_data)) {
            foreach ($challan_data as $object) {
                $settle_date = "";
                // if($object->old_fee_rec_no!=""){
                //     $txn_no = explode("__",$object->old_fee_rec_no);
                //     if(isset($txn_no[1])){
                //         $razorpay_txn_no = $txn_no[1];
                //         $settle_date = DB::connection('dynamic')->table('student_online_fee_payments')
                //             ->where('payment_gateway_transaction_id', $razorpay_txn_no)
                //             ->orderBy('settlement_date', 'desc')  // or 'desc' for latest
                //             ->value('settlement_date');
                        
                //     }
                // }
                $data = json_decode($object->str_json, true);
                $head_names = explode(',', $data['head_name']);
                $head_rec_amounts = explode(',', $data['head_rec_ammount']);
                $term_str = explode(',', $data['term_str']);
                
                $payment_date = \Carbon\Carbon::parse($data['payment_date'])->format('d-m-Y');
                $due_upto = $object->due_upto;
                $remark = "";
                for ($i = 0; $i < count($head_names); $i++) {
                    $rec_amt = floatval($head_rec_amounts[$i] ?? 0);
                    if ($rec_amt <= 0) continue;
                    if($head_names[$i]=="TUITION FEES"){
                        $total_tuition_fees += $rec_amt;
                    }
                //    $remark .= " \n ".strtoupper(trim($head_names[$i])) . ' (' .$term_str[$i].' AMT:'. $rec_amt . ')';
                }
                $transactionId = $data['challan_id'] ?? null;
				$gatewayId = $transactionId;
                $settle_date = ""; 
				if (($data["from_employee"] ?? '') == "RAZORPAY" && $transactionId) {
					$payment = DB::table('student_online_fee_payments')
						->where('transaction_id', $transactionId)
						->first();

					if ($payment && $payment->payment_gateway_transaction_id) {
						$gatewayId = $payment->payment_gateway_transaction_id;
                        $settlementDate = $payment->settlement_date;
                        if ($settlementDate) {
                            $settle_date = Carbon::parse($settlementDate)->format('d-m-Y');
                        } 
					} else {
						$gatewayId = null;
					}
				}
                $remark .= "Voucher No. ".$object->id. " CHALLAN:".  $gatewayId." / MODE: ".$data["payment_by_select"]." / BY: ".$data["from_employee"];
                $mergedRows[] = [
                        'type' => 'cr',
                        'due_date' => $due_upto,
                        'entry_date' => ($settle_date!="") ? $settle_date : $payment_date,
                        'amount_dr' => '',
                        'amount_cr' => $data['sub_total_received'],
                        'remarks' => $remark,
                        'settle_date' => $settle_date,
                    ];
            }
        }
        
        // Sort merged array by entry_date (assume date in Y-m-d format)
        usort($mergedRows, function($a, $b) {
            $dateA = \DateTime::createFromFormat('d-m-Y', $a['entry_date']);
            $dateB = \DateTime::createFromFormat('d-m-Y', $b['entry_date']);
            return $dateA <=> $dateB;
        });
        $mergedRowsNew['total_tuition_fees'] = $total_tuition_fees;
        $mergedRowsNew['mergedRows'] = $mergedRows;
        return $mergedRowsNew;

    }
    // public function search_cancle_student_ledger(Request $request){
    //     // print_r($request->all());exit;

    //     $student_id = $request->student_id;

    //     $feesreceiptchallan_student_data = DB::connection('dynamic')->table('feesreceiptchallan')->select('str_json')->where('student_id',$student_id)->get();

    //     if(!empty($feesreceiptchallan_student_data[0])){
    //         $feesreceiptchallan_student_data_json = json_decode($feesreceiptchallan_student_data[0]->str_json);
    //         // $name_formno = $feesreceiptchallan_student_data_json->name_formno;
    //     }

    //     $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_id)->get();

    //     $student_data = DB::connection('dynamic')->table('student_registration')->where('id', $student_id)->first();

    //     $data_student_name = DB::connection('dynamic')->table('inquiry_registration')->select('student_name','id')->get();
    //     // print_r($challan_data);exit;

    //     return view('backend.FeesTypeMaster.canclestudentledgershow',compact('data_student_name','challan_data','student_data', 'student_id'));
    // }

    public function search_cancle_student_ledger(Request $request){
        $student_id = $request->student_id;
    
        $feesreceiptchallan_student_data = DB::connection('dynamic')->table('feesreceiptchallan')->select('str_json')->where('student_id',$student_id)->get();
    
        if(!empty($feesreceiptchallan_student_data[0])){
            $feesreceiptchallan_student_data_json = json_decode($feesreceiptchallan_student_data[0]->str_json);
        }
    
        $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_id)->get();
    
        $student_data = DB::connection('dynamic')->table('student_registration')->where('id', $student_id)->first();
    
        // $data_student_name = DB::connection('dynamic')->table('inquiry_registration')->select('student_name','id')->get();
        $response = DB::table('inquiry_registration')
        ->select('inquiry_registration.*', 'student_registration.scholar_no')
        ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number')
        ->get();
    
        $data_student_name = $response;
    
        // Check if "All Students" option is selected
        if ($request->search_student == 'all') {
            $student_id = null; // Set student_id to null to fetch data for all students
        }
    
        return view('backend.FeesTypeMaster.canclestudentledgershow', compact('data_student_name', 'challan_data', 'student_data', 'student_id'));
    }
    










    public function  student_ledger_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        // print_r($c);exit;
        $delete_resp = DB::connection('dynamic')->table($c)->where('id', $b);
        //CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect('cancle_student_ledger')->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect('cancle_student_ledger')->with('error', 'Record not removed');
        }
    }
    public function registerstaff(Request $request){
         echo"<pre>";
         print_r($request->all());
    }
    public function view_student_ledger($id)
    {
        $student_id = $id;
        $student_data = DB::connection('dynamic')->table('student_registration')->where('id', $student_id)->first();

        if (!$student_data) {
            return redirect()->route('student_ledger')->with('error', 'Student ledger record not found.');
        }

        $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no', $student_data->scholar_no)->get();
        $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_id)->get();
        $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name', 'id', 'scholar_no')->get();

        $course_fees_data = DB::connection('dynamic')->table('generate_duechartstatus')
            ->where('student_id', $student_id)
            ->get();

        $decoded_course_fees_data = [];
        foreach ($course_fees_data as $course_data) {
            $json_data = json_decode($course_data->json_str, true);
            if (!empty($json_data[0]['json_str'])) {
                $json_data_main = json_decode($json_data[0]['json_str'], true);
                if (is_array($json_data_main)) {
                    $decoded_course_fees_data[] = $json_data_main;
                }
            }
        }

        $mergedRowsNew = $this->mergeLedger($decoded_course_fees_data, $challan_data, $student_data, $totalnextyear);
        $total_tuition_fees = $mergedRowsNew['total_tuition_fees'];
        $mergedRows = $mergedRowsNew['mergedRows'];

        return view('backend.FeesTypeMaster.ledgershow', compact('mergedRows', 'data_student_name', 'challan_data', 'student_data', 'student_id', 'totalnextyear', 'decoded_course_fees_data', 'total_tuition_fees'));
    }


    public function export_stu_Pdf($id){

        $student_id = $id;

        $feesreceiptchallan_student_data = DB::table('feesreceiptchallan')->select('str_json')->where('student_id',$student_id)->get();

        if(!empty($feesreceiptchallan_student_data[0])){
            $feesreceiptchallan_student_data_json = json_decode($feesreceiptchallan_student_data[0]->str_json);
            // $name_formno = $feesreceiptchallan_student_data_json->name_formno;
        }

        $challan_data = DB::table('feesreceiptchallan')->where('student_id', $student_id)->get();

        $student_data = DB::table('student_registration')->where('id', $student_id)->first();

        $data_student_name = DB::table('inquiry_registration')->select('student_name','id')->get();        
                           
        $pdf = PDF::loadView('backend.FeesTypeMaster.ledgershow_pdf', compact('data_student_name','challan_data','student_data'));
        return $pdf->download('Student-ledger.pdf');
    }
    
    public function defaulter_list(){


        //$student_id = $request->student_id; 
        $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', 01)->get();
        if(!empty($student_data)){ 
            foreach($student_data as $student_data1){ 
        $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no',$student_data1->scholar_no)->get();
        
         $feesreceiptchallan_student_data = DB::connection('dynamic')->table('feesreceiptchallan')->select('str_json')->where('student_id',$student_data1->id)->get();

        if(!empty($feesreceiptchallan_student_data[0])){
            $feesreceiptchallan_student_data_json = json_decode($feesreceiptchallan_student_data[0]->str_json);
            // $name_formno = $feesreceiptchallan_student_data_json->name_formno;
        }

        $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_data1->id)->get();

        $student_data = DB::connection('dynamic')->table('student_registration')->where('id', $student_data1->id)->first();

        //$student_data_opening = DB::connection('dynamic')->table('opning_balance')->where('scholar_no', $student_data->scholar_no)->first();
        $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id')->get();
        }}
        //return view('backend.FeesTypeMaster.ledgershow',compact('data_student_name','challan_data','student_data', 'student_id','totalnextyear'));
         return view('backend.FeesTypeMaster.defaulter');
    }


}
