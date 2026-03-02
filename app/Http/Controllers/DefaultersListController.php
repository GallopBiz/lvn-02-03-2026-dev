<?php

namespace App\Http\Controllers;

// use App\Models\TeacherSubject;
use App\Http\Controllers\Controller;
// use App\Models\CommanModel;
use App\Models\Student_registration;
use App\Models\Subject;
use App\Models\DefaultersList;
use App\Models\Generate_duechartstatus;
use App\Models\Course_fees_head_master;
use Illuminate\Http\Request;
use League\Csv\Writer;
use DB;
use PDF;
use Config;
class DefaultersListController extends Controller
{      
    // public function index(){        
    //     return view('backend.Fees-module.Defaulters_list');
    // }

    public function index(){
        $studentclasses = DB::table('classes')->select('class_name')->distinct()->get();

        $headss = Course_fees_head_master::select('ac_head_name')->distinct()->get();
        

        $student_ids = DB::table('generate_duechartstatus')
        ->join('student_registration', 'generate_duechartstatus.student_id', '=', 'student_registration.id')
        ->select('generate_duechartstatus.student_id', 'student_registration.student_name')
        ->get();


        $studentsession = Student_registration::select('session_name')->distinct()->get();
        $subjects = Subject::select('id','subject_name')->distinct('subject_name')->get();
        $session = Student_registration::select('id','session_name')->distinct('session_name')->get();
        $teachersession = Student_registration::all();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();

        return view('backend.Fees-module.Defaulters_list', compact('studentclasses', 'classlist', 'subjects', 'session', 'studentsession','teachersession', 'headss','student_ids'));
    }

    public function exportPdf(){        
        $teachersubjects = DB::table('defaulters_lists')
        ->join('student_registration', 'defaulters_lists.student_id', '=', 'student_registration.id')        
        ->select('defaulters_lists.*', 'student_registration.student_name')
        ->get();

        // return view('backend.Fees-module.Defaulters_list', compact('teachersubjects'));

        // $pdfs = DefaultersList::get();
        $pdf = PDF::loadView('backend.Fees-module.Defaulters_list_pdf', compact('teachersubjects'));  
        // return view('backend.Fees-module.Defaulters_list', compact('pdfs'));

        $Data = DefaultersList::get();
        $pdf = PDF::loadView('backend.Fees-module.Defaulters_list_pdf', ['teachersubjects' => $teachersubjects])->setOptions(['defaultFont' => 'sans-serif'] , compact('teachersubjects'));
        return $pdf->download('defaulters_list.pdf');
    }

    public function export_Pdfdefaulter(){
        
    }

    public function export(Request $request){
        // echo "<pre>";print_r($request->all());exit;

        $tableName = $request->input('table_name');
        $columnNames = $request->input('column_names');
        // print_r($columnNames);exit;
        $data = DB::table('defaulters_lists')
        ->join('student_registration', 'defaulters_lists.student_id', '=', 'student_registration.id')        
        ->select('defaulters_lists.*', 'student_registration.student_name')
        ->get();
        
         // Create a new CSV writer
         $csv = Writer::createFromFileObject(new \SplTempFileObject());

         // Add CSV headers
         $csv->insertOne($columnNames);
 
          // Add CSV data rows
          foreach ($data as $item) {
             $row = [];
             foreach ($columnNames as $column) {
                 // Check if the column contains JSON data
                 if (strpos($column, 'json_') === 0) {
                     $jsonData = json_decode($item->{$column}, true);
                     $row[] = implode(", ", $jsonData);
                 } else {
                     $row[] = $item->{$column};
                 }
             }
             $csv->insertOne($row);
         }
         // echo"<pre>";
         // print_r($csv);
         // exit;
 
         // Set the response headers
         $headers = [
             'Content-Type' => 'text/csv',
             'Content-Disposition' => 'attachment; filename='.$tableName.'.csv',
         ];
 
         // Return the CSV file as a response
         return response($csv->getContent(), 200, $headers);
    }
    
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
    
