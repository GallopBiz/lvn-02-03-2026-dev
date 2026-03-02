<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student_registration;
use App\Models\Generate_duechartstatus;
use App\Models\Feesreceiptchallan;
use App\Models\Late_fees_master;
use App\Models\AccountAnnualDefaulterStudent;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PublicController extends Controller
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
                $db_name = "lvnschool_test_lvn_2024";
            }
            
        }
        $db_name = "2025_2026";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

    }
    public function index()
    {
        return view('fee_structure.index');
    }

    private function getRemainingFees($scholarNo)
    {
        $studentFee = AccountAnnualDefaulterStudent::where('scholar_no', $scholarNo)->where('status', 'pending')->first();

        if (!$studentFee) {
            return 0; // Student not found
        }

        return max(0, $studentFee->total);
    }

    public function getStudentDetails(Request $request)
    {
        $scholarNumber = $request->input('scholarNumber');
        $student = Student_registration::where('scholar_no', $scholarNumber)->first();

        $remainingAmount = $this->getRemainingFees($scholarNumber);
        // return response()->json(['error' => $student], 404);
        if ($student) {
             $is_staff = json_decode($student->json_str , 1);
            $is_sibling = json_decode($student->json_str , 1);
           // print_r($is_staff); print_r($is_sibling); 
            
            if(empty($is_staff['is_staff_applied_for_admission'])){
                $is_staff['is_staff_applied_for_admission'] = 0;
            }
            if(empty($is_sibling['is_sibling_applied_for_admission']) && $is_sibling['is_sibling_applied_for_admission']==0){
                $is_sibling['is_sibling_applied_for_admission'] = 0;
            }
            // Find Generate_duechartstatus record by student_id
            $feesrecipt_chalan = DB::connection('dynamic')
            ->select(DB::raw('select * from `feesreceiptchallan` where `student_id` = ? order by `id` asc limit ? offset ?'), [$student->id, PHP_INT_MAX, 1]);


            $feesrecipt_chalan = collect($feesrecipt_chalan);
            //  $feesrecipt_chalan12 = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', 156)->get(); 
            // $feesrecipt_chalan1 = json_decode($feesrecipt_chalan2->str_json , 1);
            // var_dump($feesrecipt_chalan1); 

            //Feesreceiptchallan::select('str_json')->where('student_id', $student->id)->get();
            
            if (!$feesrecipt_chalan->isEmpty()){
               // $stucture = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id', $student->id)->first();
                $stucture = Generate_duechartstatus::where('student_id', $student->id)->first();
                $json_str_stuct = json_decode($stucture->json_str);
                $json_strstuct = json_decode($json_str_stuct[0]->json_str,1);

                foreach($feesrecipt_chalan as $val){
                    $json_decode_recipt[] = json_decode($val->str_json , 1);
                }

                $newArray = array(
                    'fees_date' => array(),
                    'account_name' => array(),
                    'fees' => array(),
                    'due_date' => array(),
                    'term' => array()
                );

                foreach ($json_strstuct['account_name'] as $index => $account_name) {
                    $match = false;
                
                    foreach ($json_decode_recipt as $arr) {
                        $head_name = explode(',', $arr['head_name']);
                        $head_rec_ammount = explode(',', $arr['head_rec_ammount']);
                        $term_str = explode(',', $arr['term_str']);
                        $discountlumsum = $arr['discount'];
                        
                        if (in_array($account_name, $head_name) && in_array($json_strstuct['fees'][$index], $head_rec_ammount) && in_array($json_strstuct['term'][$index], $term_str)) {
                            $match = true;
                            break;
                        }
                    }
                
                    if (!$match) {
                        $newArray['fees_date'][] = $json_strstuct['fees_date'][$index];
                        $newArray['account_name'][] = $account_name;
                        $newArray['fees'][] = $json_strstuct['fees'][$index];
                        $newArray['due_date'][] = $json_strstuct['due_date'][$index];
                        $newArray['term'][] = $json_strstuct['term'][$index];
                        //$newArray['discountlumsum'][] = $json_strstuct['discount'][$index];
                        
                    } 
                }
                $bustotal = 0;
                $busFees = 0;
                  //$discountlumsum = json_decode($json_decode_recipt->str_json , 1);
                foreach($json_decode_recipt as $k => $v){
                    if (!empty($v['TotalBusAmount'])){
                        $bustotal += $v['TotalBusAmount'];
                        $newArray['busfees'] = $bustotal;
                        $busFees = $bustotal;


                    } else {
                        $newArray['busfees'] = 0;
                    }
                   
                }
                // $discountlumsum1 = $discountlumsum['discount'];
                $newArray_json = json_encode($newArray);
                $json_data = [
                    'id' => null,
                    'class_name' => null,
                    'session_name' => null,
                    'fees_type_name' => null,
                    'cast_category' => null,
                    'batch' => null,
                    'json_str' => $newArray_json,
                ];
                $json_data_encode = json_encode([$json_data]);
                $newduchart = [
                    'id' => 0,
                    'student_id' => $student->id,
                    'class_name' => $student->class_name,
                    'sectionname' => $student->session_name,
                    'status' => 'g',
                    'amount' => 0,
                    'session_name' => $student->session_name,
                    'json_str' => $json_data_encode,
                    'is_rte' => $student->application_for,
                    'updated_date' => time(),
                ];
                $feesreciptdiscount = Feesreceiptchallan::where('student_id', $student->id)->first();
                $latefee = Late_fees_master::where('id', 1)->first(); 
                if($feesreciptdiscount){
                 $feesreciptdiscount1 = json_decode($feesreciptdiscount->str_json, 1);
                        $feedis=$feesreciptdiscount1['discount']['lumpsum_fees'];

                }else{
                    $feedis = 0;
                }
                return response()->json([
                    'duechart_data' => $newduchart,
                    'id' => $student->id,
                    'student_name' => ucwords($student->student_name),
                    'amount' => 0,
                    'bus_fees' => $busFees, // Include bus fees in the response
                    'is_staff' => $is_staff['is_staff_applied_for_admission'],
                    'is_sibling' => $is_sibling['is_sibling_applied_for_admission'],
                    'discountlumsum1' => $feedis,
                    'latefeeamount' => $latefee['upto'],
                    'remainingAmount' => $remainingAmount
                ]);
            }else{    
            $dueChartStatus = Generate_duechartstatus::where('student_id', $student->id)->first();
            //$latefee2 = DB::connection('dynamic')->('select * from late_fees_master')->get();
            $latefee = Late_fees_master::where('id', '1')->get(); 
            //alert($latefee);
           // print_r($latefee); 
            
    
            if ($dueChartStatus) {
                $feesreciptdiscount = Feesreceiptchallan::where('student_id', $student->id)->first();
                if($feesreciptdiscount){
                 $feesreciptdiscount1 = json_decode($feesreciptdiscount->str_json, 1);
                        $feedis=$feesreciptdiscount1['discount']['lumpsum_fees'];

                }else{
                    $feedis = 0;
                }
                $busFees = json_decode($dueChartStatus->json_str, true)['bus_fees'] ?? 18000;
                
                return response()->json([
                    'duechart_data' => $dueChartStatus,
                    'id' => $student->id,
                    'student_name' => ucwords($student->student_name),
                    'amount' => $dueChartStatus->amount,
                    'bus_fees' => $busFees, // Include bus fees in the response
                    'is_staff' => $is_staff['is_staff_applied_for_admission'],
                    'is_sibling' => $is_sibling['is_sibling_applied_for_admission'],
                    'discountlumsum1' => $feedis,
                    'remainingAmount' => $remainingAmount
                    //'latefeeamount' => $latefee->upto,

                ]);
            } else {
                return response()->json(['error' => 'Due chart status not found for the student'], 404);
            }
        }} else {
            return response()->json(['error' => 'Student not found'], 404);
        }

    }
    
	public function studentFeesDetails(){
		return view('fee_structure.index');
	}

	public function getFeeStructure(Request $request)
	{
		$scholar_id = $request->input('scholar_id');

		// Fetch student details based on scholar_id
		$student = DB::table('student_registration')->where('scholar_no', $scholar_id)->first();
        $remainingAmount = $this->getRemainingFees($scholar_id);

		if (!$student) {
			return back()->with('error', 'Student not found');
		}

		// Fetch the fee structure
		$fee_structure = DB::table('generate_duechartstatus')
			->where('student_id', $student->id)
			->get();

		// Decode the fee structure JSON data
		foreach ($fee_structure as $fee) {
			$fee->json_data = json_decode($fee->json_str, true);
			$fee->sub_json_data = json_decode($fee->json_data[0]["json_str"], true);
		}

        // student old fee receipt challan
        $feeReceipts = DB::table('feesreceiptchallan')
        ->where('student_id',  $student->id)
        ->select('str_json') // Select the 'json_str' column
        ->get();
        $record_found_feesreciept_challan = $feeReceipts->count();
        $feeReceiptDetails= array();
        //  [fee_head] => BUS FEES [term_number] => 1 [total_fee_first] => 18000.00 [discount_sum] => 0.00 [net_total_sum] => 0.00 [allocated_amount_sum] => 18000.00 [balance_amount_last] => 16200.00
        foreach ($feeReceipts as $receipt) {
            // Decode the JSON string into an associative array
            $jsonData = json_decode($receipt->str_json, true);
            $head_names = explode(",",$jsonData["head_name"]); 
            $head_due_amount = explode(",",$jsonData["head_due_amount"]); 
            $head_rec_amount = explode(",",$jsonData["head_rec_ammount"]); 
            $term_str = explode(",",$jsonData["term_str"]); 
            $head_to_date = explode(",",$jsonData["head_to_date"]); 
            $head_due_date = explode(",",$jsonData["head_due_date"]); 
            foreach($head_names as $key=> $head_name ){
                if(isset($feeReceiptDetails[$head_name."_".$term_str[$key]])){
                    // echo $head_name."_".$term_str[$key];
                    // echo "<br>";
                    // print_r($feeReceiptDetails);

                    // var_dump($feeReceiptDetails[$head_name."_".$term_str[$key]]["allocated_amount_sum"]);
                    // var_dump($head_rec_amount[$key]);
                    // print_r($head_rec_amount);
                    // var_dump($key);
                    // die;
                    $feeReceiptDetails[$head_name."_".$term_str[$key]]["allocated_amount_sum"] += (int)$head_rec_amount[$key];
                }else{
                    $feeReceiptDetails[$head_name."_".$term_str[$key]]["fee_head"]= $head_name;
                    $feeReceiptDetails[$head_name."_".$term_str[$key]]["term_number"]= intval($term_str[$key]);
                    $feeReceiptDetails[$head_name."_".$term_str[$key]]["allocated_amount_sum"]= (int)$head_rec_amount[$key];
                }
            }
        }
        // I have commented discount part as it is handled by generate 
        // due chart now if we need to modify anything lets do it 
        // separate or create ticket for it 
        $exempted_fees_details = null;
        // DB::table('student_fees_exempted')->where('scholar_no', $scholar_id)->first();
        $exempt_total =0;
        $lum_checked = 0;
        if(!is_null($exempted_fees_details)){
            $exempt_total = $exempted_fees_details->exempt_per;
        }
        if(isset($_REQUEST['lum']) && $_REQUEST['lum']==1){
            if(is_null($exempted_fees_details)){
                $exempted_fees_details = (object) [];
                $exempted_fees_details->head_name = "TUITION FEES";
                $exempt_total = $exempt_total+10;
            }else if($exempted_fees_details->lum!=1){
                
                $exempt_total = $exempt_total+10;
            }
            $lum_checked = 1;
        }
        // Student late fee check
        $currDate =  date('d-M-y');
        
		$fee_headwise = array();
		foreach($fee->sub_json_data['account_name'] as $key => $head_wise_data){
            $key_fee_headwise = $fee->sub_json_data['account_name'][$key]."_".intval($fee->sub_json_data['term'][$key]);
            $orig_fees = $fees_exempted = $fee->sub_json_data['fees'][$key];
            $dis_applied = false;
            $remark = "";
            
            if($exempt_total >0 && $exempted_fees_details && ($exempted_fees_details->head_name==$fee->sub_json_data['account_name'][$key])){
                $fees_exempted = $fee->sub_json_data['fees'][$key]-($fee->sub_json_data['fees'][$key]*($exempt_total/100));
                if (!(floor($fees_exempted) == $fees_exempted)) {
                    $fees_exempted = ceil($fees_exempted);
                }
                $dis_applied = true;
                if(isset($_REQUEST['lum']) && $_REQUEST['lum']==1){
                    $remark = "Discount Applied with Lumpsum Payment";
                }else{
                    $remark = "Discount Applied";
                } 
            }

            
            // $data = $this->calculateLateFee();
            $fee_headwise[$key_fee_headwise] = array(
                "fees_date" => $fee->sub_json_data['fees_date'][$key],
                "account_name" => $fee->sub_json_data['account_name'][$key],
                "fees" => $fees_exempted,
                "due_date" => $fee->sub_json_data['due_date'][$key],
                "term" => $fee->sub_json_data['term'][$key],
            );
            if($dis_applied){
                $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                $fee_headwise[$key_fee_headwise]["dis_applies_flag"] = 1;
                $fee_headwise[$key_fee_headwise]["remark"] = $remark;                
                $fee_headwise[$key_fee_headwise]["percentage"] = $exempt_total;                
            }
            
            // override fees if late fee is there
            if($feeReceipts->isEmpty()){
                $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                    $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                    $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                    $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                    $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                    $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                }
            }else{
                $fee_receipt_detail_key = $fee->sub_json_data['account_name'][$key]."_".$fee->sub_json_data['term'][$key];
                if(isset($feeReceiptDetails[$fee_receipt_detail_key])){
                    if($feeReceiptDetails[$fee_receipt_detail_key]["allocated_amount_sum"]<=0){
                        $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                        if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                            $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                            $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                            $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                            $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                            $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                        }
                    }
                }else{
                    $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                    if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                        $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                        $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                        $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                        $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                        $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                    }
                }   
            }
		}

		
        
        // Calculate remaining amounts (fees - allocated)
		$fee_final_data = $fee_headwise;
        $term_map = array(
            "1" => "1st",
            "2" => "2nd",
            "3" => "3rd",
            "4" => "4th"
        );
		foreach ($feeReceiptDetails as $fee) {
            if(isset($fee_headwise[$fee['fee_head']."_".$fee['term_number']])){
                $allocatedAmount = (float)$fee['allocated_amount_sum'];
                if($fee['fee_head']=="TUITION FEES"){
                    if(isset($feeReceiptDetails["LATE FEES_".$term_map[$fee['term_number']]])){
                        $allocatedAmount = $allocatedAmount+$feeReceiptDetails["LATE FEES"."_".$term_map[$fee['term_number']]]["allocated_amount_sum"];
                    }
                }
                $fee_generate_due_details = $fee_headwise[$fee['fee_head']."_".$fee['term_number']];
                $term_fees = $fee_generate_due_details["fees"];
                
                $fee['term_fee'] = $term_fees;
                $fee['remaining_amount'] = $term_fees - $allocatedAmount;
                $fee_final_data[$fee['fee_head']."_".$fee['term_number']]["allocatedAmount"] = $allocatedAmount;
                $fee_final_data[$fee['fee_head']."_".$fee['term_number']]["remaining_amount"] = $fee['remaining_amount'];
            }
        }
        
        return view('fee_structure.index', compact( 'fee_structure', 'student','fee_final_data','remainingAmount','record_found_feesreciept_challan','lum_checked'));
	}

    public function storeFeesBeforePayment(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'fees' => 'required|array', // Expected fees as array
        ]);
    
        $studentId = $validated['student_id'];
        $selectedFees = $validated['fees'];
        $transactionId = uniqid('txn_', true); // Generate unique transaction ID
        
		// add custmo before transaction id for better visibility
        if($request->has('custom_payment')){
            $transactionId = "CUSTOM_".$transactionId;
		}
        // Loop through selected fees and insert them into the database
        foreach ($selectedFees as $feeTerm => $feeData) {
				$selectedFeeTerm = "";
				if($request->has('custom_payment')){
					$selectedFeeTerm = $feeTerm;
				}else{
					$selectedFeeTerm = $feeData['fee_term'];
				}
            DB::table('student_online_fee_payments')->insert([
                'transaction_id' => $transactionId,
                'student_id' => $studentId,
                'fee_term' => $selectedFeeTerm,
                'fee_account' => $feeData['account_name'],
                'fees_due' => $feeData['fees'],
                'allocated_amount' => $feeData['remaining_amount'] ?? $feeData['fees'],
                'remaining_amount' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
                'late_fee' => $feeData['late_fee'],
                'discount' => $feeData['discount'],
                'lumsum' => $_POST["lumsum"]
            ]);
        }

        // Return success response
        return response()->json(['transaction_id' => $transactionId, 'message' => 'Fees stored successfully'], 200);
    }

    public function paymentSuccess(Request $request)
    {
        // Validate request data
        $data = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'transaction_id' => 'required|string',
        ]);
        // check if payment via custom-payment
        $head_wise_payment = true;
        if($request->has('custom_payment')){
            $head_wise_payment = false;
        }
        try {
            // Update payment status in the database
            DB::table('student_online_fee_payments')
                ->where('transaction_id', $data['transaction_id'])
                ->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                    'updated_at' => now(),
                    'payment_gateway_transaction_id'=> $data['razorpay_payment_id']
                ]);
                
            if($head_wise_payment){
                $this->storeFeeReceiptChallanDetails($data['razorpay_payment_id'], $data['transaction_id']);
            }
            $payment = DB::table('student_online_fee_payments')
                ->where('transaction_id', $transaction_id)
                ->first();

            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            $student_id = $payment->student_id ?? null;

            if ($student_id) {
                $this->updateOrInsertExemption($student_id, 'TUITION FEES');
            }
            return response()->json(['message' => 'Payment verified and updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment verification failed', 'error' => $e->getMessage(),  // This gives the error message
            'trace' => $e->getTraceAsString()], 500);
        }
    }
	
	/**
     * Browser / cron callable sync
     * URL: /sync-missing-fee-receipts?token=RUN_SYNC_2026
     */
    /**
     * Public sync endpoint
     * URL: /sync-missing-fee-receipts?token=RUN_SYNC_2026
     */
    public function paymentSuccessInternal(Request $request)
    {
        // 🔐 Basic protection
        if ($request->query('token') !== 'RUN_SYNC_2026') {
            abort(403, 'Forbidden');
        }

        $records = DB::select("
            SELECT DISTINCT
                sop.transaction_id,
                sop.payment_gateway_transaction_id,
                CONCAT(sop.transaction_id, '__', sop.payment_gateway_transaction_id) AS expected_old_fee_rec_no
            FROM student_online_fee_payments sop
            LEFT JOIN feesreceiptchallan frc
                ON frc.old_fee_rec_no COLLATE utf8mb4_unicode_ci =
                CONCAT(
                    CAST(sop.transaction_id AS CHAR),
                    '__',
                    CAST(sop.payment_gateway_transaction_id AS CHAR)
                ) COLLATE utf8mb4_unicode_ci
            WHERE sop.status = 'paid'
            AND frc.old_fee_rec_no IS NULL;
        ");

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($records as $row) {
            try {
                DB::beginTransaction();

                // 1️⃣ Update payment (idempotent)
                DB::table('student_online_fee_payments')
                    ->where('transaction_id', $row->transaction_id)
                    ->update([
                        'status' => 'paid',
                        'payment_date' => now(),
                        'updated_at' => now(),
                        'payment_gateway_transaction_id' => $row->payment_gateway_transaction_id
                    ]);

                // 2️⃣ Create challan
                $this->storeFeeReceiptChallanDetails(
                    $row->payment_gateway_transaction_id,
                    $row->transaction_id
                );

                // 3️⃣ Fetch payment
                $payment = DB::table('student_online_fee_payments')
                    ->where('transaction_id', $row->transaction_id)
                    ->first();

                if (!$payment) {
                    throw new \Exception('Payment not found');
                }

                // 4️⃣ Update exemption
                if (!empty($payment->student_id)) {
                    $this->updateOrInsertExemption(
                        $payment->student_id,
                        'TUITION FEES'
                    );
                }

                DB::commit();

                $successCount++;
                $results[] = "✅ Processed: {$row->transaction_id}";

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Public paymentSuccessInternal FIRST FAILURE', [
                    'transaction_id' => $row->transaction_id,
                    'error'          => $e->getMessage(),
                    'file'           => $e->getFile(),
                    'line'           => $e->getLine(),
                    'trace'          => collect($e->getTrace())->take(10)->toArray(), // first 10 frames
                ]);

                $failCount++;
                $results[] = "❌ Failed: {$row->transaction_id}";
            }
        }

        return response()->json([
            'status'  => 'completed',
            'success' => $successCount,
            'failed'  => $failCount,
            'total'   => count($records),
            'details' => $results,
        ]);
    }


    private function updateOrInsertExemption($student_id, $head_name)
    {
        $student_detail = DB::table('student_registration')
            ->where('id', $student_id)
            ->first();

        if (!$student_detail) {
            return false; // student not found
        }

        $scholar_no = $student_detail->scholar_no ?? null;

        if (!$scholar_no) {
            return false; // scholar_no missing
        }

        $existing = DB::table('student_fees_exempted')
            ->where('scholar_no', $scholar_no)
            ->where('head_name', $head_name)
            ->first();

        if ($existing) {
            // Update existing row
            DB::table('student_fees_exempted')
                ->where('student_id', $existing->student_id)
                ->update([
                    'lum' => 1,
                    'exempt_per' => $existing->exempt_per + 10,
                ]);
        } else {
            // Insert new row
            DB::table('student_fees_exempted')->insert([
                'student_id' => $student_id,
                'scholar_no' => $scholar_no,
                'head_name' => $head_name,
                'lum' => 1,
                'exempt_per' => 10,
            ]);
        }

        return true;
    }

    public function storeFeeReceiptChallanDetails($razorpay_payment_id, $transactionId)
    {
        $termDueDate = array(
            "1st" => array("to_date"=>"01-04-2025","due_upto"=>"05-05-2025"),
            "2nd" => array("to_date"=>"01-07-2025","due_upto"=>"05-08-2025"),
            "3rd" => array("to_date"=>"01-10-2025","due_upto"=>"05-11-2025"),
            "4th" => array("to_date"=>"01-01-2026","due_upto"=>"05-02-2026"),
        );
        // Fetch the payment details for the successful transaction
        $paymentData = DB::table('student_online_fee_payments')
        ->join('student_registration', 'student_online_fee_payments.student_id', '=', 'student_registration.id')
        ->where('student_online_fee_payments.transaction_id', $transactionId)
        ->where('student_online_fee_payments.status', 'paid') // Only fetch successful payments
        ->select(
            'student_online_fee_payments.*',
            'student_registration.student_name',
            DB::raw("JSON_EXTRACT(student_registration.json_str, '$.fathername') as fathername"),
            DB::raw("JSON_EXTRACT(student_registration.json_str, '$.fathername_prefix') as fathername_prefix"),
            DB::raw("JSON_EXTRACT(student_registration.json_str, '$.section_name') as section_name"),
            'student_registration.class_name',
            'student_registration.date_of_birth',
            'student_registration.created_at',
            'student_registration.form_number',
            'student_registration.scholar_no'
        )
        ->get();

        // Prepare data arrays
        $head_name = [];
        $head_due_amount = [];
        $head_rec_amount = [];
        $term_str = [];
        $head_to_date = [];
        $head_due_date = [];

        // Loop over payment data to fill arrays
        foreach ($paymentData as $payment) {
            $due_amount = $payment->fees_due;
            $rec_amount = $payment->allocated_amount;
            if($payment->late_fee>0){
                $due_amount = $due_amount - $payment->late_fee; 
                $rec_amount = $rec_amount - $payment->late_fee;
            }
            $head_name[] = $payment->fee_account;
            $head_due_amount[] = $due_amount;
            $head_rec_amount[] = $rec_amount;
            $term_str[] = $payment->fee_term;
            $head_to_date[] = $termDueDate[$payment->fee_term]["to_date"];
            $head_due_date[] = $termDueDate[$payment->fee_term]["due_upto"];
            
            if($payment->late_fee>0){
            $head_name[] = "LATE FEES";
            $head_due_amount[] = $payment->late_fee;
            $head_rec_amount[] = $payment->late_fee;
            $term_str[] = $payment->fee_term;
            $head_to_date[] = "";
            $head_due_date[] = "";
            }
        }

        // Calculate totals
        $total_dueamount = array_sum($head_due_amount);
        $sub_total_received = array_sum($head_rec_amount);
        $term_advance_balance = 0; // Customize this based on your logic
        $discount = 0; // Customize if applicable

        // Prepare the $json structure
        $student_id = $paymentData->first()->student_id;
        $challan_id = $transactionId; // $paymentData->first()->challan_id; // Customize based on your DB schema

        $json = [
            'student_id' => $student_id,
            'challan_id' => $challan_id,
            'head_name' => implode(',', $head_name),
            'head_due_amount' => implode(',', $head_due_amount),
            'head_rec_ammount' => implode(',', $head_rec_amount),
            'term_str' => implode(',', $term_str),
            'head_to_date' => implode(',', $head_to_date),
            'head_due_date' => implode(',', $head_due_date),
            'name_father' => ($paymentData->first()->fathername_prefix ?? '') . ' ' . ($paymentData->first()->fathername ?? ''),
            'name_classsection' => ($paymentData->first()->class_name ?? '') . ' ' . ($paymentData->first()->section_name ?? ''),
            'name_admdt' => $paymentData->first()->created_at ?? '',
            'name_formno' => $paymentData->first()->form_number ?? '',
            'name_scholarno' => $paymentData->first()->scholar_no ?? '',
            'feestype' => 0,
            'total_dueamount' => $total_dueamount,
            'sub_total_due' => $total_dueamount,
            'sub_total_received' => $sub_total_received,
            'grand_total_due' => $total_dueamount,
            'by_cash' => $sub_total_received,
            'payment_by_select' => "ONLINE", 
            'from_employee' => "RAZORPAY",
            'advance_received' => $term_advance_balance,
            'discount' => $discount,
            'payment_date' => $paymentData->first()->payment_date ?? now()->toDateString(),
        ];

        // Prepare the $data structure
        $data = [
            'student_id' => $student_id,
            'student_dob' => $paymentData->first()->date_of_birth,
            'recpt_chain' => null,
            'due_upto' => '05-02-2025', // Customize based on your logic
            'name_student' => $paymentData->first()->student_name,
            'str_json' => json_encode($json),
            'old_fee_rec_no' => $transactionId."__".$razorpay_payment_id, // Customize based on your schema
        ];
        $status = $this->insertChallanRecord($data);
        // Save or return $data for further processing
        // Example: return the $data to the view or pass it to another method
        if($status){
            return true;
        }
        return false;
    }


    public function insertChallanRecord(array $data)
    {
        // Get the column names (keys of the $data array)
        $columns = implode(", ", array_keys($data));

        // Create placeholders for the values (same number of ? as there are values)
        $placeholders = implode(", ", array_fill(0, count($data), '?'));

        // Build the raw SQL query
        $sql = "INSERT INTO feesreceiptchallan ($columns) VALUES ($placeholders)";

        // Get the values (values of the $data array)
        $values = array_values($data);

        // Execute the SQL insert using the DB facade
        try {
            DB::insert($sql, $values);
            return true; // Return true on successful insertion
        } catch (\Exception $e) {
            // Return false if there was an error
            return response()->json(['message' => 'Payment verification failed', 'error' => $e->getMessage(),  // This gives the error message
            'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function calculateLateFee($dueDate) {
        // Define late fee rates and limits
        $firstPhaseRate = 25;  // Rs. 25 per day for the first 21 days
        $secondPhaseRate = 100;  // Rs. 100 per day after 21 days
        $firstPhaseDays = 21;
    
        // Convert due date to timestamp
        $dueDateTimestamp = strtotime($dueDate);
        $currentDateTimestamp = strtotime(date('Y-m-d'));
    
        // Check if current date is past the due date
        if ($currentDateTimestamp <= $dueDateTimestamp) {
            return 0; // No late fees if still within the due date
        }
    
        // Calculate the number of late days
        $lateDays = ($currentDateTimestamp - $dueDateTimestamp) / (60 * 60 * 24);
    
        // Calculate the late fee based on the late days
        if ($lateDays <= $firstPhaseDays) {
            // Apply Rs. 25 per day for the first 21 days
            $lateFee = $lateDays * $firstPhaseRate;
        } else {
            // Apply Rs. 25 per day for the first 21 days, and Rs. 100 per day after that
            $lateFee = ($firstPhaseDays * $firstPhaseRate) + (($lateDays - $firstPhaseDays) * $secondPhaseRate);
        }
    
        return $lateFee;
    }

    public function customPayment(Request $request) {
        $scholar_id = $request->input('scholar_id');
        // $fee_structure = false;
        // $student = false;
        // $fee_final_data = false;
        if($scholar_id!=""){
            // Fetch student details based on scholar_id
            $student = DB::table('student_registration')->where('scholar_no', $scholar_id)->first();

            if (!$student) {
                return back()->with('error', 'Student not found');
            }

            // Fetch the fee structure
            $fee_structure = DB::table('generate_duechartstatus')
                ->where('student_id', $student->id)
                ->get();

            // Decode the fee structure JSON data
            foreach ($fee_structure as $fee) {
                $fee->json_data = json_decode($fee->json_str, true);
                $fee->sub_json_data = json_decode($fee->json_data[0]["json_str"], true);
            }

            // student old fee receipt challan
            $feeReceipts = DB::table('feesreceiptchallan')
            ->where('student_id',  $student->id)
            ->select('str_json') // Select the 'json_str' column
            ->get();
            $feeReceiptDetails= array();
            //  [fee_head] => BUS FEES [term_number] => 1 [total_fee_first] => 18000.00 [discount_sum] => 0.00 [net_total_sum] => 0.00 [allocated_amount_sum] => 18000.00 [balance_amount_last] => 16200.00
            foreach ($feeReceipts as $receipt) {
                // Decode the JSON string into an associative array
                $jsonData = json_decode($receipt->str_json, true);
                $head_names = explode(",",$jsonData["head_name"]); 
                $head_due_amount = explode(",",$jsonData["head_due_amount"]); 
                $head_rec_amount = explode(",",$jsonData["head_rec_ammount"]); 
                $term_str = explode(",",$jsonData["term_str"]); 
                $head_to_date = explode(",",$jsonData["head_to_date"]); 
                $head_due_date = explode(",",$jsonData["head_due_date"]); 
                foreach($head_names as $key=> $head_name ){
                    if(isset($feeReceiptDetails[$head_name."_".$term_str[$key]])){
                        $feeReceiptDetails[$head_name."_".$term_str[$key]]["allocated_amount_sum"] += $head_rec_amount[$key];
                    }else{
                        $feeReceiptDetails[$head_name."_".$term_str[$key]]["fee_head"]= $head_name;
                        $feeReceiptDetails[$head_name."_".$term_str[$key]]["term_number"]= intval($term_str[$key]);
                        $feeReceiptDetails[$head_name."_".$term_str[$key]]["allocated_amount_sum"]= $head_rec_amount[$key];
                    }
                }
            }
            
            // Fetch student fees exempted
            $exempted_fees_details = DB::table('student_fees_exempted')->where('scholar_no', $scholar_id)->first();
                
            // Student late fee check
            $currDate =  date('d-M-y');
            
            $fee_headwise = array();
            foreach($fee->sub_json_data['account_name'] as $key => $head_wise_data){
                $key_fee_headwise = $fee->sub_json_data['account_name'][$key]."_".intval($fee->sub_json_data['term'][$key]);
                $orig_fees = $fees_exempted = $fee->sub_json_data['fees'][$key];
                $dis_applied = false;
                $remark = "";
                if($exempted_fees_details && ($exempted_fees_details->head_name==$fee->sub_json_data['account_name'][$key])){
                    $fees_exempted = $fee->sub_json_data['fees'][$key]-($fee->sub_json_data['fees'][$key]*($exempt_total/100));
                    if (!(floor($fees_exempted) == $fees_exempted)) {
                        $fees_exempted = ceil($fees_exempted);
                    }
                    $dis_applied = true;
                    $remark = "Discount Applied";
                }
                // $data = $this->calculateLateFee();
                $fee_headwise[$key_fee_headwise] = array(
                    "fees_date" => $fee->sub_json_data['fees_date'][$key],
                    "account_name" => $fee->sub_json_data['account_name'][$key],
                    "fees" => $fees_exempted,
                    "due_date" => $fee->sub_json_data['due_date'][$key],
                    "term" => $fee->sub_json_data['term'][$key],
                );
                if($dis_applied){
                    $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                    $fee_headwise[$key_fee_headwise]["dis_applies_flag"] = 1;
                    $fee_headwise[$key_fee_headwise]["remark"] = $remark;                
                    $fee_headwise[$key_fee_headwise]["percentage"] = $exempt_total;                
                }
                
                // override fees if late fee is there
                if($feeReceipts->isEmpty()){
                    $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                    if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                        $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                        $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                        $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                        $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                        $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                    }
                }else{
                    $fee_receipt_detail_key = $fee->sub_json_data['account_name'][$key]."_".$fee->sub_json_data['term'][$key];
                    if(isset($feeReceiptDetails[$fee_receipt_detail_key])){
                        if($feeReceiptDetails[$fee_receipt_detail_key]["allocated_amount_sum"]<=0){
                            $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                            if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                                $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                                $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                                $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                                $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                                $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                            }
                        }
                    }else{
                        $late_fee = $this->calculateLateFee($fee->sub_json_data['due_date'][$key]);
                        if(!$dis_applied && $fee->sub_json_data['account_name'][$key]=="TUITION FEES" && $late_fee>0){
                            $fee_headwise[$key_fee_headwise]["orig_fees"] = $orig_fees;
                            $fee_headwise[$key_fee_headwise]["fees"] = $fees_exempted+$late_fee;
                            $fee_headwise[$key_fee_headwise]["remark"] = "Late Fee";
                            $fee_headwise[$key_fee_headwise]["late_fee_flag"] = 1;
                            $fee_headwise[$key_fee_headwise]["late_fee"] = $late_fee;
                        }
                    }   
                }
            }

            
            
            // Calculate remaining amounts (fees - allocated)
            $fee_final_data = $fee_headwise;
            $term_map = array(
                "1" => "1st",
                "2" => "2nd",
                "3" => "3rd",
                "4" => "4th"
            );
            foreach ($feeReceiptDetails as $fee) {
                if(isset($fee_headwise[$fee['fee_head']."_".$fee['term_number']])){
                    $allocatedAmount = (float)$fee['allocated_amount_sum'];
                    if($fee['fee_head']=="TUITION FEES"){
                        if(isset($feeReceiptDetails["LATE FEES_".$term_map[$fee['term_number']]])){
                            $allocatedAmount = $allocatedAmount+$feeReceiptDetails["LATE FEES"."_".$term_map[$fee['term_number']]]["allocated_amount_sum"];
                        }
                    }
                    $fee_generate_due_details = $fee_headwise[$fee['fee_head']."_".$fee['term_number']];
                    $term_fees = $fee_generate_due_details["fees"];
                    
                    $fee['term_fee'] = $term_fees;
                    $fee['remaining_amount'] = $term_fees - $allocatedAmount;
                    $fee_final_data[$fee['fee_head']."_".$fee['term_number']]["allocatedAmount"] = $allocatedAmount;
                    $fee_final_data[$fee['fee_head']."_".$fee['term_number']]["remaining_amount"] = $fee['remaining_amount'];
                }
            }
            return view('fee_structure.custom-payment', compact( 'fee_structure', 'student','fee_final_data'));
        }
        return view('fee_structure.custom-payment');
    }

    public function thankYouPage(Request $request)
    {
        // Fetch the transaction ID from the session
        $transactionId = $request->query('txn_id');
        if (!$transactionId) {
            return redirect()->route('fee-structure.index')->with('error', 'Transaction ID not found');
        }
    
        // Fetch payment details from the database
        $paymentDetails = DB::table('student_online_fee_payments')
            ->join('student_registration', 'student_online_fee_payments.student_id', '=', 'student_registration.id')
            ->where('student_online_fee_payments.transaction_id', $transactionId)
            ->where('student_online_fee_payments.status', 'paid') // Make sure the status is correct
            ->select(
                'student_online_fee_payments.*',
                'student_registration.student_name',
                DB::raw("JSON_EXTRACT(student_registration.json_str, '$.fathername') as fathername"),
                DB::raw("JSON_EXTRACT(student_registration.json_str, '$.fathername_prefix') as fathername_prefix"),
                DB::raw("JSON_EXTRACT(student_registration.json_str, '$.section_name') as section_name"),
                'student_registration.class_name',
                'student_registration.date_of_birth',
                'student_registration.created_at',
                'student_registration.form_number',
                'student_registration.scholar_no'
            )
            ->get();
    
        if ($paymentDetails->isEmpty()) {
            return redirect()->route('fee-structure.index')->with('error', 'No payment details found');
        }
    
        // Pass the data to the view
        return view('thank_you', ['paymentDetails' => $paymentDetails, 'transactionId' => $transactionId]);
    }
}