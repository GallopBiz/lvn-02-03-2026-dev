<?php

namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Session;
use Exception;
  
class RazorpayPaymentController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {        
        unset($_SESSION['success']);
        return view('razorpayView');
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function store(Request $request)
    {
        $input = $request->all();
  
        $api = new Api('rzp_test_JOC0wRKpLH1cVW', '9EzSlxvJbTyQ2Hg0Us5ZX4VD');
  
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        $data = [
            'headname' => $input['headname'],
            'headfees' => $input['headfees'],
            'hidden_total_dueamount' => $input['hidden_total_dueamount'],
            'termSelect_select' => $input['termSelect_select'],
            'term_str' => $input['term_str'],
            'head_to_date' => $input['head_to_date'],
            'head_due_date' => $input['head_due_date'],
            'student_dob' => $input['student_dob'],
            'scholar_no' => $input['scholar_no'],
            'student_name' => $input['name_student'],
            'class_name' => $input['class_name'],
        ];
        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount'])); 
            } catch (Exception $e) {
                return  $e->getMessage();
                Session::put('error',$e->getMessage());
                return redirect()->back();
            }
        }
          
        // Session::put('success', 'Payment successful');
        // return redirect()->back()->with('success','Payment successful.');
        return redirect()->route('fees_payments_success',['id' => $input['student_id'],'data' => $data])->with('success','Payment successful.');
    }
}