        // Perform your search logic here
        $searchResults = DefaultersList::where('column_name', 'like', "%$keyword%")->get();
    
        return response()->json($searchResults);
    }

    public function create(Request $request){  
        $pdf = $request->pdf;
        $csv = $request->csv;
        $scholar_no = $request->scholar_no;
        $enrollment_no = $request->enrollment_no;
        $student_name = $request->student_name;
        $class_name = $request->class_name;
        $section_name = $request->section_name;
        $account_name = $request->account_name;
        $balance_amount = $request->balance_amount;
        $min_date = $request->min_date;
        $max_date = $request->remark;
        $student = $request->student;
        $year = $request->year;
        $date_type = (!empty($request->date_type) ? $request->date_type : '');
        $date = (!empty($request->date) ? $request->date : '');
        $status = $request->status;
        $ac_head_name = (!empty($request->ac_head_name) ? $request->ac_head_name : '');
        $next_yesr_fees = $request->next_yesr_fees;
        $rte = $request->rte;
        $staff_ward = $request->staff_ward;
        $session_name = $request->session_name;
        $scholarship = $request->scholarship;
        $student_id = !empty($request->student_id) ? $request->student_id: '';
        $studentname = Student_registration::where('id',$student_id)->get('student_name');
        $studentclasses = Student_registration::select('class_name')->distinct()->get();
        $headss = Course_fees_head_master::select('ac_head_name')->distinct()->get();
        $student_ids = DB::table('generate_duechartstatus')->join('student_registration', 'generate_duechartstatus.student_id', '=', 'student_registration.id')->select('generate_duechartstatus.student_id','student_registration.student_name',DB::raw("JSON_UNQUOTE(JSON_EXTRACT(student_registration.json_str, '$.father_mobile')) as father_mobile"),DB::raw("JSON_UNQUOTE(JSON_EXTRACT(student_registration.json_str, '$.mobile_number')) as mobile_number"))->get();
        // $student_id = Generate_duechartstatus::select('student_id', 'student_name')->distinct()->get();

        $studentsession = Student_registration::select('session_name')->distinct()->get();
        $subjects = Subject::select('id','subject_name')->distinct('subject_name')->get();
        $session = Student_registration::select('id','session_name')->distinct('session_name')->get();
        $teachersession = Student_registration::all();
        // $teachersubjects = DefaultersList::all();
        // $results = DB::table('generate_duechartstatus')->select('json_str')
        // ->leftJoin('feesreceiptchallan', 'generate_duechartstatus.student_id', '=', 'feesreceiptchallan.student_id')
        // ->get();
                // sending the form data 
                $formdata[] = [
                    'scholar_no' => $scholar_no,
                    'enrollment_no' => $enrollment_no,
                    'class_name' => $class_name,
                    'section_name' => $section_name,
                    'account_name' => $account_name,
                    'balance_amount' => $balance_amount,
                    'min_date' => $min_date,
                    'max_date' => $max_date,
                    'student' => $student,
                    'year' => $year,
                    'date_type' => $date_type,
                    'date' => $date,
                    'status' => $status,
                    'ac_head_name' => $ac_head_name,
                    'next_yesr_fees' => $next_yesr_fees,
                    'rte' => $rte,
                    'staff_ward' => $staff_ward,
                    'session_name' => $session_name,
                    'scholarship' => $scholarship,
                    'student_id' => $student_id
                    
                ];
                // echo "<pre>";print_r($formdata);exit;
                $currentDate = date('d-m-Y');
                // $currentDate = now()->toDateString(); // Get the current date

                $results = DB::connection('dynamic')->table('generate_duechartstatus')->select('generate_duechartstatus.id as gid','feesreceiptchallan.id as fid','generate_duechartstatus.student_id','generate_duechartstatus.class_name as class_name','generate_duechartstatus.session_name','generate_duechartstatus.sectionname','generate_duechartstatus.json_str','feesreceiptchallan.str_json',
        DB::connection('dynamic')->raw('JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(json_str, "$[0].json_str")), "$.due_date[0]")) as due_date'))->leftJoin('feesreceiptchallan', 'generate_duechartstatus.student_id', '=', 'feesreceiptchallan.student_id')->when($student_id, function ($query) use ($student_id) {return $query->where('generate_duechartstatus.student_id', $student_id);})->where('generate_duechartstatus.class_name', $class_name)->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(JSON_UNQUOTE(JSON_EXTRACT(json_str, "$[0].json_str")), "$.due_date[0]")) < ?', [$currentDate])->get();

        $termHeadMap = [];
        // echo '<pre>';
        
            // print_r($results);exit;
        foreach ($results as $object) {
            if (!empty($object->fid)) {
                $jsonData = json_decode($object->str_json, true);
                $records[] = $jsonData;

                $heads = explode(',', $jsonData['head_name']);
                $termStr = explode(',', $jsonData['term_str']);
                $recAmounts = explode(',', $jsonData['head_rec_ammount']);
                $headDueAmounts = explode(',', $jsonData['head_due_amount']);
                $headduedate = explode(',', $jsonData['head_due_date']);
                $headtodate = explode(',', $jsonData['head_to_date']);

                $diff = 0;
                foreach ($termStr as $key => $term) {
                    $head = $heads[$key];
                    $recAmount = $recAmounts[$key];
                    $headDueAmount = $headDueAmounts[$key];
                    $headduedateq = $headduedate[$key];
                    $headtodateq = $headtodate[$key];
                    $student_id = $object->student_id;
                    if($date_type == 'DUE DATE'){
                        if($headduedateq > $date){
                            continue;
                        }
                    }
                    if($date_type == 'ENTRY DARE'){
                        if($headtodateq > $date){
                            continue;
                        }
                    }
                    if(!empty($ac_head_name) && $ac_head_name != $head){
                        continue;
                    }

                    // Initialize an empty array for this term if it doesn't exist
                    if (!isset($termHeadMap[$term])) {
                        $termHeadMap[$term][$student_id][$head] = [];
                    }
                    $studentname2 = Student_registration::where('id',$student_id)->get('class_name');
                    // Create a unique identifier combining head_name and student_id
                    $uniqueIdentifier = "$head - student_id: $student_id - class_name : $object->class_name - session_name : $object->session_name - duedate : $headduedateq - todate : $headtodateq - flag: p - sectionname: $object->sectionname";

                    if ($recAmount != 0) {
                        // Include "pay" and "Due" if recAmount is not 0
                        $uniqueIdentifier .= " - pay: $recAmount - due: $headDueAmount";
                        
                        // Calculate the "Diff" value by subtracting "pay" from "Due"
                        $payAmount = (int)$recAmount;
                        $dueAmount = (int)$headDueAmount;
                        $diff = $dueAmount - $payAmount;
                        $uniqueIdentifier .= " - diff: $diff";
                    } elseif (!empty($headDueAmount)) {
                        // Include only "Due" if headDueAmount is not empty
                        $uniqueIdentifier .= " - due: $headDueAmount";
                    }

                    // Add the unique identifier to the term array if it's not already there
                    if (!in_array($uniqueIdentifier, $termHeadMap[$term])) {
                        $termHeadMap[$term][$student_id][$head] = $uniqueIdentifier;
                    }
                }
            }
        }

        $result1 = [];

        foreach ($results as $object) {
            if (!empty($object->gid) && empty($object->str_json)) {
                
                // Iterate through the JSON objects
                foreach (json_decode($object->json_str, true) as $jsonData1) {
                    // $jsonData1 = json_decode($object->json_str, 1);
                    $jsonData2 = json_decode($jsonData1['json_str'], 1);
                    if (isset($jsonData2['term']) && isset($jsonData2['account_name']) && isset($jsonData2['due_date'])) {
                        $terms = $jsonData2['term'];
                        $accountNames = $jsonData2['account_name'];
                        $dueDates = $jsonData2['due_date'];
                        
                        foreach ($terms as $index => $term) {
                            $accountName = $accountNames[$index];
                            $dueDate = $dueDates[$index];

                            if($date_type == 'DUE DATE'){
                                if($dueDate > $date){
                                    continue;
                                }
                            }
                            if($date_type == 'ENTRY DARE'){
                                if($jsonData2['fees_date'][$index] > $date){
                                    continue;
                                }
                            }
                            if(!empty($ac_head_name) && $ac_head_name != $accountName){
                                continue;
                            }

                            $entry = "$accountName - student_id: {$object->student_id} - class_name: {$object->class_name} - session_name: {$object->session_name} - duedate: $dueDate - todate : {$jsonData2['fees_date'][$index]} - flag: u - sectionname: $object->sectionname - pay:  - due: {$jsonData2['fees'][$index]} - diff: {$jsonData2['fees'][$index]}";

                            if (!isset($result1[$term])) {
                                $result1[$term] = [];
                            }

                            if (!isset($result1[$term][$object->student_id])) {
                                $result1[$term][$object->student_id] = [];
                            }

                            // Add an entry to the result array
                            $result1[$term][$object->student_id][$accountName] = $entry;
                            // echo '<pre>';print_r($result1);
                        }
                    }
                }
            }
        }
        // echo '<pre>';print_r($result1);
        // echo '<pre>';print_r($result1);
        // echo '<pre>';print_r($termHeadMap);
        // die();
        if(!empty($result1)) {
            $result_data = array_merge_recursive($termHeadMap, $result1);
        } else {
            $result_data = $termHeadMap;
        }

        // die();
        // Re-index the arrays
        foreach ($result_data as &$headNames) {
            $headNames = array_values($headNames);
        }

        $result = [];

        foreach ($result_data as $term => $termArray) {
            foreach ($termArray as $studentArray) {
                foreach ($studentArray as $head => $entry) {
                    // Check if the entry contains "Diff: 0", and skip it
                    if (strpos($entry, 'diff: 0') !== false) {
                        continue;
                    }
                    
                    // Add the entry to the result
                    $result[$term][] = $entry;
                }
            }
        }
        // echo '<pre>';
        // print_r($result);die();
        // DefaultersList::create($request->post());
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        if(!empty($pdf)){
            $pdf = PDF::loadView('backend.Fees-module.Defaulters_list_pdf', compact('classlist','result','formdata','studentclasses', 'studentname','subjects', 'session', 'studentsession','teachersession', 'headss','student_ids'));
            return $pdf->download('defaulters_list.pdf');
        }
        if(!empty($csv)){
                // Create a new CSV Writer
    $csv = Writer::createFromFileObject(new \SplTempFileObject());

    // Add headers to the CSV
    $csv->insertOne(['Section', 'Description', 'Student ID', 'Class Name', 'Session Name', 'Due Date', 'To Date', 'Flag', 'Section Name', 'Pay', 'Due', 'Diff']);        
    // $csv->insertOne(['SN','Class','Sesstion','Student Name','Roll No','Scholar No.','Enrollment No.','Stu Mob.','Father Mob.','Fees']);
    // Iterate through each section in $result
    $fp = fopen('/Applications/XAMPP/xamppfiles/htdocs/lvn-school/test.txt','a');
    fwrite($fp,print_r($result,1)."\n");
    foreach ($result as $section => $data) {
        // Iterate through each row of data in the section
        foreach ($data as $rowData) {
            // Split the row data by "-" to get individual fields
            $fields = explode(" - ", $rowData);


            $outputArray = [];
            foreach ($fields as $item) {
                $parts = explode(":", $item, 2);
                if (count($parts) === 2) {
                    $outputArray[] = trim($parts[1]);
                } else {
                    $outputArray[] = $item;
                }
            }
            // print_r($outputArray);

            
            // print_r($fields);
            // die();
            // Extract values from the fields
            $description = substr($outputArray[0], 0, strpos($outputArray[0], ':'));
            $studentInfo = substr($outputArray[0], strpos($outputArray[0], ':') + 2);

            // Split student info by " - " to get individual student-related fields
            $studentFields = explode(" - ", $studentInfo);

            // Add the section as the first field
            array_unshift($outputArray, $section);

            // Insert the row into the CSV
            $csv->insertOne($outputArray);
        }
    }

    // Set the headers for the CSV download
    $tableName = 'defaulters_list';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $tableName . '.csv',
    ];

    // Output the CSV to the browser and exit
    $csv->output($tableName . '.csv');
    }
        return view('backend.Fees-module.Defaulters_list', compact('classlist','result','formdata','studentclasses', 'studentname','subjects', 'session', 'studentsession','teachersession', 'headss','student_ids','class_name'));
    }

    public function view($id){
        $defaulters_list = DefaultersList::whereId($id)->get();
        $defaulters = DefaultersList::orderBy('id','desc')->paginate(5);
        return view('backend.Fees-module.Defaulters_list', compact('defaulters_list','defaulters'));
    }


    public function defaulter_list_csv($id){ 
        //echo "<pre>";
        //echo $id= $_GET['id'];
        //print_r($id); die();
         $class_name = $id; 
            $df = json_decode($id); //echo "abd";
            if(!empty($class_name)){ 

                // Create a new CSV Writer
    $csv = Writer::createFromFileObject(new \SplTempFileObject());

    // Add headers to the CSV
    $csv->insertOne(['SN','Class','Section','Student Name','Scholar No.','Enrollment No.','Fees']);        
    // Iterate through each section in $result
    // echo '<pre>';print_r($df);die();
    $outputArray= [];
    $k = 1;

        
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
        $challan_data ="";
       // echo $class_name; die();
        
         if(!empty($class_name)){
            
             if($class_name != '07'){ $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', $class_name)->orderBy('json_str->section_name', 'asc')->orderBy('student_name', 'asc')->get();
         }else{ $classname1 = array("1209","1377","230","466","1174","694","7711");
             $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', $class_name)->orderBy('json_str->section_name', 'asc')->orderBy('student_name', 'asc')->whereIn('id', $classname1)->get();
         }
           
         }else{

            $classname1 = array("Nursery","KG1","KG2","01","02","03","04","05","06","08","09","10","11","12");
            $classname2 = array("466","1377","1209","230","1174","694","7711");
            $student_data = DB::connection('dynamic')->table('student_registration')->whereIn('class_name', $classname1)->OrwhereIn('id', $classname2)->orderBy('class_name', 'asc')->orderBy('json_str->section_name', 'asc')->orderBy('student_name', 'asc')->get();
            // $classname2 = array("1209","1377","230","466","1174","694");
            //  $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', 07)->orderBy('json_str->section_name', 'asc')->orderBy('student_name', 'asc')->whereIn('id', $classname2)->get();
            
         } 
       
        
        
                $j=1;            
            foreach($student_data as $student_data1){ 
                $data2 = json_decode($student_data1->json_str, true); 
              //echo $student_data1->id;
                $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_data1->id)->where('due_upto', '05-05-2024')->get();
                //prin  
                //var_dump($challan_data);
                

                //echo count($challan_data);
                

                   
            $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                          $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no',$student_data1->scholar_no)->get();
                $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id')->get();
                $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                         if(!empty($totalnextyear)) {
                          // print_r($totalnextyear);
                          $i = 1;
                          
                          $previousFeesDate = null; // Initialize variables to track previous values
                          $previousCreatedAt = null;
                          $previousTotalNextYear = null;
                          $mergedAccountNames = []; // Initialize an array to store merged account_names
                          
                          foreach ($totalnextyear as $next) {
                              
                              // print_r($next);
                              if($next->account_name == 'CUATION MONEY'){
                                $totalnextyear1 = $next->totalnextyear - $next->fees;
                                $refundable = $next->fees;
                              }
                              // Check if the current fees_date, created_at, and totalnextyear are different from the previous ones
                              if ($next->fees_date != $previousFeesDate || $next->created_at != $previousCreatedAt || $next->totalnextyear != $previousTotalNextYear) {
                                  
                                  $id_n = $i;
                                  $fees_date = $next->fees_date;
                                  $created_at = $next->created_at;
                                  
                                  
                                  // echo '<td>0</td>';
                                  $previousFeesDate = $next->fees_date; // Update previous values
                                  $previousCreatedAt = $next->created_at;
                                  $previousTotalNextYear = $next->totalnextyear;
                          
                                  // Display the merged account_names for the same group
                                  if (!empty($mergedAccountNames)) {
                                      // echo '<td>' . implode(', ', $mergedAccountNames) . '</td>';
                                  } else {
                                      // echo '<td></td>';
                                  }
                          
                                  // Clear the mergedAccountNames array for the next group
                                  $mergedAccountNames = [$next->account_name];
                              } else {
                                  // Append account_name to the mergedAccountNames for the same group
                                  $mergedAccountNames[] = $next->account_name;
                              }
                              $i++;
                          }
                          
        if(count($challan_data) == 0){ 
                    $totalnextyear2 = $totalnextyear1; //echo $refundable; 
                     
        }          
                          
                          // Display the last row outside the loop
                          
                          
                            
                        } 
                        $firstRow = true;  $sno = 1;               
        foreach ($challan_data as $object) {
            if ($firstRow) {
                            $firstRow = false; // Set the flag to false after processing the first row
                            continue; // Skip the first row
                            echo "next";
            }


            $data = json_decode($object->str_json, true); 
            $term_str = explode(',', $data['term_str']);
            $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
                          $head_rec_ammount1 = explode(',', $data['head_rec_ammount']);
                          
                          $rec_ammount = array_sum($head_rec_ammount1);
                          $by_cash = $data['by_cash'];
                          $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                          $payment_by_select = $data['payment_by_select'];
                          $term_str = explode(',', $data['term_str']);


                          // // Create an array to hold remarks for each fee item
                          $remarks = [];
                          for ($i = 0; $i < count($head_name); $i++) {
                              $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                          }

                          

                          $by_cash_ += $by_cash;
                          $sno++;

                          
                          if(!empty($rec_ammount)){ 
                             $total_dueamount_ += $rec_ammount;
                           }else{
                            $total_dueamount_ += $total_dueamount;
                          }
                          $sno++;
                          $a1 = $total_dueamount_ + $totalnextyear1;
                          $b2 =$by_cash_ - $a1;

                          

                  


            //echo "<td> ".$data['discount']['lumpsum_fees']."</td>";
            if(!empty($data['discount']['lumpsum_fees'])){

               //echo "<td style='background-color: #bce7bc;'> Not Defaulter</td></tr>";
            }
            else{  
                           
               if(!empty($data['payment_date']) and $data['payment_date'] <= '2024-05-30'){   
                   if($b2 > 0){ 
               // $outputArray2[] = [
               //                      'SN' => 1,
               //                      'Class' => $student_data1->class_name,
               //                      'Sesstion' => $student_data1->scholar_no,
               //                      'Student_Name' => $student_data1->student_name,
               //                      'Roll_No' => '',
               //                      'Scholar_No' => $student_data1->scholar_no,
               //                      'Enrollment_No' => '',
               //                      'Stu_Mob' => '',
               //                      'Father_Mob' => '',
               //                      'Fees' => $b2,
               //                  ]; 
                $studentFees2=
                                            $outputArray['SN'] = $k;
                                $outputArray["Class"] = $student_data1->class_name;
                                $outputArray["Section"] = $data2['section_name'];
                                $outputArray["Student_Name"] = $student_data1->student_name;
                                // $outputArray["Roll_No"] = '';
                                $outputArray["Scholar_No"] = $student_data1->scholar_no;
                                $outputArray["Enrollment_No"] = '';
                                // $outputArray["Stu_Mob"] = '';
                                // $outputArray["Father_Mob"] = '';
                                // $outputArray["Fees"] = $b2; 
                                $csv->insertOne($outputArray);
        $k++;
                
                    $j++;

                 }      
                }else{
                    if($b2 > 0){ 
                    $outputArray['SN'] = $k;
                                $outputArray["Class"] = $student_data1->class_name;
                                $outputArray["Section"] = $data2['section_name'];
                                $outputArray["Student_Name"] = $student_data1->student_name;
                                // $outputArray["Roll_No"] = '';
                                $outputArray["Scholar_No"] = $student_data1->scholar_no;
                                $outputArray["Enrollment_No"] = '';
                                // $outputArray["Stu_Mob"] = '';
                                // $outputArray["Father_Mob"] = '';
                                $outputArray["Fees"] = $b2; 
                                $csv->insertOne($outputArray);
        $k++;
                
                          $j++;
                }  
                }  
            
            $a1=""; $b2="";
               
        } 
    }
                     
                        
       }  
    //echo "<pre>";print_r($outputArray2);echo "<pre>"; die;   
       
    

    // Set the headers for the CSV download
    $tableName = 'defaulters_list';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=' . $tableName . '.csv',
    ];

    // Output the CSV to the browser and exit
    $csv->output($tableName . '.csv');
        }
        exit;
        
        }



    public function defaulter_list_csv2($id){
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
                $db_name = "2024_2025";
            }
            
        }
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
        $challan_data ="";
        $challan_data ="";
        $df = json_decode($id);
        if(!empty($df)){

            // Create a new CSV Writer
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // Add headers to the CSV
        $csv->insertOne(['SN','Class','Sesstion','Student Name','Roll No','Scholar No.','Enrollment No.','Stu Mob.','Father Mob.','Fees']);        
        // Iterate through each section in $result
        // echo '<pre>';print_r($df);die();
        $outputArray= [];
        $k = 1;

             
            $classname1 = array("Nursery","KG1","KG2","01","03","04","06","08","09","10","11","12");
            $student_data = DB::connection('dynamic')->table('student_registration')->whereIn('class_name', $classname1)->orderBy('class_name', 'asc')->get();   
             $j=1;            
            foreach($student_data as $student_data1){ 
              //echo $student_data1->id;
                $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_data1->id)->get();

            $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                          $totalnextyear = DB::connection('dynamic')->table('totalnextyear')->where('scholar_no',$student_data1->scholar_no)->get();
                $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id')->get();
                $total_dueamount_ = 0; $by_cash_ = 0;
                        
                          $totalnextyear1 = 0;
                          $refundable = 0;
                         if(!empty($totalnextyear)) {
                          // print_r($totalnextyear);
                          $i = 1;
                          
                          $previousFeesDate = null; // Initialize variables to track previous values
                          $previousCreatedAt = null;
                          $previousTotalNextYear = null;
                          $mergedAccountNames = []; // Initialize an array to store merged account_names
                          
                          foreach ($totalnextyear as $next) {
                              
                              // print_r($next);
                              if($next->account_name == 'CUATION MONEY'){
                                $totalnextyear1 = $next->totalnextyear - $next->fees;
                                $refundable = $next->fees;
                              }
                              // Check if the current fees_date, created_at, and totalnextyear are different from the previous ones
                              if ($next->fees_date != $previousFeesDate || $next->created_at != $previousCreatedAt || $next->totalnextyear != $previousTotalNextYear) {
                                  
                                  $id_n = $i;
                                  $fees_date = $next->fees_date;
                                  $created_at = $next->created_at;
                                  
                                  
                                  // echo '<td>0</td>';
                                  $previousFeesDate = $next->fees_date; // Update previous values
                                  $previousCreatedAt = $next->created_at;
                                  $previousTotalNextYear = $next->totalnextyear;
                          
                                  // Display the merged account_names for the same group
                                  if (!empty($mergedAccountNames)) {
                                      // echo '<td>' . implode(', ', $mergedAccountNames) . '</td>';
                                  } else {
                                      // echo '<td></td>';
                                  }
                          
                                  // Clear the mergedAccountNames array for the next 
                                  $mergedAccountNames = [$next->account_name];
                              } else {
                                  // Append account_name to the mergedAccountNames for the same group
                                  $mergedAccountNames[] = $next->account_name;
                              }
                              $i++;
                          }
                          
                if(count($challan_data) == 0){ 
                    // echo "<td></td><td style='background-color: #e5ef36;'>";  
                      $totalnextyear2 = $totalnextyear1; //echo $refundable; 
                     //"</td></tr>";
                }          
                          
                          // Display the last row outside the loop
                          
                          
                            
                        } 
                        $firstRow = true;  $sno = 1;               
        foreach ($challan_data as $object) {
            if ($firstRow) {
                            $firstRow = false; // Set the flag to false after processing the first row
                            continue; // Skip the first row
                            echo "next";
            }

            $data = json_decode($object->str_json, true); 
            $term_str = explode(',', $data['term_str']);
            $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
                          $head_rec_ammount1 = explode(',', $data['head_rec_ammount']);
                          
                          $rec_ammount = array_sum($head_rec_ammount1);
                          $by_cash = $data['by_cash'];
                          $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                          $payment_by_select = $data['payment_by_select'];
                          $term_str = explode(',', $data['term_str']);

                          // // Create an array to hold remarks for each fee item
                          $remarks = [];
                          for ($i = 0; $i < count($head_name); $i++) {
                              $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                          }

                          

                          $by_cash_ += $by_cash;
                          $sno++;

                          
                          if(!empty($rec_ammount)){ 
                             $total_dueamount_ += $rec_ammount;
                           }else{
                            $total_dueamount_ += $total_dueamount;
                          }
                          $sno++;
                          $a1 = $total_dueamount_ + $totalnextyear1;
                          $b2 =$by_cash_ - $a1;

            //echo "<td> ".$data['discount']['lumpsum_fees']."</td>";
            if(!empty($data['discount']['lumpsum_fees'])){

               //echo "<td style='background-color: #bce7bc;'> Not Defaulter</td></tr>";
            }
            else{  
                 $newfee =  $b2;
                 //$outputArray = [   
                $outputArray["class"] = $student_data1->class_name;
                $outputArray["id"] = $k;
                                $outputArray["Session"] = 01;
                                $outputArray["Student_Name"] = $student_data1->student_name;
                                $outputArray["Scholar_No"] = $student_data1->scholar_no;
                                $outputArray["Enrollment_No"] = "";
                                $outputArray["Stu_Mob"] = "";
                                $outputArray["Father_Mob"] = "";
                                $outputArray["Fees"] = $newfee;
                           // ];
             
                    
                }    
            //}
              //foreach ($term_str as $term_str1) { if( $term_str1 == "2nd"){ }else { echo "Defaulter"; break;} }
           // }
            $a1=""; $b2="";
              
        } 
$outputArray5 =array_unique($outputArray);
//print_r($outputArray);
    $csv->insertOne($outputArray5);
    $k++;
}

