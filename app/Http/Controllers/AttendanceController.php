<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use App\Models\Employees;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\CurlJob;
use DB;
use App\Models\Holidays;
use Carbon\Carbon;
class AttendanceController extends Controller
{
    // public function index()
    // {
    //     $employeeName = Employees::where('is_delete', 0)->get();
    //     return view('backend.HRMS.attendance', compact('employeeName'));
    // }

    public function index()
    {
        $employeeName = Employees::where('is_delete', 0)->get();
        return view('backend.HRMS.attendance', compact('employeeName'));
    }

    // public function filterAttendance(Request $request)
    // {
    //     $employeeId = $request->input('EmployeeID');
    //     $monthYear = $request->input('Date');
    
    //     $query = CurlJob::query();
    
    //     if ($employeeId) {
    //         $query->where('user_id', $employeeId);
    //     }
    
    //     if ($monthYear) {
    //         $startDate = $monthYear . '-01'; // Starting date of the month
    //         $endDate = date('Y-m-t', strtotime($startDate)); // Last date of the month
    //         $query->whereBetween('log_date', [$startDate, $endDate]);
    //     }
    
    //     $transactions = $query->get();
    //     $employeeName = Employees::where('is_delete', 0)->get();
    
    //     return view('backend.HRMS.attendance', compact('transactions', 'employeeName'))
    //            ->with('success', 'Data fetched successfully');
    // }
    

    // public function filterAttendance(Request $request)
    // {
    //     // Get input values
    //     $employeeId = $request->input('EmployeeID');
    //     $monthYear = $request->input('Date');
    
    //     // Initialize query on employee_thumb_attendance table
    //     $query = DB::table('employee_thumb_attendance');
    
    //     // Apply filters based on employee ID and month/year
    //     if ($employeeId) {
    //         $query->where('ess_emp_code', $employeeId);
    //     }
    
    //     if ($monthYear) {
    //         $startDate = $monthYear . '-01'; // Start date of the month
    //         $endDate = date('Y-m-t', strtotime($startDate)); // End date of the month
    //         $query->whereBetween('log_date', [$startDate, $endDate]);
    //     }
    
    //     // Get the results
    //     $transactions = $query->get();
    
    //     // Fetch employee names for the dropdown
    //     $employeeName = Employees::where('is_delete', 0)->get();
    
    //     // Return the view with attendance data and employee names
    //     return view('backend.HRMS.attendance', compact('transactions', 'employeeName'))
    //            ->with('success', 'Data fetched successfully');
    // }

    // public function filterAttendance(Request $request)
    // {
    //     $employeeId = $request->input('EmployeeID');
    //     $monthYear = $request->input('Date');
    
    //     // Initialize query on employee_thumb_attendance table
    //     $query = DB::table('employee_thumb_attendance');
    
    //     // Apply filters based on employee ID and month/year
    //     if ($employeeId) {
    //         $query->where('ess_emp_code', $employeeId);
    //     }
    
    //     if ($monthYear) {
    //         $startDate = $monthYear . '-01'; // Start date of the month
    //         $endDate = date('Y-m-t', strtotime($startDate)); // End date of the month
    //         $query->whereBetween('log_date', [$startDate, $endDate]);
    //     }
    
    //     // Get the attendance records
    //     $transactions = $query->get();
    
    //     // Fetch employee names for the dropdown
    //     $employeeName = Employees::where('is_delete', 0)->get();
    
    //     // Fetch holidays
    //     $holidays = Holidays::pluck('HolidayStartDate')->toArray();
    //     $holidays = array_map(function ($date) {
    //         return Carbon::parse($date)->format('Y-m-d');
    //     }, $holidays);
    
    //     // Get the list of dates in the selected month
    //     $dates = [];
    //     $currentDate = Carbon::parse($startDate);
    //     while ($currentDate->lte($endDate)) {
    //         $dates[] = $currentDate->format('Y-m-d');
    //         $currentDate->addDay();
    //     }
    
