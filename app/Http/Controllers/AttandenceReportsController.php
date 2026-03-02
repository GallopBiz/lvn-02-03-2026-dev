<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student_registration;
use Carbon\Carbon;
use App\Models\dailyattandence;
use DB;

class AttandenceReportsController extends Controller
{
    public function index(){
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $AdaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        // filter class option 
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        return view('backend.AcademicsModules.Attandencereports',compact('classlist','AdaysInMonth','uniqueNames'));
    }

    public function classattandence(Request $request){
    //  echo"<pre>";
    //  print_r($request->all());
     
        $AMonth=$request->Month;
        $class=$request->classname;
        $date = Carbon::createFromFormat('Y-m', $request->Month);
        $year = $date->year;
        $month = $date->month;
        $AdaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Convert the $AMonth to the first day of the month for the comparison
        $startDate = Carbon::createFromFormat('Y-m', $AMonth)->startOfMonth();
        // print_r($startDate);
        // Calculate the last day of the month
        $endDate = $startDate->copy()->endOfMonth();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // print_r($endDate);
        $monthWiseAttend = dailyattandence::where('create_date', '>=', $startDate)
                                ->where('create_date', '<=', $endDate)
                                ->where('class_name', $class)
                                ->get();

                                $alreadyShown = Student_registration::pluck('class_name')->toArray();
                                $uniqueNames = array_unique($alreadyShown);
                                $decodedDataArray = [];

                                foreach ($monthWiseAttend as $item) {
                                    $decodedData = json_decode($item['json_str'], true);
                                    $decodedData['Attandence_Name'] = $item['Attandence_Name']; // Add this line
                                    $decodedDataArray[] = $decodedData;
                                }
                                $classwiseAttendance = Student_registration::where('class_name', $class)->get();
                                $sundays = [];

                                while ($startDate->lte($endDate)) {
                                    if ($startDate->dayOfWeek === Carbon::SUNDAY) {
                                        $sundays[] = $startDate->format('Y-m-d');
                                    }
                                    $startDate->addDay();
                                }
        return view('backend.AcademicsModules.Attandencereports',compact('classlist','AdaysInMonth','uniqueNames','decodedDataArray','classwiseAttendance','monthWiseAttend','sundays'));
        exit;
    }
    public function classstrenght(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        // exit;
        $CMonth=$request->value;
        // print_r($CMonth);
      // Separate the year and month
        list($year, $month) = explode('-', $CMonth);
        
        // echo "Year: $year, Month: $month"; // Display separated year and month
        
       
        // $date = Carbon::createFromFormat('Y-m', $request->CMonth);
        
        // $year = $date->year;
        // $month = $date->month;
        $AdaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        // Convert the $AMonth to the first day of the month for the comparison
        $startDate = Carbon::createFromFormat('Y-m', $CMonth)->startOfMonth();
        // print_r($startDate);exit;
        // Calculate the last day of the month
        $endDate = $startDate->copy()->endOfMonth();
        $monthWiseAttend = dailyattandence::where('create_date', '>=', $startDate)
        ->where('create_date', '<=', $endDate)
        ->get();
        // filter class option 
        // print_r($monthWiseAttend);exit;
        $sundays = [];

                                while ($startDate->lte($endDate)) {
                                    if ($startDate->dayOfWeek === Carbon::SUNDAY) {
                                        $sundays[] = $startDate->format('Y-m-d');
                                    }
                                    $startDate->addDay();
                                }
                                $sundayDays = array_map(function ($date) {
                                    return date('j', strtotime($date));
                                }, $sundays);
            
        $alreadyShown = Student_registration::pluck('class_name')->toArray();
        $uniqueNames = array_unique($alreadyShown);
        $classes = array_values($uniqueNames);
        $classOrder = [
            'Nursery', 'KG1', 'KG2', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', 'xyz'
        ];
        
        $sortedClasses = collect($classes)->sort(function ($a, $b) use ($classOrder) {
            return array_search($a, $classOrder) - array_search($b, $classOrder);
        })->values()->all();
        
        $decodedDataArray = [];

        foreach ($monthWiseAttend as $item) {
            $decodedData = json_decode($item['json_str'], true);
            $decodedDataArray[] = $decodedData;
        }  
        $totalStudentCounts = [];


        $totalStudentCounts = [];
//   this  code is to count the total no. of the student in the class 
        foreach ($monthWiseAttend as $item) {
            $countP = 0;
            $countA = 0;
            $countE = 0;
            $students = json_decode($item->json_str, true);

            foreach ($students as $student) {
                $value = $student['value'];
                
                if ($value == 'p') {
                    $countP++;
                }
                
                if ($value == 'A' || $value == 'E') {
                    $countA++;
                }

                if ($value == 'E') {
                    $countE++;
                }
            }

            $data['dateclass'] = $item->Attandence_Name;

            $data['studentCount'] = count($decodedData);
            
            // Get the class for the current item
            $data['class'] = $item->class_name;
            $data['section'] = $item->section_name;
           $finaldata[]= [
            'dateclass'=> $data['dateclass'],
            'studentCount'=> $data['studentCount'],
            'class'=> $data['class'],
            'section'=> $data['section'],
            'countP' => $countP,
            'countA' => $countA,
            'countE' => $countE,
           ];
        }
        $final_data_student =   [
                                    'classes' => $sortedClasses,
                                    'days_in_month' => $AdaysInMonth,
                                    'classwise' => $finaldata,
                                    'sundaydays' => $sundayDays
                                    
                                ];

        // print_r($final_data_student);
        return json_encode($final_data_student,1);
        
    }
    public function classstrenghtstatuswise(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        $startdate=$request->value[0];
        $enddate=$request->value[1];
        $class=$request->value[2];
        $section=$request->value[3];
        $status=$request->value[4];
       
        $monthWiseAttend = dailyattandence::where('create_date', '>=', $startdate)
        ->where('create_date', '<=', $enddate)->where('section_name',$section)->where('class_name',$class)
        ->get();
        
        // print_r($monthWiseAttend);
        // exit;
        // filter class option 
        return json_encode($monthWiseAttend,1); 
    }
}