// Set the headers for the CSV download
$tableName = 'defaulters_list';
$headers = [
    'Content-Type' => 'text/csv',
    'Content-Disposition' => 'attachment; filename=' . $tableName . '.csv',
];

// Output the CSV to the browser and exit
$csv->output($tableName . '.csv');
    }
    exit;
    
    }


  public function search_defaulters_list(Request $request){ 
        $classname = ""; 
     $classname = $request->class_name; 
      $dlist = array();
      $studentsession = Student_registration::select('session_name')->distinct()->get();
        $subjects = Subject::select('id','subject_name')->distinct('subject_name')->get();
        $session = Student_registration::select('id','session_name')->distinct('session_name')->get();
        $teachersession = Student_registration::all();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        $studentclasses = DB::table('classes')->select('class_name')->distinct()->get();

        $headss = Course_fees_head_master::select('ac_head_name')->distinct()->get();
        

        $student_ids = DB::table('generate_duechartstatus')
        ->join('student_registration', 'generate_duechartstatus.student_id', '=', 'student_registration.id')
        ->select('generate_duechartstatus.student_id', 'student_registration.student_name')
        ->get();


                   

 
     return view('backend.Fees-module.Defaulters_listnew',compact('studentclasses', 'classlist', 'subjects', 'session', 'studentsession','teachersession', 'headss','student_ids','classname'));
  }

}