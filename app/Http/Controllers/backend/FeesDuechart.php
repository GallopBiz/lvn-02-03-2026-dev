<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Models\FeesDuechartModels;
use App\Models\course_fees_head_master;
use App\Http\Controllers\Controller;
use App\Models\CommanModel;
use App\Models\Classes;
use App\Models\Generate_duechartstatus;
use DB;
use Illuminate\Support\Facades\Config;

class FeesDuechart extends Controller
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
    }

    public function generate_due_chart()
    {
        $datas = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        return view('backend.FeesDue-chart.Generate_due_chart', compact('datas'));
    }
    public function class_student(Request $request){
        $id = $request->id;
        $listclassstudent = DB::connection('dynamic')->table('classes')->where('class_name',$id)->get(); //Classes::where('class_name',$id)->get();

        return json_encode($listclassstudent,1);
    }
    public function generate_due_chart_status()
    {
        return view('backend.FeesDue-chart.generate_due_chart_status');
    }

    public function save_due_chart(Request $request)
    {
        $classname = $request->classname;
        $sectionname = $request->secationname1;
        // $data['datas'] = DB::connection('dynamic')->table('student_registration')->select('class_name')->distinct()->get();
        $data['datas'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $data['duachart'] = DB::connection('dynamic')
        ->table('student_registration')
        ->where('class_name', $classname)
        ->whereRaw("JSON_EXTRACT(json_str, '$.section_name') = ?", [$sectionname])
        ->get();
        //DB::connection('dynamic')->table('student_registration')->where('class_name',$classname)->get(); //CommanModel::fetchDataWhere('student_registration', ['class_name' => $classname]);
        $data['feesstructure'] = DB::connection('dynamic')->table('course_fees_structure_master')->where('class_name',$classname)->get(); //CommanModel::fetchDataWhere('course_fees_structure_master', ['class_name' => $classname]);
        $data['duachart_data'] = DB::connection('dynamic')->table('generate_duechartstatus')->where('class_name', '=', $classname)->where('sectionname', '=', $sectionname)->get();
        $data['classname'] = $classname;
        $data['sectionname'] = $sectionname;
        return view('backend.FeesDue-chart.Generate_due_chart', $data);

    }
    public function save_student_duechart(Request $request)
    {
        $sid = json_decode($request->studentid, true);
        $course_fees_structure_master_data = DB::connection('dynamic')->table('course_fees_structure_master')->where('class_name', $request->classname)->get();    

        if (empty($course_fees_structure_master_data)) {
            $course_fees_structure_master_data_json = '';
        } else {
            $course_fees_structure_master_data_json_1 = json_encode($course_fees_structure_master_data);
        }

        $i = 0;
        $amount = '';
        $currentYear = date("Y");
        $nextYear = $currentYear + 1;
        $session = '"'.$currentYear . '_' . $nextYear.'"';
        foreach ($sid as $id) {
            // $name = DB::table('student_registration')->select('id','student_name')->where('id','=',$id['student_id'])->first();
            $admission_type = DB::connection('dynamic')->table('student_registration')->select('student_name', 'id', DB::connection('dynamic')->raw('JSON_EXTRACT(json_str, "$.admission_type") = "RTE" as admission_type'))->where('id', '=', $id)->first();
            // $admission_type = DB::connection('dynamic')->table('student_registration')->select('id as admission_type','id','student_name')->where('application_for','=','RTE')->where('id', '=', $id)->first();
            $school_trasnport = DB::connection('dynamic')->table('student_registration')->select('student_name', 'id', DB::connection('dynamic')->raw('JSON_EXTRACT(json_str, "$.required_school_transport") = "1" as required_school_transport'))->where('id', '=', $id)->first();
            $session_name = DB::connection('dynamic')->table('student_registration')->select(DB::connection('dynamic')->raw('JSON_EXTRACT(json_str, "$.batch") as session_name'))->where('id', '=', $id)->first();
            // print_r($session_name);die();
		
            if ($school_trasnport !== null && $school_trasnport->required_school_transport != 1){
                $school_trasnport->required_school_transport = 0;
            }
            
            // echo '<pre>'; print_r($school_trasnport->required_school_trasnport);die();
            //         $query1 = DB::table('student_registration')
            // ->select('class_name', DB::raw('COUNT(*) as count, SUM(JSON_EXTRACT(json_str, "$.required_school_trasnport") = "1") as transport_count'))
            // ->groupBy('class_name');
            $decode = json_decode($course_fees_structure_master_data_json_1,1);
            $decode1 = json_decode($decode[0]['json_str'], true);
            // print_r($session_name->session_name);die();
            
            if ($session_name !== null && $session >= $session_name->session_name) {
                if (!empty($decode1["account_name"])) {
                    // 
                    if($school_trasnport->required_school_transport == 0){
                        $itemsToRemove = ["ADMISSION FEES", "BUS FEES" ,"CUATION MONEY", "ALUMNI FEES"];
                    } else {
                        $itemsToRemove = ["ADMISSION FEES", "CUATION MONEY", "ALUMNI FEES"];
                    }
                    
                    $indexesToRemove = [];
                    
                    foreach ($itemsToRemove as $itemToRemove) {
                        // Loop through the arrays and remove all occurrences of the item
                        foreach ($decode1["account_name"] as $key => $accountName) {
                            if ($accountName === $itemToRemove) {
                                unset($decode1["account_name"][$key]);
                                unset($decode1["fees"][$key]);
                                unset($decode1["fees_date"][$key]);
                                unset($decode1["due_date"][$key]);
                                unset($decode1["term"][$key]);
                            }
                        }
                        
                        // Reindex the arrays to remove gaps in the keys
                        $decode1["account_name"] = array_values($decode1["account_name"]);
                        $decode1["fees"] = array_values($decode1["fees"]);
                        $decode1["fees_date"] = array_values($decode1["fees_date"]);
                        $decode1["due_date"] = array_values($decode1["due_date"]);
                        $decode1["term"] = array_values($decode1["term"]);
                    }
                    
                    
                    // // Sort the indexes in reverse order so that removal doesn't affect subsequent indexes
                    // rsort($indexesToRemove);
                    
                    // foreach ($indexesToRemove as $index) {
                    //     // Unset the item at the specified index in both arrays
                    //     unset($decode1["account_name"][$index]);
                    //     unset($decode1["fees"][$index]);
                    //     unset($decode1["fees_date"][$index]);
                    //     unset($decode1["due_date"][$index]);
                    //     unset($decode1["term"][$index]);

                    //     // Reindex the arrays
                    //     $decode1["account_name"] = array_values($decode1["account_name"]);
                    //     $decode1["fees"] = array_values($decode1["fees"]);
                    //     $decode1["fees_date"] = array_values($decode1["fees_date"]);
                    //     $decode1["due_date"] = array_values($decode1["due_date"]);
                    //     $decode1["term"] = array_values($decode1["term"]);
                    // }
                }
                

                $feesSum = 0;
                if (!empty($decode1["fees"])) {
                    foreach ($decode1["fees"] as $fee) {
                        $feesSum += intval($fee); // Convert to integer and add to the sum
                    }
                }

                // Create a new JSON with the modified data
                $json1 = [
                    'id' => $decode[0]['id'],
                    'class_name' => $decode[0]['class_name'],
                    'session_name' => $session_name->session_name,
                    'fees_type_name' => $decode[0]['fees_type_name'],
                    'cast_category' => $decode[0]['cast_category'],
                    'batch' => $decode[0]['batch'],
                    'json_str' => json_encode($decode1), // Encode the modified data
                    'total_above_fees' => $feesSum, // Calculate the total fees amount
                    'is_delete' => $decode[0]['id'],
                    'created_at' => $decode[0]['id'],
                    'updated_at' => $decode[0]['id'],
                ];

                $json_arr = json_encode([$json1]);
                $course_fees_structure_master_data_json = $json_arr;
                $amount =($admission_type && $admission_type->admission_type != 1) ? $feesSum : '0';

            } else {
                // $decode[0]['json_str']
                if (!empty($decode1["account_name"])) {
                    if($school_trasnport !== null && $school_trasnport->required_school_transport == 0){
                        $itemsToRemove1 = ["BUS FEES"];
                    } else {
                        $itemsToRemove1 = ["FEES"];
                    }
                    $indexesToRemove1 = [];
                    
                    foreach ($itemsToRemove1 as $itemToRemove) {
                        // Loop through the arrays and remove all occurrences of the item
                        foreach ($decode1["account_name"] as $key => $accountName) {
                            if ($accountName === $itemToRemove) {
                                unset($decode1["account_name"][$key]);
                                unset($decode1["fees"][$key]);
                                unset($decode1["fees_date"][$key]);
                                unset($decode1["due_date"][$key]);
                                unset($decode1["term"][$key]);
                            }
                        }
                        
                        // Reindex the arrays to remove gaps in the keys
                        $decode1["account_name"] = array_values($decode1["account_name"]);
                        $decode1["fees"] = array_values($decode1["fees"]);
                        $decode1["fees_date"] = array_values($decode1["fees_date"]);
                        $decode1["due_date"] = array_values($decode1["due_date"]);
                        $decode1["term"] = array_values($decode1["term"]);
                    }
                    
                    // Sort the indexes in reverse order so that removal doesn't affect subsequent indexes
                    // rsort($indexesToRemove1);
                    
                    // foreach ($indexesToRemove1 as $index1) {
                    //     // Unset the item at the specified index in both arrays
                    //     unset($decode1["account_name"][$index1]);
                    //     unset($decode1["fees"][$index1]);
                    //     unset($decode1["fees_date"][$index1]);
                    //     unset($decode1["due_date"][$index1]);
                    //     unset($decode1["term"][$index1]);

                    //     // Reindex the arrays
                    //     $decode1["account_name"] = array_values($decode1["account_name"]);
                    //     $decode1["fees"] = array_values($decode1["fees"]);
                    //     $decode1["fees_date"] = array_values($decode1["fees_date"]);
                    //     $decode1["due_date"] = array_values($decode1["due_date"]);
                    //     $decode1["term"] = array_values($decode1["term"]);
                    // }
                }
                

                $feesSum = 0;
                if (!empty($decode1["fees"])) {
                    foreach ($decode1["fees"] as $fee) {
                        $feesSum += intval($fee); // Convert to integer and add to the sum
                    }
                }

                // Create a new JSON with the modified data
                $json1 = [
                    'id' => $decode[0]['id'],
                    'class_name' => $decode[0]['class_name'],
                    'session_name' => $session_name ? $session_name->session_name : null,
                    'fees_type_name' => $decode[0]['fees_type_name'],
                    'cast_category' => $decode[0]['cast_category'],
                    'batch' => $decode[0]['batch'],
                    'json_str' => json_encode($decode1), // Encode the modified data
                    'total_above_fees' => $feesSum, // Calculate the total fees amount
                    'is_delete' => $decode[0]['id'],
                    'created_at' => $decode[0]['id'],
                    'updated_at' => $decode[0]['id'],
                ];

                $json_arr = json_encode([$json1]);
                $course_fees_structure_master_data_json = $json_arr;
                $amount = ($admission_type && $admission_type->admission_type != 1) ? $feesSum : '0';
            }

            // if ($school_trasnport->required_school_trasnport == 0) {
            //     $decode = json_decode($course_fees_structure_master_data_json,1);
            //     $decode1 = json_decode($decode[0]['json_str'],1);
            //     if(!empty($decode1["account_name"])){
            //         $indexToRemove = array_search("BUS FEES", $decode1["account_name"]);
            //     }
            //     // if ($session != $session_name->session_name) {
            //     //     if(!empty($decode1["account_name"])){
            //     //         $indexToRemove = array_search("ADMISSION FEES", $decode1["account_name"]);
            //     //     }
            //     // }
            //     // Create a new array to hold the modified subarrays
            //     $newData = [];

            //     foreach ($decode1 as $key => $values) {
            //         // Check if the index to remove is within bounds for this subarray
            //         if ($indexToRemove !== false && isset($values[$indexToRemove])) {
            //             // Remove the element at the specified index
            //             unset($values[$indexToRemove]);
            //             // Reindex the array
            //             $values = array_values($values);
            //         }

            //         // Add the modified subarray to the new array
            //         $newData[$key] = $values;
            //     }
            //     $feesSum = 0;
            //     if(!empty($decode1["account_name"])){
            //         foreach ($newData["fees"] as $fee) {
            //             $feesSum += intval($fee); // Convert to integer and add to the sum
            //         }
            //     }
                
            //     $json = json_encode($newData);
            //     $json1 = [
            //         'id' => $decode[0]['id'],
            //         'class_name' => $decode[0]['class_name'],
            //         'session_name' => $session_name->session_name,
            //         'fees_type_name' => $decode[0]['fees_type_name'],
            //         'cast_category' => $decode[0]['cast_category'],
            //         'batch' => $decode[0]['batch'],
            //         'json_str' => $json,
            //         'total_above_fees' => $feesSum,
            //         'is_delete' => $decode[0]['id'],
            //         'created_at' => $decode[0]['id'],
            //         'updated_at' => $decode[0]['id'],
            //     ];
            //     $json_arr = json_encode([$json1]);
            //     $course_fees_structure_master_data_json = $json_arr;
            //     $amount = $feesSum;
            //     if ($admission_type->admission_type != 1) {
            //         $amount = $feesSum;
            //     } else {
            //         $amount = '0';
            //     }
            // } else {
            //     // if ($admission_type->admission_type != 1) {
            //     //     $amount = $request->amount;
            //     // } else {
            //     //     $amount = '0';
            //     // }
            //     //$amount = $request->amount;
            // }

            $select_id = DB::connection('dynamic')->table('generate_duechartstatus')->select('id')->where('student_id', '=', $id)->where('class_name', '=', $request->classname)->first();
            $insertArr[] = [
                'student_id' => $id,
                'class_name' => $request->classname,
                'session_name' => $session_name ? $session_name->session_name : null,
                'sectionname' => $request->sectionname,
                'amount' => $amount = ($admission_type && $admission_type->admission_type != 1) ? $feesSum : '0',//(!empty($feesSum)) ? $feesSum : $request->amount,
                'json_str' => $course_fees_structure_master_data_json,
                'is_rte' => (!empty($admission_type->admission_type)) ? 1 : 0,
                'status' => 'g',
            ];
            
            if (empty($select_id)) {
                DB::connection('dynamic')->table('generate_duechartstatus')->insert($insertArr[$i]);
            }
            $i++;
        }
        foreach ($insertArr as $arr) {
            $name = DB::connection('dynamic')->table('student_registration')->select('id', 'student_name')->where('id', '=', $arr['student_id'])->first();
            $insertArrnext[] = [
                'student_id' => $name->id,
                'student_name' => $name->student_name,
                'class_name' => $request->classname,
                'session_name' => $session_name->session_name,
                'sectionname' => $request->sectionname,
                'amount' => $arr['amount'],
                'json_str' => $course_fees_structure_master_data_json,
                'status' => 'g',
            ];
        }
        // echo '<pre>';print_r($insertArrnext);die();
        // return redirect('generate-due-chart-list')->with('success', 'Fees Due Chart Generate successfully')->with('data', $insertArrnext);
        return redirect()->to('generate-due-chart-list/'.$request->classname.'/'.$request->sectionname)->with('success','Fees Due Chart Generate successfully')->with('data', $insertArrnext);
    }

    // public function generate_due_chart_list($id,$session){
    //     // $data = session('data');
    //     // $data = Generate_duechartstatus::All();

    //    $data = DB::table('generate_duechartstatus')
    //         ->join('student_registration', 'generate_duechartstatus.student_id', '=', 'student_registration.id')
    //         ->select('generate_duechartstatus.*', 'student_registration.student_name')
    //         ->where('generate_duechartstatus.class_name', $id)
    //         ->get();
    //     $info['id'] = $id;
    //     $info['session'] = $session;
    //     $data = $data->map(function ($i) {
    //         return (array) $i;
    //     })->toArray();
    //     $classlist = DB::table('class_name')->select('class_name')->distinct()->get();
    //     return view('backend.FeesDue-chart.Generate_due_chart_list', compact('info','data','classlist'));
    //     // return view('backend.FeesDue-chart.Generate_due_chart_list',compact('data'));
    // }


    public function generate_due_chart_list($id, $session, Request $request) {
        $searchTerm = $request->input('search_term'); 
        
        $query = DB::connection('dynamic')->table('generate_duechartstatus')
            ->join('student_registration', 'generate_duechartstatus.student_id', '=', 'student_registration.id')
            ->select('generate_duechartstatus.*', 'student_registration.student_name')
            ->where('generate_duechartstatus.class_name', $id);
        if($session != "") {
            $query->where('sectionname', '=', $session);
        }
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('generate_duechartstatus.student_id', '=', $searchTerm)
                      ->orWhere('student_registration.student_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('generate_duechartstatus.class_name', 'like', '%' . $searchTerm . '%');
            });
        }
    
        $data = $query->get();
        $info['id'] = $id;
        $info['session'] = $session;
        $data = $data->map(function ($i) {
            return (array) $i;
        })->toArray();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        return view('backend.FeesDue-chart.Generate_due_chart_list', compact('info', 'data', 'classlist'));
    }
    
    















}