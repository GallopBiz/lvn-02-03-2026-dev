<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Models\Busstop;
use App\Models\Areamaster;
use App\Http\Controllers\Controller;
use App\Models\Course_fees_head_master;
use App\Models\Late_fees_master;
use App\Models\CommanModel;
use App\Models\Student_registration;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class FeesPaymentController extends Controller
{
    //
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

    }

	public function index2()
		{
			return view('fee_structure.index');
		}

    public function index($id = null){
        // $busstops = Busstop::where('is_delete',0)->get();
        // $select_main = Areamaster::All();
        
        // return $form_number;
        
        $data['id']=$id;
        $data['course_fees_head_master'] = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get();//Course_fees_head_master::where('is_delete',0)->get();
        $data['class_names'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $data['holds_structure_row'] = DB::connection('dynamic')->table('holds_structure_row')->get(); //Holds_structure_row::all();
        $data['fees_types'] = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $data['terms'] = DB::connection('dynamic')->table('terms')->where('is_delete',0)->get();
        $data['states'] = DB::connection('dynamic')->table('states');//State::get(["name", "id"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where('id',$id)->get();//CommanModel::fetchDataWhere('student_registration',['id'=> $id]);
        $data['Vehicallist'] = DB::connection('dynamic')->table('generate_duechartstatus')->select('*')->where('student_id',$id)->get();
        $data['late_fees_master'] = DB::connection('dynamic')->table('late_fees_master')->where('id',1)->first(); //Late_fees_master::where('id',1)->first();
        $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
        $data['checkBus'] = DB::connection('dynamic')->table('student_registration')->select('json_str')->where('is_archived', 0)->where('id',$id)->get();
        
        $student_id = 1;
        $class_name = 01;
        $arr_te = DB::connection('dynamic')->table('generate_duechartstatus')
        ->select(
            DB::connection('dynamic')->raw('JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(json_str, "$[0].json_str")), "$.term")) as term')
        )
        ->where('student_id','=',$id)
        ->get();
        $arr_terms = json_decode($arr_te,1);
        $terms = [];
        if (!empty($arr_terms[0])){
            $str_terms = str_replace(['[', ']',' ','"'], '', $arr_terms[0]['term']);
            $terms = array_unique(explode(',',$str_terms));
        }

        return view('backend.fees_payments', compact('terms', 'data'));
    }

    public function save_feesreceipt_user(Request $request){
        // $data = $request->all();
        $data = $request->all();
        $data_student_name = Student_registration::select('student_name','id','form_number')->get();
        $course_fees_head_orders_list_arr = Course_fees_head_master::orderBy('order','ASC')->get();
        $late_fees_master = Late_fees_master::where('id',1)->first();
        $lumpsum_fees = DB::connection('dynamic')->table('fees_types_master')->where('fees_type','=','lumpsum fees deposit')->first();
        $siblings = DB::connection('dynamic')->table('fees_types_master')->where('fees_type','=','siblings')->first();
        // return view('backend.Fees-module.Student_ledger_Fees_receipt_challan',compact('data','data_student_name','course_fees_head_orders_list_arr','late_fees_master','lumpsum_fees','siblings'));
        return view('backend.save_feesreceipt_user',compact('data','data_student_name','course_fees_head_orders_list_arr','late_fees_master','lumpsum_fees','siblings'));
    }

    public function fees_payments_success($id,Request $request){
        $check = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id','=',$id)->first();

        if(empty($check)){
            $generate_due = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id','=',$id)->first();
            $generate_due_j = json_decode($generate_due->json_str,1);
            $generate_due_chat = json_decode($generate_due_j[0]['json_str'],1);
            $data = $request->get('data');
            $sum_amount = $generate_due_chat['fees'];
            $json = [
                'head_name' => implode(',', $generate_due_chat['account_name']),
                'head_due_amount' => implode(',', $generate_due_chat['fees']),
                'head_rec_ammount' => implode(',', $generate_due_chat['fees']),
                // 'head_from_ammount' => implode(',', $requgenerate_due_chatest['head_from_ammount']),
                'term_str' => implode(',',$generate_due_chat['term']),
                'head_to_date' => implode(',', $generate_due_chat['fees_date']),
                'head_due_date' => implode(',', $generate_due_chat['due_date']),
                'name_father' => null,
                'name_classsection' => $data['class_name'],
                'name_admdt' => now(),
                'name_formno' => null,
                'name_scholarno' => $data['scholar_no'],
                'feestype' => 0,
                'total_dueamount' => array_sum($sum_amount),
                'sub_total_due' => array_sum($sum_amount),
                'sub_total_received' => array_sum($sum_amount),
                'total_received' => null,
                'balance_f' => null,
                'grand_total_due' => array_sum($sum_amount),
                'grand_total_received' => null,
                'balance_due' => null,
                'fee_commitment_remarks' => null,
                'received_amount_details_remarks' => 'online',
                'by_cash' => array_sum($sum_amount),
                'late_fees_rate' => null,
                'late_fees_rate_due' => null,
                'late_fees_rate_received' => null,
                'payment_by_select' => null,
                'late_fees_waive_off_due' => null,
                'late_fees_waive_off_received' => null,
                'from_employee' => null,
                'old_advance_fees_due' => null,
                'old_advance_fees_received' => null,
                'from_party' => null,
                'transfer_to_party' => null,
                'late_fees_on_posting_due' => null,
                'late_fees_on_posting_received' => null,
                'advance_due' => null,
                'advance_received' => null,
                'late_fees_account' => null,
                'advance_fees_account' => null,
            ];
            $count_date = count($data['head_to_date']);
            $data = [
                'student_id' => $id,
                'student_dob' => $data['student_dob'],
                'recpt_chain' => null,
                'due_upto' => $data['head_to_date'][$count_date - 1],
                'name_student' => $data['student_name'],
                'str_json' => json_encode($json),
            ];
            $cfs_list = DB::connection('dynamic')->table('feesreceiptchallan')->insert($data);
        }
        
        $generate_due_data = $request->get('data');
        $sum_amount_1 = $generate_due_data['headfees'];
        $json1 = [
            'head_name' => implode(',', $generate_due_data['headname']),
            'head_due_amount' => implode(',', $generate_due_data['headfees']),
            'head_rec_ammount' => implode(',', $generate_due_data['headfees']),
            // 'head_from_ammount' => implode(',', $requgenerate_due_chatest['head_from_ammount']),
            'term_str' => implode(',',$generate_due_data['term_str']),
            'head_to_date' => implode(',', $generate_due_data['head_to_date']),
            'head_due_date' => implode(',', $generate_due_data['head_due_date']),
            'name_father' => null,
            'name_classsection' => $generate_due_data['class_name'],
            'name_admdt' => date('d-m-Y'),
            'name_formno' => null,
            'name_scholarno' => $generate_due_data['scholar_no'],
            'feestype' => 0,
            'total_dueamount' => array_sum($sum_amount_1),
            'sub_total_due' => array_sum($sum_amount_1),
            'sub_total_received' => array_sum($sum_amount_1),
            'total_received' => null,
            'balance_f' => null,
            'grand_total_due' => array_sum($sum_amount_1),
            'grand_total_received' => null,
            'balance_due' => null,
            'fee_commitment_remarks' => null,
            'received_amount_details_remarks' => 'online',
            'by_cash' => array_sum($sum_amount_1),
            'late_fees_rate' => null,
            'late_fees_rate_due' => null,
            'late_fees_rate_received' => null,
            'payment_by_select' => null,
            'late_fees_waive_off_due' => null,
            'late_fees_waive_off_received' => null,
            'from_employee' => null,
            'old_advance_fees_due' => null,
            'old_advance_fees_received' => null,
            'from_party' => null,
            'transfer_to_party' => null,
            'late_fees_on_posting_due' => null,
            'late_fees_on_posting_received' => null,
            'advance_due' => null,
            'advance_received' => null,
            'late_fees_account' => null,
            'advance_fees_account' => null,
        ];
        $count_date = count($generate_due_data['head_to_date']);

        $data1 = [
            'student_id' => $id,
            'student_dob' => $generate_due_data['student_dob'],
            'recpt_chain' => null,
            'due_upto' => $generate_due_data['head_to_date'][$count_date - 1],
            'name_student' => $generate_due_data['student_name'],
            'str_json' => json_encode($json1),
        ];

        $cfs_list = DB::connection('dynamic')->table('feesreceiptchallan')->insert($data1);
        
        $data['id']=$id;
        $data['course_fees_head_master'] = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get();//Course_fees_head_master::where('is_delete',0)->get();
        $data['class_names'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $data['holds_structure_row'] = DB::connection('dynamic')->table('holds_structure_row')->get(); //Holds_structure_row::all();
        $data['fees_types'] = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $data['terms'] = DB::connection('dynamic')->table('terms')->where('is_delete',0)->get();
        $data['states'] = DB::connection('dynamic')->table('states');//State::get(["name", "id"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where('id',$id)->get();//CommanModel::fetchDataWhere('student_registration',['id'=> $id]);
        $data['Vehicallist'] = DB::connection('dynamic')->table('generate_duechartstatus')->select('*')->where('student_id',$id)->get();
        $data['late_fees_master'] = DB::connection('dynamic')->table('late_fees_master')->where('id',1)->first(); //Late_fees_master::where('id',1)->first();
        $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
        $data['checkBus'] = DB::connection('dynamic')->table('student_registration')->select('json_str')->where('is_archived', 0)->where('id',$id)->get();

        // return $data['all_inquiry'];
        // $student_id = 1;
        // $class_name = 01;
        $arr_te = DB::connection('dynamic')->table('generate_duechartstatus')
        ->select(
            DB::connection('dynamic')->raw('JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(json_str, "$[0].json_str")), "$.term")) as term')
        )
        ->where('student_id','=',$id)
        ->get();
        $arr_terms = json_decode($arr_te,1);
        $terms = [];
        if (!empty($arr_terms[0])){
            $str_terms = str_replace(['[', ']',' ','"'], '', $arr_terms[0]['term']);
            $terms = array_unique(explode(',',$str_terms));
        }

        $data['result'] = $request->get('data');
        
        return view('backend.save_feesreceipt_user', compact('terms', 'data'));
    }


    public function search_student_name(Request $request){
        
        $form_number = $request->post('form_number');        
        $student = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where("form_number","=", $form_number)->get();                
        // print_r($student);die();
        $student_id = $student[0]->id;
        $due_chart_data = DB::connection('dynamic')->table('feesreceiptchallan')->select('str_json')->where('student_id',$student_id)->get();
        $data2 = [];
        $advance_received = 0;
        $data2['receiptchallan']= 0;
        if(!empty($due_chart_data)){
            
            $json_str_arr = json_decode($due_chart_data,1);
            // $decode_json = json_decode('{"head_name":"ADMISSION FEES,ALUMNI FEES,CUATION MONEY","head_due_amount":"3000,1050,2000","head_rec_ammount":"3000,,","term_str":"1st,1st,1st","head_to_date":"2023-08-10,2023-08-10,2023-08-10","head_due_date":"2023-08-24,2023-08-24,2023-08-24","name_father":"Balram Prajapat","name_classsection":null,"name_admdt":"2023-07-18T09:29:21.000000Z","name_formno":"7303","name_scholarno":null,"feestype":"0","total_dueamount":null,"fee_commitment_remarks":null,"received_amount_details_remarks":null,"by_cash":"3000","late_fees_rate":"23434","late_fees_rate_due":null,"late_fees_rate_received":null,"payment_by_select":null,"late_fees_waive_off_due":null,"late_fees_waive_off_received":null,"from_employee":null,"old_advance_fees_due":null,"old_advance_fees_received":null,"from_party":null,"transfer_to_party":null,"late_fees_on_posting_due":null,"late_fees_on_posting_received":null,"advance_due":null,"advance_received":null,"late_fees_account":null,"advance_fees_account":null}',1);
            $result = [];
            $demoIndex = 1;
            foreach($json_str_arr as $json_str){
                $json_str_json = json_decode($json_str['str_json'],1);
                $head_names = explode(',', $json_str_json['head_name']);
                $head_due_amount = explode(',', $json_str_json['head_due_amount']);
                $head_rec_ammount = explode(',', $json_str_json['head_rec_ammount']);
                $term_str = explode(',', $json_str_json['term_str']);
                $head_to_date = explode(',', $json_str_json['head_to_date']);
                $head_due_date = explode(',', $json_str_json['head_due_date']);
                $advance_received = $json_str_json['advance_received'];
                for ($i = 0; $i < count($head_names); $i++) {
                    if($head_due_amount[$i] === $head_rec_ammount[$i]){
                        $demoData = [
                            "head_name" => $head_names[$i],
                            "head_due_amount" => $head_due_amount[$i],
                            "head_rec_ammount" => $head_rec_ammount[$i],
                            "term_str" => $term_str[$i],
                            "head_to_date" => $head_to_date[$i],
                            "head_due_date" => $head_due_date[$i],
                        ];
                    } else {
                        $demoData = [
                            "head_name" => $head_names[$i],
                            "head_due_amount" => $head_due_amount[$i],
                            "head_rec_ammount" => $head_rec_ammount[$i],
                            "term_str" => $term_str[$i],
                            "head_to_date" => $head_to_date[$i],
                            "head_due_date" => $head_due_date[$i],
                        ];
                        
                    }
                    $demoIndex++;
                    $result[$demoIndex] = $demoData;
                    $demoIndex++;
                }
                
            }
            $uniqueData = [];

            foreach ($result as $key => $value) {
                $headName = $value['head_name'].'_'.$value['term_str'];
                $headRecAmount = $value['head_rec_ammount'];
            
                if (!isset($uniqueData[$headName])) {
                    $uniqueData[$headName] = [
                        "head_name" => $value['head_name'],
                        "head_due_amount" => $value['head_due_amount'],
                        "term_str" => $value['term_str'],
                        "head_to_date" => $value['head_to_date'],
                        "head_due_date" => $value['head_due_date'],
                        'head_rec_ammount' => 0,
                    ];
                }
            
                // Calculate the total received amount for each head_name
                if (!empty($headRecAmount)) {
                    $uniqueData[$headName]['head_rec_ammount'] += (float) $headRecAmount;
                }
            }

            $finalUniqueData = array_values($uniqueData);
            $data2['receiptchallan']= $finalUniqueData;
        }
        $generate_due = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id','=',$student_id)->first();
        
        $generate_due_j = json_decode($generate_due->json_str,1);
        $generate_due_chat = json_decode($generate_due_j[0]['json_str']);
        $data2['generate_due_chat'] = $generate_due_chat;

        $data2['generate_duechartstatus'] = DB::connection('dynamic')->table('generate_duechartstatus')->where("student_id", "{$student_id}")->get();        
        return $data2;
    }



    public function get_student_info(Request $request) {
        $result = Student_registration::where('id', $request->post('student_id'))->first();
    
        // ...
    
        $due_dates_ja = (!empty($due_dates_ja) && is_array($due_dates_ja)) ? array_unique($due_dates_ja) : [];
    
        // ...

        if (!empty($due_chart_data) && $due_chart_data->json_str !== null) {
            $json_str_arr = json_decode($due_chart_data->json_str, 1);
            $decode_json = json_decode($json_str_arr[0]['json_str'], 1);
            $result = [];
    
            $demoIndex = 1;
            for ($i = 0; $i < count($decode_json["fees_date"]); $i++) {
                $demoData = [
                    "fees_date" => $decode_json["fees_date"][$i],
                    "account_name" => $decode_json["account_name"][$i],
                    "fees" => $decode_json["fees"][$i],
                    "due_date" => $decode_json["due_date"][$i],
                    "term" => $decode_json["term"][$i]
                ];
                $result["Demo" . $demoIndex] = $demoData;
                $demoIndex++;
            }
    
            $data2['due_chart_data'] = (!empty($result) && is_array($result)) ? $result : [];
        }
    
        $data2['siblings'] = (!empty($result1[1]['siblings_name']) ? $result1[1]['siblings_name'] : '0');
        $data2['course_fees_head_orders_list_arr'] = $course_fees_head_orders_list_arr;
    
        return json_encode($data2);
    }
    


    public function student_details(Request $request) {
        $student_id = $request->input('student_id');
    
        $student = Student_registration::where('form_number', $student_id)->first();
        return $student;
    }
    

    // public function search_student_name(Request $request){
    //     $result = DB::table('inquiry_registration')->select('student_name','id')->where("student_name","LIKE", "%{$request->post('student_name')}%")->get();
    //     return json_encode($result);
    // }

    // public function get_student_info(Request $request){

    //     $result = Student_registration::where('id',$request->post('student_id'))->first();

    //     $due_chart_data = Feesreceiptchallan::select('str_json')->where('student_id',$request->post('student_id'))->first();

    //     $due_dates = DB::table('course_fees_structure_master')->select('json_str')->where('class_name','=',$result->class_name)->first();
        
    //     $due_dates_j = (!empty($due_dates)) ? json_decode($due_dates->json_str,1) : '' ;
    //     $due_dates_ja = (!empty($due_dates_j)) ? $due_dates_j['due_date'] : '' ;
    //     // $due_dates_j = json_decode($due_dates->json_str,1);
    //     // $due_dates_ja = $due_dates_j['due_date'];

    //    $course_fees_head_orders_list_arr = Course_fees_head_master::select('ac_head_name')->orderBy('order','ASC')->get();



    //     $arr = json_decode($result['json_str'],1);
    //     $result1 = [];
    //     $data2 = [];
    //     foreach($arr as $k => $ar){

    //         if('fathername' == $k) {
    //             $result['fathername'] = $ar;
    //             $data[] = [
    //                     $k => $ar,             
    //             ];
    //         }
    //         if('siblings_name' == $k) {
    //             $result1['1'] = [
    //                 $k => $ar,
    //             ];
    //         }
    //     }
        
    //     $data_dt[] = ['adm_dt' => $result->created_at];
    //     $data1 = array_merge($data,$data_dt);

    //     for ($i=0;$i<count($data1);$i++){
    //         $data2 = [
    //             'formno' => (!empty($result['form_number'])) ? $result['form_number'] : '',
    //             'student_name' => (!empty($result['student_name'])) ? $result['student_name'] : '',
    //             'fathername' => (!empty($result['student_father_name'])) ? $result['student_father_name'] : '',
    //             // 'siblings' => (!empty($result1['siblings_name'])) ? $result1['siblings_name'] : '',
    //             'adm_dt' => (!empty($result->created_at)) ? $result->created_at : '',
    //             'class_name' => (!empty($result->class_name)) ? $result->class_name : '',
    //             'fee_commitment_remarks' => (!empty($result['fee_commitment_remarks'])) ? $result['fee_commitment_remarks'] : '',
    //             'due_upto' => (!empty($due_dates_ja)) ? array_unique($due_dates_ja) : '' ,
    //         ];
    //     }

    //     if(!empty($due_chart_data) && $due_chart_data->json_str!==null){
            
    //         $json_str_arr = json_decode($due_chart_data->json_str,1);
    //         $decode_json = json_decode($json_str_arr[0]['json_str'],1);
    //         $result = [];
    //         $demoIndex = 1;
    //         for ($i = 0; $i < count($decode_json["fees_date"]); $i++) {
    //             $demoData = [
    //                 "fees_date" => $decode_json["fees_date"][$i],
    //                 "account_name" => $decode_json["account_name"][$i],
    //                 "fees" => $decode_json["fees"][$i],
    //                 "due_date" => $decode_json["due_date"][$i],
    //                 "term" => $decode_json["term"][$i]
    //             ];
    //             $result["Demo" . $demoIndex] = $demoData;
    //             $demoIndex++;
    //         }

    //         $data2['due_chart_data'] = $result;
    //     }
    //     $data2['siblings'] = (!empty($result1[1]['siblings_name']) ? $result1[1]['siblings_name'] : '0');
    //     $data2['course_fees_head_orders_list_arr'] = $course_fees_head_orders_list_arr;
    //     // $data2['generate_due_chat'] = $generate_due_chat;
    //     return json_encode($data2);
    // }






}