    //     // Create an array to hold attendance data with the status
    //     $attendanceData = [];
    //     foreach ($dates as $date) {
    //         $attendance = $transactions->firstWhere('log_date', $date);
    //         if ($attendance) {
    //             $attendanceData[$date] = [
    //                 'log_date' => $date,
    //                 'in_time' => $attendance->in_time,
    //                 'out_time' => $attendance->out_time,
    //                 'status' => 'Present'
    //             ];
    //         } else {
    //             if (in_array($date, $holidays)) {
    //                 $attendanceData[$date] = [
    //                     'log_date' => $date,
    //                     'in_time' => null,
    //                     'out_time' => null,
    //                     'status' => 'Holiday'
    //                 ];
    //             } else {
    //                 $dayOfWeek = Carbon::parse($date)->dayOfWeek;
    //                 if ($dayOfWeek === Carbon::SUNDAY) {
    //                     $attendanceData[$date] = [
    //                         'log_date' => $date,
    //                         'in_time' => null,
    //                         'out_time' => null,
    //                         'status' => 'Week Off'
    //                     ];
    //                 } else {
    //                     $attendanceData[$date] = [
    //                         'log_date' => $date,
    //                         'in_time' => null,
    //                         'out_time' => null,
    //                         'status' => 'Absent'
    //                     ];
    //                 }
    //             }
    //         }
    //     }
    
    //     // Debugging: Check the content of $attendanceData
    //     // dd($attendanceData);
    
    //     // Return the view with attendance data and employee names
    //     return view('backend.HRMS.attendance', compact('attendanceData', 'employeeName'))
    //            ->with('success', 'Data fetched successfully');
    // }
    
    public function filterAttendance(Request $request)
{
    $employeeId = $request->input('EmployeeID');
    $monthYear = $request->input('Date');

    // Initialize query on employee_thumb_attendance table
    $query = DB::table('employee_thumb_attendance');

    // Apply filters based on employee ID and month/year
    if ($employeeId) {
        $query->where('ess_emp_code', $employeeId);
    }

    if ($monthYear) {
        $startDate = $monthYear . '-01'; // Start date of the month
        $endDate = date('Y-m-t', strtotime($startDate)); // End date of the month
        $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    // Get the attendance records
    $transactions = $query->get();

    // Fetch employee names for the dropdown
    $employeeName = Employees::where('is_delete', 0)->get();

    // Fetch holidays
    $holidays = Holidays::pluck('HolidayStartDate')->toArray();
    $holidays = array_map(function ($date) {
        return Carbon::parse($date)->format('Y-m-d');
    }, $holidays);

    // Get the list of dates in the selected month
    $dates = [];
    $currentDate = Carbon::parse($startDate);
    while ($currentDate->lte($endDate)) {
        $dates[] = $currentDate->format('Y-m-d');
        $currentDate->addDay();
    }

    // Create an array to hold attendance data with the status
    $attendanceData = [];
    foreach ($dates as $date) {
        $attendance = $transactions->firstWhere('log_date', $date);
        if ($attendance) {
            $attendanceData[$date] = [
                'log_date' => Carbon::parse($date)->format('d-m-Y'),
                'in_time' => Carbon::parse($attendance->in_time)->format('H:i'), // 24-hour format
                'out_time' => Carbon::parse($attendance->out_time)->format('H:i'), // 24-hour format
                'status' => 'Present'
            ];
        } else {
            if (in_array($date, $holidays)) {
                $attendanceData[$date] = [
                    'log_date' => Carbon::parse($date)->format('d-m-Y'),
                    'in_time' => null,
                    'out_time' => null,
                    'status' => 'Holiday'
                ];
            } else {
                $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                if ($dayOfWeek === Carbon::SUNDAY) {
                    $attendanceData[$date] = [
                        'log_date' => Carbon::parse($date)->format('d-m-Y'),
                        'in_time' => null,
                        'out_time' => null,
                        'status' => 'Week Off'
                    ];
                } else {
                    $attendanceData[$date] = [
                        'log_date' => Carbon::parse($date)->format('d-m-Y'),
                        'in_time' => null,
                        'out_time' => null,
                        'status' => 'Absent'
                    ];
                }
            }
        }
    }

    // Return the view with attendance data and employee names
    return view('backend.HRMS.attendance', compact('attendanceData', 'employeeName'))
           ->with('success', 'Data fetched successfully');
}



    public function view($id){        
        $stream_master = Attendance::where('AttendanceID',$id)->get();        
        $stream = Attendance::where('is_delete',0)->get();
        $employeeName = Employees::where('is_delete',0)->distinct()->get();
        return view('backend.HRMS.attendance', compact('stream_master','stream','employeeName'));
    }

    public function store(Request $request){
        $data = [
            'Date' => $request->Date,
            'Status' => $request->Status,
            'EmployeeID' => $request->EmployeeID,
        ];        
        Attendance::where('AttendanceID',$request->id)->update($data);
        return redirect()->route('attendance')->with('success','attendance has been Updated successfully.');
    }

    public function attendance_delete($id)
    {
        // echo $id;
        // exit;Position
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['AttendanceID'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id){
        $stream = Attendance::findOrFail($id);
        $stream->delete();
        return redirect()->route('attendance')->with('success','Deleted successfully.');
    }

}

