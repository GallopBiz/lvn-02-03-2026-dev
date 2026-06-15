<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Models\CourseFeesStructureMasterModel;
use App\Models\Course_fees_head_master;
use App\Models\Holds_structure_row;
use App\Models\Generate_duechartstatus;
use App\Models\Student_registration;
use App\Http\Controllers\Controller;
use App\Models\CommanModel;
use App\Models\Terms;
use DB;
use Illuminate\Support\Facades\Config;

class CourseFeesStructureMaster extends Controller
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

    public function create_course_fees_structure_master(){

        $course_fees_head_master = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get(); //Course_fees_head_master::where('is_delete',0)->get();
        $class_names = DB::connection('dynamic')->table('class_name')->select('class_name')->distinct()->get();
        $holds_structure_row = Holds_structure_row::all();
        $fees_types = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $terms = Terms::where('is_delete',0)->get();
      
        $courseFeesStructureArr = CourseFeesStructureMasterModel::where('is_delete',0)->get();

        if(!empty($courseFeesStructureArr[0]['json_str'])){
            $json = $courseFeesStructureArr[0]['json_str'];
            $arr = json_decode($json);
            // print_r($arr->fees_date);
            for ($i=0; $i < count($arr->fees_date); $i++){
                $courseFeesStructure[] = [
                        'fees_date' => $arr->fees_date[$i],
                        'account_name' => $arr->account_name[$i],
                        'fees' => $arr->fees[$i],
                        'due_date' => $arr->due_date[$i],
                        'term' => $arr->term[$i],
                    ];
            }
        } else {
            $courseFeesStructure = [];
        }
        
        // print_r($courseFeesStructure);
        // die();
        return view('backend.cfs_master.index', compact('courseFeesStructure','holds_structure_row','course_fees_head_master','class_names','fees_types','terms'));
    }

    public function course_fees_structure_master_list(){        
        $cfs_list = DB::connection('dynamic')->table('course_fees_structure_master')->where('is_delete',0)->get();//CourseFeesStructureMasterModel::where('is_delete',0)->get();
        // echo '<pre>';
        // print_r($cfs_list);
        // die();
        return view('backend.cfs_master.course_fees_structure_master_list', compact('cfs_list'));
    }

    public function save_course_fees_structure_master(Request $request){

        $json_str = [
            'fees_date'=>$request->fees_date,
            'account_name'=>$request->account_name, 
            'fees'=>$request->fees,
            'due_date'=>$request->due_date,
            'term'=>$request->term,
        ];
        $json_data = json_encode($json_str);
        $insertArr = [
            'class_name'=>$request->class_name,
            'session_name'=>$request->session_name,
            'fees_type_name'=>$request->fees_type_name,
            // 'cast_category'=>$request->cast_category,
            'batch'=>$request->batch,
            'json_str'=>$json_data,
            'total_above_fees'=>$request->total_above_fees,
        ];

        $cfs_list = DB::connection('dynamic')->table('course_fees_structure_master')->insert($insertArr);//CourseFeesStructureMasterModel::Create($insertArr);

        DB::connection('dynamic')->table('holds_structure_row')->truncate();
        //Holds_structure_row::truncate();
        return redirect()->back()->with('success','Fees Structure created successfully')->with('sl_classname', $request->class_name)->with('sl_sessionname', $request->session_name)->with('sl_feetypename', $request->fees_type_name)->with('sl_batch', $request->batch);

    }



    public function create(Request $request){
        CourseFeesStructureMasterModel::create($request->post());
        return redirect()->route('bus-stop')->with('success','Bus Stop has been created successfully.');
    }

    public function view($id){
        $bus_stops = CourseFeesStructureMasterModel::whereId($id)->get();
        $busstops = CourseFeesStructureMasterModel::orderBy('id','desc')->paginate(5);
        return view('backend.bus_stop_index',compact('bus_stops','busstops'));
    }

    public function store(Request $request){
        $data = [
            'area_name' => $request->area_name,
            'bus_stop_name' => $request->bus_stop_name,
            'latitude' => $request->latitude,
            'langitude' => $request->langitude,
        ];
        Course_fees_structure_master::whereId($request->id)->update($data);
        return redirect()->route('bus-stop')->with('success','Bus Stop has been Updated successfully.');
    }
    public function  busstop_delete(Request $request)
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
        $feestype = Course_fees_structure_master::findOrFail($id);
        $feestype->delete();
        return redirect()->route('bus-stop')->with('success','Bus Stop has been Deleted successfully.');
    }

    public function get_previous_structure(Request $request){

        $session_name = $request->session_name;
         $class_name = $request->class_name;
        //  DB::table('Course_fees_structure_master')->truncate();
        $courseFeesStructureArr = CourseFeesStructureMasterModel::where('session_name',"=", $session_name)->where('class_name',"=", $class_name)->get();

        $json = $courseFeesStructureArr[0]['json_str'];

        //  print_r($courseFeesStructureArr);
    

        $arr = json_decode($json);
         //print_r($arr->fees_date);
        for ($i=0; $i < count($arr->fees_date); $i++){
            $courseFeesStructure[] = [
                    'fees_date' => $arr->fees_date[$i],
                    'account_name' => $arr->account_name[$i],
                    'fees' => $arr->fees[$i],
                    'due_date' => $arr->due_date[$i],
                    'term' => $arr->term[$i],
                ];
        }
        // print_r($courseFeesStructure);
        // die();

        if(sizeof($courseFeesStructure)>0){

            Holds_structure_row::truncate();
            foreach ($courseFeesStructure as $data) {
                holds_structure_row::create([
                    'fees_date' => $data['fees_date'],
                    'account_name' => $data['account_name'],
                    'fees' => $data['fees'],
                    'due_date' => $data['due_date'],
                    'term' => $data['term'],
                ]);
        }

            return json_encode($courseFeesStructure);
        }else{
            return "notfound";
        }
    }



    public function total_structure_row(){
        $totals = Holds_structure_row::get();
        $num = 0;
        if(!empty($totals)){
            foreach($totals as $total){
                $num += $total['fees'];
            }
            return json_encode($num);
        } else {
            return json_encode(0);
        }
        
    }

    public function save_structure_row(Request $request){
        $iteration = 0;
        $insertArr = [];

        foreach ($request->data1 as $data) {
            if ($iteration < 2) {
                $iteration++; // Increment the iteration counter
                continue; // Skip the current iteration
            }

            $insertArr = [
                'fees_date' => $request->fees_date_str,
                'due_date' => $request->due_date_str,
                'term' => $request->term_str,
                'account_name' => $data['account_name_str'],
                'fees' => $data['fees_str'],
            ];
            Holds_structure_row::Create($insertArr);
        }

        return redirect()->back()->with('success','Row Added.');
    }

    public function save_next_year_fees(Request $request){
        $insertArr = [];
        $res = DB::connection('dynamic')
            ->table('totalnextyear')
            ->select(DB::raw('SUM(fees) as total_fees'))
            ->where('scholar_no', '=', $request->scholar_no)
            ->first();
        $max_num = DB::connection('dynamic')->table('totalnextyear')->max('receipt_number');
        $max = (empty($max_num)) ? 1 : $max_num + 1;    
        $total_amount = DB::connection('dynamic')->table('abvance_nextyear_fees')->where('deleted_at','=','0')->first();
        $allowedAmount = (float) ($total_amount->amount ?? 0);
        $existingAmount = (float) ($res->total_fees ?? 0);

        if ($allowedAmount <= 0) {
            return response()->json(['error' => "Next year fee amount is not configured."]);
        }

        if ($existingAmount < $allowedAmount){
            foreach ($request->data1 as $data) {
                $insertArr = [
                    'fees_date' => $request->fees_date_str,
                    'due_date' => $request->due_date_str,
                    'totalnextyear' => $request->totalnextyear,
                    'account_name' => $data['account_name_str'],
                    'fees' => $data['fees_str'],
                    'scholar_no' => $request->scholar_no,
                    'received_type' => $request->received_amount,
                    'reference_number' => $request->reference_number,
                    'receipt_number' => $max,
                ];
                DB::connection('dynamic')->table('totalnextyear')->insert($insertArr); //Holds_structure_row::Create($insertArr);
            }
    
            // return redirect()->back()->with('success','Row Added.');
            return response()->json([
                'success' => "Row Added",
                'receipt_number' => $max,
            ]);
        } else {
            return response()->json([
                'error' => "Next year fees already submitted for this student.",
                'allowed_amount' => $allowedAmount,
                'existing_amount' => $existingAmount,
            ]);
        }
    }

    public function delete_structure_row(Request $request){


        $delete_id  = $request->delete_id;

        CommanModel::deleteData('holds_structure_row',['id'=>$delete_id]);

        // Holds_structure_row::Create($insertArr);
        return redirect()->back()->with('success','Row Added.');
    }


    public function course_fees_structure_master_delete(Request $request){

        $delete_resp = CommanModel::soft_delete('course_fees_structure_master',['id'=>$request->delete_id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }

    }

    public function generate_due_chart(){
         return view('backend.Fees-module.generate_due_chart');

    }
    public function editview($id){
        $Vehicallist = DB::connection('dynamic')->table('course_fees_structure_master')->where('id',$id)->get();//CourseFeesStructureMasterModel::whereId($id)->get();
        // print_r($Vehicallist);exit;
        $course_fees_head_master = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get();//Course_fees_head_master::where('is_delete',0)->get();
        $class_names = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $holds_structure_row = DB::connection('dynamic')->table('holds_structure_row')->get();//Holds_structure_row::all();
        $fees_types = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $terms = DB::connection('dynamic')->table('terms')->where('is_delete',0)->get();
        // print_r($terms); exit;
        $courseFeesStructureArr = DB::connection('dynamic')->table('course_fees_structure_master')->where('is_delete',0)->get();//CourseFeesStructureMasterModel::where('is_delete',0)->get();
    //    echo"<pre>"; print_r($courseFeesStructureArr[0]->json_str);exit;
        if(!empty($courseFeesStructureArr[0]->json_str)){
            $json = $courseFeesStructureArr[0]->json_str;
            $arr = json_decode($json);
            // print_r($arr->fees_date);
            for ($i=0; $i < count($arr->fees_date); $i++){
                $courseFeesStructure[] = [
                        'fees_date' => $arr->fees_date[$i],
                        'account_name' => $arr->account_name[$i],
                        'fees' => $arr->fees[$i],
                        'due_date' => $arr->due_date[$i],
                        'term' => $arr->term[$i],
                    ];
            }
        } else {
            $courseFeesStructure = [];
        }
        // $Vehicallist = AddVehial::orderBy('id','desc')->paginate(5);
        // $vehicals = AddVehial::where('is_delete',0)->get();

        return view('backend.cfs_master.edit_course_fees_structure_master_list',compact('Vehicallist','courseFeesStructure','holds_structure_row','course_fees_head_master','class_names','fees_types','terms'));
    }
// Assuming PHP as the server-side language
public function deleteRow(Request $request)
{    $sNo = $request->input('sNo');
    $value = $request->input('value');

    // Get the jsonData from the request body
    $jsonData = json_decode($request->input('jsonData'), true);
    // Check if the row index exists and delete it
    if (isset($jsonData['fees_date'][$sNo - 1])) {
        unset($jsonData['fees_date'][$sNo - 1]);
        unset($jsonData['account_name'][$sNo - 1]);
        unset($jsonData['fees'][$sNo - 1]);
        unset($jsonData['due_date'][$sNo - 1]);
        unset($jsonData['term'][$sNo - 1]);

        // Re-index the arrays
        $jsonData['fees_date'] = array_values($jsonData['fees_date']);
        $jsonData['account_name'] = array_values($jsonData['account_name']);
        $jsonData['fees'] = array_values($jsonData['fees']);
        $jsonData['due_date'] = array_values($jsonData['due_date']);
        $jsonData['term'] = array_values($jsonData['term']);
        $newstrudata =json_encode($jsonData);
        CourseFeesStructureMasterModel::whereId($value)->update(['json_str' => $newstrudata]);
        // Encode and save the updated JSON data
        file_put_contents('path_to_your_json_file.json', json_encode($jsonData));


        // Return the updated data in the response
        return response()->json(['updatedData' => $jsonData]);
    }

    // Handle errors if the row index is out of bounds
    return response()->json(['error' => 'Row not found'], 404);
}
    public function studentdeleteRow(Request $request){
        $sno = $request->input('sNo');
        $value = $request->input('value');
        $fulldata = $request->input('fulldata');
        $newdata = $request->input('jsonData');
        // print_r($newdata);exit;
        // Get the jsonData from the request body
    $data = json_decode($request->input('jsonData'), true);
    $newdata = json_decode($data);
    // Count the length of the "fees_date" array
    $feesDateLength = count($newdata->fees_date);

// echo "Length of 'fees_date' array: " . $feesDateLength;
        // print_r($sno);exit;
         $fulldata['json_str'];
        $b = json_decode($fulldata['json_str'],true);
        // unset($b[0]['json_str']);
    //    echo $sno;exit;
        // print_r($sno);exit;
    // Check if the row index exists and delete it
       // Check if the index is valid

// Check if the index is valid
if ($sno >= 0 && $sno <= count($newdata->fees_date)) {
    // Remove the values at the specified index
    unset($newdata->fees_date[$sno-1]);
    unset($newdata->term[$sno-1]);
    unset($newdata->due_date[$sno-1]);
    unset($newdata->account_name[$sno-1]);
    unset($newdata->fees[$sno-1]);

    // Reindex the arrays to remove gaps in the indices
    $newdata->fees_date = array_values($newdata->fees_date);
    $newdata->term = array_values($newdata->term);
    $newdata->due_date = array_values($newdata->due_date);
    $newdata->account_name = array_values($newdata->account_name);
    $newdata->fees = array_values($newdata->fees);
    $b[0]['json_str']= json_encode($newdata);
    // json_encode($b);
    // $fulldata['json_str']=$b;
    // Print the remaining data
    // print_r($b);exit;
    DB::table('generate_duechartstatus')
                ->where('id', $fulldata['id'])
                ->update(['json_str' => $b]);
    // Generate_duechartstatus::where('id', $fulldata['id'])->update(['json_str' => $fulldata['json_str']]);
    // Generate_duechartstatus::whereId($fulldata['id'])->update(['json_str' => ]);
    // echo"done";
    return response()->json(['updatedData' => $newdata]);

} else {
    echo "Invalid index!";
}
        exit;
        return response()->json(['error' => 'Row not found'], 404);
    }

    public function edit_course_fees_structure_master_data(Request $request){
        // print_r($request->all());exit;
        $st_id = $request->stid;
        $classn= $request->class_name;
        $sessinn = $request->session_name;
        $feestypen = $request->fees_type_name;
        $bactst = $request->batch;
        $fees_d = $request->fees_date;
        foreach ($fees_d as $date) {
            $formattedDate = date('d-m-Y', strtotime($date));
            $feesd[] = $formattedDate;
        }
        $Accn = $request->account_name;
        $feesst = $request->fees;
        $due_d = $request->due_date;
        foreach ($due_d as $date) {
            $formattedDate = date('d-m-Y', strtotime($date));
            $dued[] = $formattedDate;
        }
        $termsst = $request->term;
        $json = [
            'fees_date'=>$feesd,
            'account_name'=>$Accn,
            'fees'=>$feesst,
            'due_date'=>$dued,
            'term'=>$termsst
        ];
        // Initialize a variable to store the total
            $total = 0;

            // Loop through the array and sum the elements
            foreach ($feesst as $value) {
                $total += (int)$value; // Convert each element to an integer before adding
            }
                    $totalA = $total;

            $json_data = json_encode($json);
            

            $update_data = [
                'class_name'=>$classn,
                'session_name'=>$sessinn,
                'fees_type_name'=>$feestypen,
                'batch'=>$bactst,
                'json_str'=>$json_data,
                'total_above_fees'=>$total,
            ];
        // print_r($update_data);exit;
        CourseFeesStructureMasterModel::whereId($st_id)->update($update_data);
        // echo"done";exit;
        return redirect('course-fees-structure-master-list')
        ->with('success','Record update successfully');
    }
    public function updatebusfees(Request $request){
        $stuid = $request->hiddenValue;
        $busfeesamount = $request->selectedValue;
        $datagenerat = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id', $stuid)->get();
    
        // Check if there is at least one record
        if (!empty($datagenerat)) {
            // Assuming there is only one record in $datagenerat
            $jsonStr = json_decode($datagenerat[0]->json_str, true);
            // Check if the necessary keys exist
            if (!empty($jsonStr[0]['json_str'])) {
                $innerJson = json_decode($jsonStr[0]['json_str'], true);
            // print_r($datagenerat[0]->id);exit;
                // Extract the first values of fees_date, due_date, and term
                $feesDate = !empty($innerJson['fees_date'][0]) ? $innerJson['fees_date'][0] : null;
                $dueDate = !empty($innerJson['due_date'][0]) ? $innerJson['due_date'][0] : null;
                $term = !empty($innerJson['term'][0]) ? $innerJson['term'][0] : null;
    
                // Append values to the respective arrays
                $innerJson['fees_date'][] = $feesDate;
                $innerJson['due_date'][] = $dueDate;
                $innerJson['term'][] = $term;
    
                // Assuming "BUS FEES" should be added to the last element of "account_name"
                $innerJson['account_name'][] = "BUS FEES";
    
                // Assuming $busfeesamount should be added to the last element of "fees"
                $innerJson['fees'][] = $busfeesamount;
                
                // Directly update total_above_fees with the calculated sum
                $newamount['total_above_fees'] = number_format(array_sum($innerJson['fees']), 2, '.', ''); // Assuming total_above_fees is formatted as a decimal
    
                // Convert the inner JSON array back to a JSON string
                $updatedInnerJsonStr = json_encode($innerJson);
    
                // Update the original JSON string in $jsonStr
                $jsonStr[0]['json_str'] = $updatedInnerJsonStr;
    
                // Update the value outside json_str
                $jsonStr[0]['total_above_fees'] = $newamount['total_above_fees'];
                // Directly update referance_amount with the same calculated sum
                $jsonStr[0]['referance_amount'] = $newamount['total_above_fees'];
    
                // Use $jsonStr as needed
                // ...
                // $finaldata = [$innerJson];

                // Update the json_str and amount columns in the database
                DB::connection('dynamic')->table('generate_duechartstatus')
                    ->where('student_id', $stuid)
                    ->update(['json_str' => $jsonStr, 'amount' => $newamount['total_above_fees']]);
    
                // Output for testing
                // print_r($jsonStr);
                // exit;
            }
        }
    
        // print_r($datagenerat);
        // exit;
    }
                
    public function updateCheckbox(Request $request)
    {
        // print_r($request->all());exit;
        $busValue = $request->isChecked;
        $student_id = $request->value;
        $drivername = $request->driverName;
        $busfeesamount =  $request->busfees;
        $datagenerat = DB::connection('dynamic')->table('generate_duechartstatus')->where('student_id', $student_id)->get();
        $busFacilityDate = date('d-m-Y');
          // Add or update the "required_school_transport" key with the value "1" in the JSON data
          $data = DB::connection('dynamic')->table('student_registration')->select('json_str')->where('id',$student_id)->get();
       // Decode the JSON string to an associative array 
       $new = json_decode($data, true);

       $dataArray = json_decode($new[0]['json_str'], true);
       if($busValue == 0){
        // echo"hyy"; exit;
        $jsonStr = json_decode($datagenerat[0]->json_str, true);
        $removeinnerJson = json_decode($jsonStr[0]['json_str'], true);
        // // Find the index of 'BUS FEES' in the 'account_name' array
        $index = array_search('BUS FEES', $removeinnerJson['account_name']);
        // print_r($innerJson);exit;
        // Check if 'BUS FEES' is found
        if ($index !== false) {
            // Remove the values at the found index in each array
            unset($removeinnerJson['fees_date'][$index]);
            unset($removeinnerJson['account_name'][$index]);
            unset($removeinnerJson['fees'][$index]);
            unset($removeinnerJson['due_date'][$index]);
            unset($removeinnerJson['term'][$index]);

            // Reindex the arrays to fill the gap
            $removeinnerJson['fees_date'] = array_values($removeinnerJson['fees_date']);
            $removeinnerJson['account_name'] = array_values($removeinnerJson['account_name']);
            $removeinnerJson['fees'] = array_values($removeinnerJson['fees']);
            $removeinnerJson['due_date'] = array_values($removeinnerJson['due_date']);
            $removeinnerJson['term'] = array_values($removeinnerJson['term']);
             // Directly update total_above_fees with the calculated sum
             $newamount['total_above_fees'] = number_format(array_sum($removeinnerJson['fees']), 2, '.', ''); // Assuming total_above_fees is formatted as a decimal
            $jsonStr[0]['json_str'] = json_encode($removeinnerJson);
            // Update the value outside json_str
            $jsonStr[0]['total_above_fees'] = $newamount['total_above_fees'];
            // Directly update referance_amount with the same calculated sum
            $jsonStr[0]['referance_amount'] = $newamount['total_above_fees'];
            // Display the updated data
            // print_r($jsonStr);
        }
        // exit;
        DB::connection('dynamic')->table('generate_duechartstatus')
        ->where('student_id', $student_id)
        ->update(['json_str' => $jsonStr]);
        // echo"if";
            // print_r($dataArray);exit;
        // Add or update the "required_school_transport" key with the value "1"
        $dataArray['required_school_transport'] = "";
        $dataArray['driver_name'] = "Select Staff"; // Replace with the actual driver name
        $dataArray['bus_facility_end_date'] = "$busFacilityDate";
        // Encode the array back to JSON
        $updatedJsonData = json_encode($dataArray);
        // print_r($updatedJsonData);exit;
       }else{

        if (!empty($datagenerat)) {
            // echo"hyy";exit;
            // Assuming there is only one record in $datagenerat
            $jsonStr = json_decode($datagenerat[0]->json_str, true);
            // Check if the necessary keys exist
            if (!empty($jsonStr[0]['json_str'])) {
                $innerJson = json_decode($jsonStr[0]['json_str'], true);
            // print_r($datagenerat[0]->id);exit;
                // Extract the first values of fees_date, due_date, and term
                $feesDate = !empty($innerJson['fees_date'][0]) ? $innerJson['fees_date'][0] : null;
                $dueDate = !empty($innerJson['due_date'][0]) ? $innerJson['due_date'][0] : null;
                $term = !empty($innerJson['term'][0]) ? $innerJson['term'][0] : null;
    
                // Append values to the respective arrays
                array_push($innerJson['fees_date'], $feesDate);
                array_push($innerJson['due_date'], $dueDate);
                array_push($innerJson['term'], $term);
    
                // Assuming "BUS FEES" should be added to the last element of "account_name"
                // $innerJson['account_name'][0] = "BUS FEES";
                array_push($innerJson['account_name'], "BUS FEES");
    
                // Assuming $busfeesamount should be added to the last element of "fees"
                array_push($innerJson['fees'], $busfeesamount);

                // $innerJson['fees'][0] = $busfeesamount;
            // print_r($innerJson);exit;
                // return $innerJson;
                
                // Directly update total_above_fees with the calculated sum
                $newamount['total_above_fees'] = number_format(array_sum($innerJson['fees']), 2, '.', ''); // Assuming total_above_fees is formatted as a decimal
    
                // Convert the inner JSON array back to a JSON string
                $updatedInnerJsonStr = json_encode($innerJson);
    
                // Update the original JSON string in $jsonStr
                $jsonStr[0]['json_str'] = $updatedInnerJsonStr;
    
                // Update the value outside json_str
                $jsonStr[0]['total_above_fees'] = $newamount['total_above_fees'];
                // Directly update referance_amount with the same calculated sum
                $jsonStr[0]['referance_amount'] = $newamount['total_above_fees'];

                // Use $jsonStr as needed
                // ...
                // $finaldata = [$innerJson];
                // print_r($jsonStr);exit;                
                // Update the json_str and amount columns in the database
                DB::connection('dynamic')->table('generate_duechartstatus')
                    ->where('student_id', $student_id)
                    ->update(['json_str' => $jsonStr, 'amount' => $newamount['total_above_fees']]);
    
                // Output for testing
            }
        }
        // echo"else";
        //    exit;
        // Add or update the "required_school_transport" key with the value "1"
        $dataArray['required_school_transport'] = "1";
        $dataArray['driver_name'] = "$drivername"; // Replace with the actual driver name
        $dataArray['bus_facility_start_date'] = "$busFacilityDate";

        // Encode the array back to JSON
        $updatedJsonData = json_encode($dataArray);
       }
            // Print the updated JSON data
            // print_r($updatedJsonData);exit;

        // if ($busValue === 'true') {  
        //     print_r("if");
           
        // } else {
        //     print_r("else");
        // }

        
        DB::connection('dynamic')->table('student_registration')->whereId($student_id)->update(['json_str' => $updatedJsonData]);
        return response()->json(['updatedData' => $updatedJsonData]);
    }

    public function edit_student_fees_structure_master_data(Request $request){
        // print_r($request->all());exit;
        $id = $request->id;
        $fullstudata = $request->fulldata;
        $st_id = $request->stid;
        // print_r($st_id);exit;
        $totalfees= $request->totalFees;
        $discountTotalfees = $request->totalDiscountedFees;
        $feesd = $request->fees_date;
        $Accn = $request->account_name;
        $feesst = $request->discounted_fees;
        $orignalFees = $request->fees;
        $dued = $request->due_date;
        $termsst = $request->term;
        $data = [
                   'fees_date'=>$feesd,
                   'account_name'=>$Accn,
                   'fees'=>$feesst,
                   'orig_fees' => $orignalFees,
                   'due_date'=>$dued,
                   'term'=> $termsst
                ];
                $fuldecode = json_decode($fullstudata);
                $jsonStrData = json_decode($fuldecode[0]->json_str);

        $classname = $jsonStrData[0]->class_name;
        $feestype = $jsonStrData[0]->fees_type_name;
        $castC = $jsonStrData[0]->cast_category;
        $batch = $jsonStrData[0]->batch;
        $jid = $jsonStrData[0]->id;
        $section = $fuldecode[0]->sectionname;
        $status = $fuldecode[0]->status;
        $session = $jsonStrData[0]->session_name;
                // echo"<pre>";
                // print_r($data);exit;

        $admission_type = DB::connection('dynamic')->table('student_registration')->select('student_name', 'id', DB::connection('dynamic')->raw('JSON_EXTRACT(json_str, "$.admission_type") = "RTE" as admission_type'))->where('id', '=', $st_id)->first();
        $keyToRemove = 'ADMISSION FEES';

        // $result = Student_registration::where('id',$st_id)->first();
        // $student_arr = json_decode($result['json_str'],1);
        // $student_is_sibling_applied_for_admission = (!empty($student_arr['is_sibling_applied_for_admission']) ? $student_arr['is_sibling_applied_for_admission'] : '0');

        if ($admission_type->admission_type == true) {
                        
            // Find the index of 'ADMISSION FEES' in account_name
            $admissionFeesIndex = array_search('ADMISSION FEES', $data['account_name']);
            // Remove the corresponding values from each array
            if ($admissionFeesIndex !== false) {
                unset($data['fees_date'][$admissionFeesIndex]);
                unset($data['account_name'][$admissionFeesIndex]);
                unset($data['fees'][$admissionFeesIndex]);
                unset($data['due_date'][$admissionFeesIndex]);
                unset($data['term'][$admissionFeesIndex]);
            }

            // Re-index the arrays
            $data['fees_date'] = array_values($data['fees_date']);
            $data['account_name'] = array_values($data['account_name']);
            $data['fees'] = array_values($data['fees']);
            $data['due_date'] = array_values($data['due_date']);
            $data['term'] = array_values($data['term']);

            // Print the modified data
            // print_r($data);
                $total = 0;
                    // print_r($data);exit;
            // Loop through the array and sum the elements
            foreach ($data['fees'] as $value) {
                $total += (int)$value; // Convert each element to an integer before adding
            }
                    $totalA = $total;
                $newDatastu=[
                    'fees_date' => $data['fees_date'],
                    'account_name' => $data['account_name'],
                    'fees'=> $data['fees'],
                    'due_date'=> $data['due_date'],
                    'term'=> $data['term']
                ];
            
            // print_r($newDatastu);exit;
            // $total_above_fees = $total;
            // Update the "total_above_fees" in the JSON data
            // $data['total_above_fees'] = $total;
            // $data['is_delete'] = 0;
            $created_at = $jsonStrData[0]->created_at;
            $updated_at = $jsonStrData[0]->updated_at;
            // $data['created_at'] = $created_at;
            // $data['updated_at'] = $updated_at;

        // echo"<pre>";
                    // print_r($data);exit;
                // Convert the datastu array to JSON
                $newstrudata['json_str'] = json_encode($newDatastu);
                    $json_data_upload = [[
                        'id'=>$jid,
                        // 'student_id'=>$st_id,
                        'class_name'=>$classname,
                        'session_name'=>$session,
                        'fees_type_name'=>$feestype,
                        'cast_category'=>$castC,
                        'batch'=>$batch,
                        'json_str'=>$newstrudata['json_str'],
                        'total_above_fees'=>$discountTotalfees,
                        'referance_amount'=>$total,
                        'is_delete'=>0,
                        'created_at'=>$created_at,
                        'updated_at'=>$updated_at,
                        // 'is_sibling_applied_for_admission'=>$student_is_sibling_applied_for_admission,
                        ]
                ];  

                // print_r($json_data_upload);
                // exit;
                $a = json_encode($json_data_upload);
                DB::connection('dynamic')->table('generate_duechartstatus')
                ->where('student_id', $st_id)
                ->update(['json_str' => $a, 'amount'=>$total]);
                // exit; 

                // echo"query excuate";exit;
                // Update json_str in the $fullstudata
                // $decodedData['json_str'] = json_encode($datastu);

                // print_r($decodedData);

                // print_r($data);
                return redirect('fees-master-student');
            // echo "hai kuch";
        } else {
            // $data['total_above_fees'] = $totalfees;
            // $data['is_delete'] = 0;
            $created_at = $jsonStrData[0]->created_at;
            $updated_at = $jsonStrData[0]->updated_at;
            // $data['created_at'] = $created_at;
            // $data['updated_at'] = $updated_at;
            // print_r($data['is_delete']);exit;
            $newstrudata['json_str'] = json_encode($data);
            $json_data_upload = [[
                'id'=>$jid,
                // 'student_id'=>$st_id,
                'class_name'=>$classname,
                'session_name'=>$session,
                'fees_type_name'=>$feestype,
                'cast_category'=>$castC,
                'batch'=>$batch,
                'json_str'=>$newstrudata['json_str'],
                'total_above_fees'=>$discountTotalfees,
                'referance_amount'=>$totalfees,
                'is_delete'=>0,
                'created_at'=>$created_at,
                'updated_at'=>$updated_at,
                // 'is_sibling_applied_for_admission'=>$student_is_sibling_applied_for_admission,
                ]
            ];
            $a = json_encode($json_data_upload);
            DB::connection('dynamic')->table('generate_duechartstatus')
                ->where('student_id', $st_id)
                ->update(['json_str' => $a,'amount'=>$discountTotalfees]);
            // echo "nhi hai";
            return redirect('fees-master-student');

        }
        
        return redirect('fees-master-student');
        
    }

    public function course_fees_structure_delete($id){
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
}
