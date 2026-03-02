<?php

namespace App\Http\Controllers;

use App\Models\HrmsAttendanceLock;
use App\Models\HrmsBiometricDetail;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAttendance;
use App\Models\TransactionLog;
use App\Models\Employees;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\CurlJob;
use DB;
use App\Models\Holidays;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('m'));
        $year = $request->input('Attendanceyear', now()->format('Y'));
    
        // Create start and end dates for the selected month and year
        $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->toDateString();
        // $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        // $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $employeeId = $request->input('employee_id');

        // Fetch all employees
        $employees = HrmsEmployee::orderBy('first_name')->get();
        // // Fetch attendance data with filters
        // $query = HrmsEmployeeAttendance::whereBetween('log_date', [$startDate, $endDate]);

        // if ($employeeId) {
        //     $query->where('employee_id', $employeeId);
        // }

        // $attendanceData = $query->get()->groupBy('employee_id');
        $attendanceData = HrmsEmployeeAttendance::with('employee')
        ->whereBetween('log_date', [$startDate, $endDate])
        ->when($employeeId, fn($query) => $query->where('employee_id', $employeeId))
        ->get()
        ->groupBy('employee_id');
        return view('backend.HRMS.employeeattendance', compact('attendanceData', 'employees', 'startDate', 'endDate'));
    }

    public function sync(Request $request)
	{
		try {
			// Get Attendanceyear and month from request
			$attendanceYear = $request->input('Attendanceyear');
			$month = $request->input('month');

			if (!$attendanceYear || !$month) {
				return response()->json(['message' => 'Attendance year and month are required.'], 400);
			}

			// Create start and end date based on the given year and month
			$fromDate = Carbon::createFromDate($attendanceYear, $month, 1)->startOfMonth()->toDateString();
			$toDate = Carbon::createFromDate($attendanceYear, $month, 1)->endOfMonth()->addDay()->toDateString();
			$startDate = Carbon::parse($fromDate)->setTime(0, 0, 0);
			$endDate = Carbon::parse($toDate)->setTime(0, 0, 0);

			// Multiple machine serials
			$serialNumbers = ['AEXY211460054', 'TFDB244701229'];

			$userName = 'dev';
			$userPassword = 'Test@123';
			$strDataList = 'Blank';
			$url = 'http://45.248.190.34:8083/iclock/WebAPIService.asmx';

			$allLogs = [];

			foreach ($serialNumbers as $serialNumber) {
				// Build SOAP XML for this machine
				$xml = '<?xml version="1.0" encoding="utf-8"?>
					<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
						xmlns:xsd="http://www.w3.org/2001/XMLSchema"
						xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
						<soap:Body>
							<GetTransactionsLog xmlns="http://tempuri.org/">
								<FromDateTime>' . $startDate . '</FromDateTime>
								<ToDateTime>' . $endDate . '</ToDateTime>
								<SerialNumber>' . $serialNumber . '</SerialNumber>
								<UserName>' . $userName . '</UserName>
								<UserPassword>' . $userPassword . '</UserPassword>
								<strDataList>' . $strDataList . '</strDataList>
							</GetTransactionsLog>
						</soap:Body>
					</soap:Envelope>';

				// Make SOAP request
				$response = Http::withHeaders([
					'Content-Type' => 'text/xml; charset=utf-8',
					'SOAPAction' => 'http://tempuri.org/GetTransactionsLog',
				])->timeout(200)->send('POST', $url, [
					'body' => $xml,
				]);

				if ($response->failed()) {
					\Log::error('Failed to fetch from ESSL device', [
						'serial' => $serialNumber,
						'status' => $response->status(),
						'body' => $response->body(),
					]);
					continue; // Skip failed device
				}

				// Parse the SOAP XML response
				$xmlResponse = simplexml_load_string($response->body());
				$namespaces = $xmlResponse->getNamespaces(true);
				$soapBody = $xmlResponse->children($namespaces['soap'])->Body;
				$getTransactionsLogResponse = $soapBody->children($namespaces[''])->GetTransactionsLogResponse;
				$strDataListResponse = (string)$getTransactionsLogResponse->strDataList;

				// Split logs and include machine serial
				$logs = explode("\n", trim($strDataListResponse));
				foreach ($logs as $log) {
					$log = trim($log);
					if (empty($log)) continue;

					[$userId, $timestamp] = explode("\t", $log);
					$allLogs[] = [
						'userId' => $userId,
						'timestamp' => $timestamp,
						'machine_serial' => $serialNumber,
					];
				}
			}

			if (empty($allLogs)) {
				return response()->json(['success' => false, 'message' => 'No logs received from any device.']);
			}

			// Process all logs together
			foreach ($allLogs as $entry) {
				$userId = $entry['userId'];
				$timestamp = $entry['timestamp'];
				$logDate = date('Y-m-d', strtotime($timestamp));
				$logTime = date('H:i:s', strtotime($timestamp));

				$existingLog = HrmsEmployeeAttendance::where('ess_emp_code', $userId)
					->where('log_date', $logDate)
					->first();

				if ($existingLog) {
					if ($logTime < $existingLog->in_time) {
						$existingLog->update(['in_time' => $logTime]);
					}
					if (!$existingLog->out_time || $logTime > $existingLog->out_time) {
						$existingLog->update(['out_time' => $logTime]);
					}
				} else {
					HrmsEmployeeAttendance::insert([
						'ess_emp_code' => $userId,
						'employee_id' => HrmsBiometricDetail::where('ess_emp_code', $userId)->value('employee_id'),
						'log_date' => $logDate,
						'in_time' => $logTime,
					]);
				}
			}

			return response()->json(['success' => true, 'message' => 'Attendance data synced successfully from both devices!']);

		} catch (\Exception $e) {
			\Log::error('Sync Error', ['error' => $e->getMessage()]);
			return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
		}
	}




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



    public function view($id)
    {
        $stream_master = Attendance::where('AttendanceID', $id)->get();
        $stream = Attendance::where('is_delete', 0)->get();
        $employeeName = Employees::where('is_delete', 0)->distinct()->get();
        return view('backend.HRMS.attendance', compact('stream_master', 'stream', 'employeeName'));
    }

    public function store(Request $request)
    {
        $data = [
            'Date' => $request->Date,
            'Status' => $request->Status,
            'EmployeeID' => $request->EmployeeID,
        ];
        Attendance::where('AttendanceID', $request->id)->update($data);
        return redirect()->route('attendance')->with('success', 'attendance has been Updated successfully.');
    }

    public function attendance_delete($id)
    {
        // echo $id;
        // exit;Position
        $a = explode('-', $id);
        $b = $a[1];
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c, ['AttendanceID' => $b]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id)
    {
        $stream = Attendance::findOrFail($id);
        $stream->delete();
        return redirect()->route('attendance')->with('success', 'Deleted successfully.');
    }
    public function lockAttendance(Request $request)
    {
        $request->validate([
            'Attendanceyear' => 'required',
            'month' => 'required|min:1|max:12',
        ]);

        HrmsAttendanceLock::updateOrCreate(
            ['year' => $request->Attendanceyear, 'month' => $request->month],
            ['is_locked' => true]
        );

        return response()->json(['success' => 'Attendance locked successfully']);
    }
    public function unlockAttendance(Request $request)
    {
        $request->validate([
            'Attendanceyear' => 'required',
            'month' => 'required|min:1|max:12',
        ]);

        HrmsAttendanceLock::where('year', $request->Attendanceyear)
            ->where('month', $request->month)
            ->update(['is_locked' => false]);

        return response()->json(['success' => 'Attendance unlocked successfully']);
    }
}


// if (AttendanceLock::isLocked($attendance->date->year, $attendance->date->month)) {
//     return response()->json(['error' => 'Attendance for this month is locked and cannot be edited.'], 403);
// }