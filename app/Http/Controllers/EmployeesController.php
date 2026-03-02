<?php

namespace App\Http\Controllers;

use App\Models\CommanModel;
use App\Models\Employees;
use App\Models\HrmsBankDetail;
use App\Models\HrmsBiometricDetail;
use App\Models\HrmsDepartment;
use App\Models\HrmsDocument;
use App\Models\HrmsEducationalQualification;
use App\Models\HrmsEmergencyContact;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeAddress;
use App\Models\HrmsLeaveStaffAllocation;
use App\Models\HrmsLeaveType;
use App\Models\HrmsPosition;
use App\Models\HrmsSalary;
use App\Models\HrmsShift;
use App\Models\HrmsShiftType;
use App\Models\HrmsStaffType;
use App\Models\HrmsStatutoryInformation;
use App\Models\HrmsWorkExperience;
use App\Models\Position;
use App\Models\Department;
use App\Models\Stafftype;
use App\Models\HrmsEmployeeLeaveBalance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmployeesController extends Controller
{
    public function index()
    {
        $stream = HrmsEmployee::all();
        $deparments = HrmsDepartment::all();

        $staffTypes = HrmsStaffType::all();
        $shifts = HrmsShift::all();

        $positions = HrmsPosition::all();
        return view('backend.HRMS.employees', compact('stream', 'deparments', 'positions', 'staffTypes', 'shifts'));
    }


    public function create(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'FirstName' => 'required|string|max:255',
                  //  'Email' => 'required|email|unique:users,email',
                    'DateOfBirth' => 'required|date',
                    'Gender' => 'required|string|in:male,female,other', 
                    'JoiningDate' => 'required|date',
                    'DepartmentID' => 'required|integer|exists:hrms_departments,id', 
                    'PositionID' => 'required|integer|exists:hrms_positions,id',
					'employee_status' => 'required|string|in:active,inactive,retired',
                    'contact_number' => [
                        'required',
                        'string',
                        'regex:/^[6-9]\d{9}$/',
                        'max:15',
                    ],
                    'phone_number' => [
                        'nullable',
                        'string',
                        'regex:/^[6-9]\d{9}$/',
                        'max:15',
                    ],
                    'ShiftID' => 'required|integer|exists:hrms_shifts,id',
                    'employment_type' => 'required|integer|exists:hrms_staff_type,id',
                    'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:600',
					// 👇 New validation for employee_status_date
                'employee_status_date' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if (in_array($request->employee_status, ['inactive', 'retired']) && empty($value)) {
                            $fail('The status date is required when employee status is inactive or retired.');
                        }
                    },
                ],
                ],
                [],
                [
                    'ShiftID' => 'shift',
                    'employment_type' => 'Staff Type',
                    'DepartmentID' => 'department',
                    'PositionID' => 'position',
                ]
            );
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            \Log::info('Request Data:', $request->all());
            if (isset($request->profile_picture)) {
                $extension = $request->profile_picture->getClientOriginalExtension(); // Get the file extension

                $newFileName = Str::random(10) . '.' . $extension; // Generate a random filename
                $destinationPath = public_path('uploads/admin/EmployeeProfile'); // Set the destination directory

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the file to the specified directory
                $request->profile_picture->move($destinationPath, $newFileName);

                // Save the file path relative to the `public` directory
                $profile_picture_filePath = 'EmployeeProfile/' . $newFileName;
            }
			$employeeStatusDate = null;
				if (in_array($request->employee_status, ['inactive', 'retired'])) {
					$employeeStatusDate = $request->employee_status_date ?? now()->format('Y-m-d');
				}
				
            $employeeData = [
                'first_name' => $request->FirstName,
                'last_name' => $request->LastName,
                'date_of_birth' => $request->DateOfBirth,
                'gender' => $request->Gender,
                'father_name' => $request->Father_Name,
                'email' => $request->Email,
                'date_of_joining' => $request->JoiningDate,
                'department_id' => $request->DepartmentID,
                'position_id' => $request->PositionID,
                'contact_number' => $request->contact_number,
                'marital_status' => $request->marital_status,
                'spouse_name' => $request->spouse_name,
                'shift_id' => $request->ShiftID,
                'staff_type_id' => $request->employment_type,
				'employee_status' => $request->employee_status,
				'employee_status_date' => $employeeStatusDate,
                'is_vacation' => $request->is_vacation ?? false

            ];
            if (isset($profile_picture_filePath)) {
                $employeeData['profile_picture'] = $profile_picture_filePath;
            }
            $employee = HrmsEmployee::create($employeeData);

            // Step 2: Handle multiple addresses (if any)
            if ($request->has('address_line')) {
                $addresses = $request->input('address_line');
                foreach ($addresses as $key => $address) {
                    // Prepare the address data with employee_id
                    $addressData = [
                        'employee_id' => $employee->id,
                        'address_type' => $request->input('address_type')[$key],
                        'address_line' => $address,
                        'city' => $request->input('city')[$key],
                        'tehsil' => $request->input('tehsil')[$key],
                        'district' => $request->input('district')[$key],
                        'pin_code' => $request->input('pin_code')[$key],
                    ];

                    // Create the address record
                    HrmsEmployeeAddress::create($addressData);
                }
            }

            // Step 3: Handle multiple educational qualifications (if any)
            if ($request->has('qualification')) {
                $qualifications = $request->input('qualification');
                foreach ($qualifications as $key => $qualification) {
                    // Prepare the qualification data with employee_id
                    $educationData = [
                        'employee_id' => $employee->id,
                        'qualification' => $qualification,
                        'specialization' => $request->input('specialization')[$key],
                        'institution_name' => $request->input('institution_name')[$key],
                        'year_of_graduation' => $request->input('year_of_graduation')[$key],
                        'certification' => $request->input('certification')[$key],
                    ];

                    // Create the education record
                    HrmsEducationalQualification::create($educationData);
                }
            }

            $contactData = [
                'employee_id' => $employee->id,
                'contact_name' => $request->input('contact_name'),
                'relationship' => $request->input('relationship'),
                'phone_number' => $request->input('phone_number'),
                'alternative_phone_number' => $request->input('alternative_phone_number'),
            ];
            HrmsEmergencyContact::create($contactData);
            //bank details
            $bankData = [
                'employee_id' => $employee->id,
                'bank_name' => $request->input('bank_name'),
                'account_number' => $request->input('account_number'),
                'ifsc_code' => $request->input('ifsc_code'),
                'branch_name' => $request->input('branch_name'),
                'pan_number' => $request->input('pan_number'),
            ];
            HrmsBankDetail::create($bankData);

            //biometric details
            //`employee_id`, `card_number`, `ess_emp_code`, `device_code`,
            $biometricData = [
                'employee_id' => $employee->id,
                'card_number' => $request->input('card_number'),
                'ess_emp_code' => $request->input('ess_emp_code'),
                'device_code' => $request->input('device_code'),
            ];
            HrmsBiometricDetail::create($biometricData);

            //employee experiences
            //`employee_id`, `organization_name`, `job_title`, `start_date`, `end_date`, `roles_responsibilities`,
            $experienceData = [
			'employee_id' => $employee->id,
			'organization_name' => $request->input('organization_name'),
			'job_title' => $request->input('job_title'),
			'start_date' => $request->input('start_date'),
			'end_date' => $request->input('end_date'),
			'roles_responsibilities' => $request->input('roles_responsibilities'),
			'total_experience' => $request->input('total_experience'), 
			];

			HrmsWorkExperience::create($experienceData);


            //employee_salaries
            // `employee_id`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `health_insurance`, `retirement_benefits`,

            //employee statutory info
            //`employee_id`, `esic_number`, `epf_number`, `uan_number`, `samagra_id`, `pan_number`, `aadhar_number`, `ayushman_number`,
            $statutoryInfo = [
                'employee_id' => $employee->id,
                'esic_number' => $request->input('esic_number'),
                'epf_number' => $request->input('epf_number'),
                'uan_number' => $request->input('uan_number'),
                'samagra_id' => $request->input('samagra_id'),
                'pan_number' => $request->input('pan_number'),
                'aadhar_number' => $request->input('aadhar_number'),
                'ayushman_number' => $request->input('ayushman_number'),
            ];

            HrmsStatutoryInformation::create($statutoryInfo);

            // //salary details
            // $salaryData = $request->only([
            //     'basic_salary',
            //     'allowances',
            //     'deductions',
            //     'net_salary',
            //     'health_insurance',
            //     'retirement_benefits'
            // ]);
            // $salaryData['employee_id'] = $employee->id; // Associate with the created employee
            // HrmsSalary::create($salaryData);


            //Handle multiple document uploads
            if ($request->has('document_type')) {
                $documents = $request->upload; // Get all uploaded files

                foreach ($documents as $index => $file) {
                    if (isset($request->document_type[$index])) {
                        $extension = $file->getClientOriginalExtension(); // Get the file extension

                        $newFileName = Str::random(10) . '.' . $extension; // Generate a random filename
                        $destinationPath = public_path('uploads/admin/EmployeeDocuments'); // Set the destination directory

                        // Ensure the directory exists
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }

                        // Move the file to the specified directory
                        $file->move($destinationPath, $newFileName);

                        // Save the file path relative to the `public` directory
                        $filePath = 'EmployeeDocuments/' . $newFileName;

                        // Create a record for the uploaded document
                        HrmsDocument::create([
                            'employee_id' => 4,
                            'document_type' => $request->document_type[$index],
                            'file_path' => $filePath,
                            'is_uploaded' => true,
                        ]);
                    }
                }
            }

            //assigne leaves
            $this->initializeLeaveBalances($employee->id, $employee->staff_type_id, $request->is_vacation);

            // Step 5: If everything is fine, commit the transaction
            DB::commit();

            return redirect()->route('employee')->with('success', 'Employee has been created successfully.');
        } catch (\Exception $e) {
            // In case of an error, roll back the transaction
            \Log::info('Error - ' . $e->getMessage());
            DB::rollBack();
            return back()->with('error', 'Failed to create employee: ' . $e->getMessage());
        }
    }

    public function initializeLeaveBalances($employeeId, $employeeStaffId, $isVacationType)
    {
        Log::info("Initializing leave balances for employee ID: {$employeeId}, staff type ID: {$employeeStaffId}");

        $leaveAllocated = HrmsLeaveStaffAllocation::where('hrms_staff_type_id', $employeeStaffId)->get();

        if ($leaveAllocated->isEmpty()) {
            Log::warning("No leave allocations found for staff type ID: {$employeeStaffId}");
        }

        foreach ($leaveAllocated as $leave) {
            $balance = $leave->max_allowed;

            HrmsEmployeeLeaveBalance::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $leave->leave_type_id,
                'balance' => $balance,
            ]);

            Log::info("Leave balance created", [
                'employee_id' => $employeeId,
                'leave_type_id' => $leave->leave_type_id,
                'balance' => $balance
            ]);
        }

        if ($isVacationType) {
            HrmsEmployeeLeaveBalance::create([
                'employee_id' => $employeeId,
                'leave_type_id' => 4, // Assuming 4 is the vacation leave type ID
                'balance' => 30,
            ]);

            Log::info("Vacation leave added for employee", [
                'employee_id' => $employeeId,
                'leave_type_id' => 4,
                'balance' => 30
            ]);
        } else {
            Log::info("Vacation leave not required for employee ID: {$employeeId}");
        }

        Log::info("Leave balance initialization complete for employee ID: {$employeeId}");
    }


    public function view($id)
    {
        $stream_master = HrmsEmployee::where('id', $id)->get();
        $stream = HrmsEmployee::all();
        $deparments = HrmsDepartment::all();

        $staffTypes = HrmsStaffType::all();
        $shifts = HrmsShift::all();

        $positions = HrmsPosition::all();

        return view('backend.HRMS.employees', compact('stream_master', 'stream', 'deparments', 'positions', 'staffTypes', 'shifts'));
    }

    public function store(Request $request)
    {
        $data = [
            'FirstName' => $request->FirstName,
            'LastName' => $request->LastName,
            'Father_Name' => $request->Father_Name,
            'Email' => $request->Email,
            'Phone' => $request->Phone,
            'mobile' => $request->mobile,
            'Address' => $request->Address,
            'ess_emp_code' => $request->ess_emp_code,
            'DateOfBirth' => $request->DateOfBirth,
            'JoiningDate' => $request->JoiningDate,
            'DepartureDate' => $request->DepartureDate,
			'confirmation_date' => $request->ConfirmationDate,
            'DepartmentID' => $request->DepartmentID,
            'PositionID' => $request->PositionID,
            // 'ess_emp_code' => $request->ess_emp_code,
            'DeviceCode' => $request->DeviceCode,
            'Company' => $request->Company,
            'Location' => $request->Location,
            'Designation' => $request->Designation,
            'Grade' => $request->Grade,
            'Team' => $request->Team,
            'Category' => $request->Category,
            'staff_type_id' => $request->EmploymentType,
            'Gender' => $request->Gender,
            'DOJ' => $request->DOJ,
            'DOC' => $request->DOC,
            'CardNumber' => $request->CardNumber,
            'ShiftRoaster' => $request->ShiftRoaster,
            'Status' => $request->Status,
            'City' => $request->City,
            'Taluka' => $request->Taluka,
            'District' => $request->District,
            'PIN_Code' => $request->PIN_Code,
            'Experience' => $request->Experience,
            'Educational_Qualification' => $request->Educational_Qualification,
            'Previous_Employer' => $request->Previous_Employer,
            'Prevoius_Designation' => $request->Prevoius_Designation,
            'Previous_Salary' => $request->Previous_Salary,
            'Duration' => $request->Duration,
            'Maratial_Status' => $request->Maratial_Status,
            'Spouse_Name' => $request->Spouse_Name,
            'Spouse_Contact_No' => $request->Spouse_Contact_No,
            'No_of_Childern' => $request->No_of_Childern,
            'Serial_No' => $request->Serial_No,
            'Joining_Designation' => $request->Joining_Designation,
            'Joining_Grade' => $request->Joining_Grade,
            'Current_Grade' => $request->Current_Grade,
            'Working_Shift' => $request->Working_Shift,
            'Default_In_Time' => $request->Default_In_Time,
            'Default_Out_Time' => $request->Default_Out_Time,
            'Default_Total_Time' => $request->Default_Total_Time,
            'Bank_Name' => $request->Bank_Name,
            'Bank_Branch_Name' => $request->Bank_Branch_Name,
            'Account_No' => $request->Account_No,
            'Do_Not_Apply_EPF_Limit' => $request->Do_Not_Apply_EPF_Limit,
            'ESI_No' => $request->ESI_No,
            'PAN_No' => $request->PAN_No,
            'Ward_Study_in_Institute' => $request->Ward_Study_in_Institute,
            'Is_Discontinued' => $request->Is_Discontinued,
            'Leaving_Date' => $request->Leaving_Date,
            'Adhar_CardNo' => $request->Adhar_CardNo,
            'UAN_No' => $request->UAN_No,
            'IFSCCODE' => $request->IFSCCODE,
            'AyushmanNo' => $request->AyushmanNo,
            'IsFirstDoseVaccinated' => $request->IsFirstDoseVaccinated,
            'FirstDoseVaccinatedDate' => $request->FirstDoseVaccinatedDate,
            'IsSecondDoseVaccinated' => $request->IsSecondDoseVaccinated,
            'SecondDoseVaccinatedDate' => $request->SecondDoseVaccinatedDate,
            'IsBoosterDoseVaccinated' => $request->IsBoosterDoseVaccinated,
            'BoosterDoseVaccinatedDate' => $request->BoosterDoseVaccinatedDate,
            'samagra_id' => $request->samagra_id,
            'Police_Verification' => $request->Police_Verification,
            'EmpName_as_on_Adhaar' => $request->EmpName_as_on_Adhaar,
            'PersonalEmail' => $request->PersonalEmail,
            'staff_type' => $request->staff_type, // Save Type directly as string
            'shift_type' => $request->shift_type,
			'employee_status' => $request->employee_status,
            // 'is_vacatation_staff' => $request->has('is_vacatation_staff') ? 1 : 0, // Handle checkbox
            'is_vacatation_staff' => $request->has('is_vacatation_staff') ? 1 : 0,

        ];
		// ✅ Add employee_status_date if status is inactive or retired
		if (in_array($request->employee_status, ['inactive', 'retired'])) {
			$data['employee_status_date'] = now(); // Current timestamp
		} else {
			$data['employee_status_date'] = null; // Clear date if status is active
		}

        // Update the employee record with the new data
        Employees::where('EmployeeID', $request->id)->update($data);

        return redirect()->route('employee')->with('success', 'Employee has been updated successfully.');
    }


    public function edit(Request $request)
    {
        // Start transaction to ensure data integrity
        DB::beginTransaction();
        $validator = Validator::make(
            $request->all(),
            [
                'FirstName' => 'required|string|max:255',
               // 'Email' => 'required|email|unique:users,email',
                'DateOfBirth' => 'required|date',
                'Gender' => 'required|string|in:male,female,other',
                'JoiningDate' => 'required|date',
                'DepartmentID' => 'required|integer|exists:hrms_departments,id',
                'PositionID' => 'required|integer|exists:hrms_positions,id',  
                'contact_number' => [
                    'required',
                    'string',
                    'regex:/^[6-9]\d{9}$/',
                    'max:15',
                ],
                'phone_number' => [
                        'nullable',
                        'string',
                        'regex:/^[6-9]\d{9}$/',
                        'max:15',
                    ],
                    'alternative_phone_number' => [
                        'nullable',
                        'string',
                        'regex:/^[6-9]\d{9}$/',
                        'max:15',
                    ],
                'ShiftID' => 'required|integer|exists:hrms_shifts,id', 
                'employment_type' => 'required|integer|exists:hrms_staff_type,id',
                'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:600',
				// Employee status validation
				'employee_status'      => 'required|in:active,inactive,retired',
				'employee_status_date' => Rule::requiredIf(
                in_array($request->employee_status, ['inactive','retired'])
              ),
        ],
        [],
        [
                'ShiftID' => 'shift',
                'employment_type' => 'Staff Type',
                'DepartmentID' => 'department',
                'PositionID' => 'position',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {

            // Fetch the employee data to edit
            $employee = HrmsEmployee::findOrFail($request->id); // Find the employee by ID
            \Log::info('Request Data:', $request->all());

            if (isset($request->profile_picture)) {
                $extension = $request->profile_picture->getClientOriginalExtension(); // Get the file extension
                $newFileName = Str::random(10) . '.' . $extension; // Generate a random filename
                $destinationPath = public_path('uploads/admin/EmployeeProfile'); // Set the destination directory

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the file to the specified directory
                $request->profile_picture->move($destinationPath, $newFileName);

                // Save the file path relative to the `public` directory
                $profile_picture_filePath = 'EmployeeProfile/' . $newFileName;
            }
            // Update the employee data
            $employeeData = [
                'first_name' => $request->FirstName,
                'last_name' => $request->LastName,
                'date_of_birth' => $request->DateOfBirth,
                'gender' => $request->Gender,
                'father_name' => $request->Father_Name,
                'email' => $request->Email,
                'date_of_joining' => $request->JoiningDate,
				'confirmation_date' => $request->ConfirmationDate,
                'department_id' => $request->DepartmentID,
                'position_id' => $request->PositionID,
                'contact_number' => $request->contact_number,
                'marital_status' => $request->marital_status,
                'spouse_name' => $request->spouse_name,
                'shift_id' => $request->ShiftID,
                'staff_type_id' => $request->employment_type,
				'employee_status' => $request->employee_status,
				'employee_status_date'=> $request->employee_status_date,
                'is_vacation' => $request->is_vacation ?? false
            ];
            if (isset($profile_picture_filePath)) {
                $employeeData['profile_picture'] = $profile_picture_filePath;
            }

            //check change in 'staff_type_id'
            if ($employee->staff_type_id != $request->employment_type) {
                //delete all leave balances
                HrmsEmployeeLeaveBalance::where('employee_id', $employee->id)->delete();
                //initialize leave balances
                $this->initializeLeaveBalances($employee->id, $request->employment_type, $request->is_vacation ?? false);
            }
            $employee->update($employeeData);

            // Step 2: Handle multiple addresses (if any)
            if ($request->has('address_line')) {
                // Delete existing addresses before updating
                $employee->addresses()->delete();

                $addresses = $request->input('address_line');
                foreach ($addresses as $key => $address) {
                    // Prepare the address data with employee_id
                    $addressData = [
                        'employee_id' => $employee->id,
                        'address_type' => $request->input('address_type')[$key],
                        'address_line' => $address,
                        'city' => $request->input('city')[$key],
                        'tehsil' => $request->input('tehsil')[$key],
                        'district' => $request->input('district')[$key],
                        'pin_code' => $request->input('pin_code')[$key],
                    ];

                    // Create the address record
                    HrmsEmployeeAddress::create($addressData);
                }
            }

            // Step 3: Handle multiple educational qualifications (if any)
            if ($request->has('qualification')) {
                // Delete existing qualifications before updating
                $employee->educationalQualifications()->delete();

                $qualifications = $request->input('qualification');
                foreach ($qualifications as $key => $qualification) {
                    // Prepare the qualification data with employee_id
                    $educationData = [
                        'employee_id' => $employee->id,
                        'qualification' => $qualification,
                        'specialization' => $request->input('specialization')[$key],
                        'institution_name' => $request->input('institution_name')[$key],
                        'year_of_graduation' => $request->input('year_of_graduation')[$key],
                        'certification' => $request->input('certification')[$key],
                    ];

                    // Create the education record
                    HrmsEducationalQualification::create($educationData);
                }
            }

            // Update emergency contact details
            $contactData = [
                'employee_id' => $employee->id,
                'contact_name' => $request->input('contact_name'),
                'relationship' => $request->input('relationship'),
                'phone_number' => $request->input('phone_number'),
                'alternative_phone_number' => $request->input('alternative_phone_number'),
            ];
            $employee->emergencyContacts()->update($contactData);

            // Update bank details
            $bankData = [
                'employee_id' => $employee->id,
                'bank_name' => $request->input('bank_name'),
                'account_number' => $request->input('account_number'),
                'ifsc_code' => $request->input('ifsc_code'),
                'branch_name' => $request->input('branch_name'),
                'pan_number' => $request->input('pan_number'),
            ];
            $employee->bankDetails()->update($bankData);

            // Update biometric details
            $biometricData = [
                'employee_id' => $employee->id,
                'card_number' => $request->input('card_number'),
                'ess_emp_code' => $request->input('ess_emp_code'),
                'device_code' => $request->input('device_code'),
            ];
            $employee->biometricDetails()->update($biometricData);

            // Update work experience details
            $experienceData = [
                'employee_id' => $employee->id,
                'organization_name' => $request->input('organization_name'),
                'job_title' => $request->input('job_title'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'roles_responsibilities' => $request->input('roles_responsibilities'),
				'total_experience' => $request->input('total_experience'),
            ];
            $employee->workExperiences()->update($experienceData);

            // Update statutory information details
            $statutoryInfo = [
                'employee_id' => $employee->id,
                'esic_number' => $request->input('esic_number'),
                'epf_number' => $request->input('epf_number'),
                'uan_number' => $request->input('uan_number'),
                'samagra_id' => $request->input('samagra_id'),
                'pan_number' => $request->input('pan_number'),
                'aadhar_number' => $request->input('aadhar_number'),
                'ayushman_number' => $request->input('ayushman_number'),
            ];
            $employee->statutoryInformation()->update($statutoryInfo);


            // Handle multiple document uploads
            // if ($request->has('upload')) {
            //     // Remove existing documents before updating
            //     $employee->documents()->delete();

            //     $documents = $request->upload; // Get all uploaded files
            //     foreach ($documents as $index => $file) {
            //         if (isset($request->document_type[$index])) {
            //             $extension = $file->getClientOriginalExtension(); // Get the file extension

            //             $newFileName = Str::random(10) . '.' . $extension; // Generate a random filename
            //             $destinationPath = public_path('uploads/admin/EmployeeDocuments'); // Set the destination directory

            //             // Ensure the directory exists
            //             if (!file_exists($destinationPath)) {
            //                 mkdir($destinationPath, 0755, true);
            //             }

            //             // Move the file to the specified directory
            //             $file->move($destinationPath, $newFileName);

            //             // Save the file path relative to the `public` directory
            //             $filePath = 'EmployeeDocuments/' . $newFileName;

            //             // Create a record for the uploaded document
            //             HrmsDocument::create([
            //                 'employee_id' => $employee->id,
            //                 'document_type' => $request->document_type[$index],
            //                 'file_path' => $filePath,
            //                 'is_uploaded' => true,
            //             ]);
            //         }
            //     }
            // }

            $documentTypes = [
                'id_proof' => 'ID Proof',
                'address_proof' => 'Address Proof',
                'educational_certificate' => 'Educational Certificate',
                'experience_letter' => 'Experience Letter',
                'passport_size_photo' => 'Passport Size Photograph',
                'police_verification' => 'Police Verification Report',
                'medical_certificate' => 'Medical Certificate',
                'salary_slip' => 'Last Salary Slip',
            ];
            $documentData = [];
            $timestamp = now();

            foreach ($documentTypes as $field => $type) {
                $documentData[] = [
                    'employee_id' => $employee->id,
                    'document_type' => $type,
                    'is_uploaded' => $request->has($field) ? 1 : 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            HrmsDocument::upsert(
                $documentData,
                ['employee_id', 'document_type'],
                ['is_uploaded', 'updated_at']
            );
            DB::commit();
            return redirect()->route('employee')->with('success', 'Employee has been updated successfully.');
        } catch (\Exception $e) {
            \Log::info('Error - ' . $e->getMessage());
            DB::rollBack();
            return back()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        $employee = HrmsEmployee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employee')->with('success', 'Deleted successfully.');
    }

    public function employee_delete($id)
    {
        $delete_resp = CommanModel::soft_delete('employees', ['EmployeeID' => $id]);
        if ($delete_resp === 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp === 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

   
    

    
public function importEmployees(Request $request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'file' => 'required|file|mimes:csv,txt,xlsx|max:2048' 
			]);
			if ($validator->fails()) {
				return redirect()->back()
					->withErrors($validator)
					->withInput();
			}
		try {
			$file = $request->file('file');
			$path = $file->getRealPath();
			$extension = $file->getClientOriginalExtension();

			if ($extension === 'csv') {
				$this->importCSV($path);
			} elseif ($extension === 'xlsx') {
				$this->importXLSX($path);
			} else {
				return response()->json(['error' => 'Invalid file format'], 422);
			}

			return redirect()->back()->with('success', 'Employees imported successfully!');
		} catch (\Exception $e) {
			return response()->json(['error' => 'File processing failed', 'details' => $e->getMessage()], 500);
		}
	}
private function importCSV($path)
{
    $handle = fopen($path, "r");
    if ($handle === FALSE) {
        return;
    }

    // Read the first row as headers and normalize them
    $headers = fgetcsv($handle);
    $headers = array_map(fn($header) => strtolower(str_replace(' ', '_', trim($header))), $headers);

    DB::beginTransaction();

    try {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Map row data dynamically using the headers
            $mappedRow = array_combine($headers, $row);
            if (!$mappedRow) {
                continue; // Skip row if mapping fails
            }
                $this->processEmployeeRow($mappedRow);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('CSV Import Error: ' . $e->getMessage());
            throw $e;
        }

        fclose($handle);
    
}
private function importXLSX($path)
{
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Extract headers (first row)
    $headers = array_map('strtolower', array_shift($rows)); // Convert to lowercase for consistency

    DB::beginTransaction();

    try {
        foreach ($rows as $row) {
            // Map the row data using the headers
            $mappedRow = array_combine($headers, $row);
            // Ensure mapping worked correctly
            if (!$mappedRow) {
                continue; // Skip if mapping fails
            }

            $this->processEmployeeRow( $mappedRow);
        }
        DB::commit();
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error('XLSX Import Error: ' . $e->getMessage());
        throw $e;
    }
}
private function detectFileType($dateValue)
{
    return preg_match('/[-\/.]/', $dateValue) ? 'csv' : 'excel';
}

private function convertToDate($dateValue, $fileType)
{
    return $fileType === 'csv' ? Carbon::parse($dateValue) : Date::excelToDateTimeObject($dateValue);
}
private function processEmployeeRow($row)
    {
        $row=array_map(function ($value) {
            return $value===''?null:$value;
        }, $row);
        // Validate each row
        $validator = Validator::make($row, [
            'emp_first_name' => 'required|string|max:255',
            'emp_last_name' => 'required|string|max:255',
            'date_of_birth' => 'required',
            'gender' => 'required|string|in:male,female,other',
            'phone_no' => 'required|numeric|digits:10',
            'joining_date' => 'required',
            'emp_type' => 'required|string',
            'department' => 'required|string',
            'shift_id' => 'required|integer|exists:hrms_shifts,id',
          //  'email' => 'required|email',
            'position' => 'required|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed for row: ', $validator->errors()->toArray());
            return;
        }

        $department = HrmsDepartment::where('department_name', $row['department'])->first();
        $position = HrmsPosition::where('position_name', $row['position'])->first();
        $staffType = HrmsStaffType::where('staff_type_name', $row['emp_type'])->first();
        $fileType = $this->detectFileType($row['date_of_birth']);
        if (!$position) {
            $position = HrmsPosition::create(['position_name' => $row['position']]);
        }
        if (!$department) {
            $department = HrmsDepartment::create(['department_name' => $row['department']]);
        }
        $employeeData = [
            'first_name' => $row['emp_first_name'],
            'last_name' => $row['emp_last_name'],
            'date_of_birth' => $this->convertToDate($row['date_of_birth'], $fileType),
            'gender' => $row['gender'],
            'email' => $row['email'],
            'date_of_joining' => $this->convertToDate($row['joining_date'], $fileType),
            'department_id' => $department->id ?? null,
            'position_id' => $position->id ?? null,
            'contact_number' => $row['phone_no'],
            'marital_status' => $row['marital_status'] ?? null,
            'shift_id' => $row['shift_id'],
            'staff_type_id' => $staffType->id ?? null,
            'is_vacation' => Str::lower($row['is_vacation'] ?? '') === 'true' ? 1 : 0
        ];

        $employee = HrmsEmployee::create($employeeData);

        $this->processAddress($row, $employee);
        $this->processEducation($row, $employee);
        $this->processEmergencyContact($row, $employee);
        $this->processBankDetails($row, $employee);
        $this->processBiometricDetails($row, $employee);
        $this->processStatutoryInformation($row, $employee);
        $this->initializeLeaveBalances($employee->id, $employee->staff_type_id, $row['is_vacation']);
    }

    private function processAddress($row, $employee)
    {
        if (isset($row['address_line'])) {
            HrmsEmployeeAddress::create([
                'employee_id' => $employee->id,
                'address_type' => 'Permanent',
                'address_line' => $row['address_line'],
                'city' => $row['city'],
                'tehsil' => $row['tehsil'],
                'district' => $row['district'],
                'pin_code' => $row['pin_code'],
            ]);
        }
    }

    private function processEducation($row, $employee)
    {
        if (isset($row['education'])) {
            HrmsEducationalQualification::create([
                'employee_id' => $employee->id,
                'qualification' => $row['education'],
                'institution_name' => $row['university'],
                'year_of_graduation' => $row['year_of_passing']
            ]);
        }
    }

    private function processEmergencyContact($row, $employee)
    {
        HrmsEmergencyContact::create([
            'employee_id' => $employee->id,
            'contact_name' => $row['emergency_contact_name'],
            'relationship' => $row['emergency_contact_relation'],
            'phone_number' => $row['emergency_contact_number']
        ]);
    }

    private function processBankDetails($row, $employee)
    {
        if (isset($row['account_number'])) {
            HrmsBankDetail::create([
                'employee_id' => $employee->id,
                'bank_name' => $row['bank_name'] ?? null,
                'account_number' => $row['account_number'] ?? null,
                'ifsc_code' => $row['ifsc_code'] ?? null,
                'branch_name' => $row['branch_name'] ?? null,
                'pan_number' => $row['pan_number'] ?? null,
            ]);
        }
    }

    private function processBiometricDetails($row, $employee)
    {
        if (isset($row['is_vacation'])) {
            HrmsBiometricDetail::create([
                'employee_id' => $employee->id,
                'card_number' => $row['emp_code'] ?? null,
                'ess_emp_code' => $row['ess_emp_code'] ?? null,
                'device_code' => $row['device_code'] ?? null,
            ]);
        }
    }

    private function processStatutoryInformation($row, $employee)
    {
        HrmsStatutoryInformation::create([
            'employee_id' => $employee->id,
            'esic_number' => $row['esic_number'] ?? null,
            'epf_number' => $row['epf_number'] ?? null,
            'uan_number' => $row['uan_number'] ?? null,
            'samagra_id' => $row['samagra_id'] ?? null,
            'pan_number' => '',
            'aadhar_number' => $row['aadhaar_number'] ?? null,
            'ayushman_number' => $row['ayushman_number'] ?? null,
        ]);
    }
	
	public function exportEmployeesCsv()
	{
		$employees = HrmsEmployee::with([
			'department',
			'staffType',
			'addresses',
			'bankDetails',
			'biometricDetails',
			'educationalQualifications',
			'workExperiences',
			'emergencyContacts',
			'statutoryInformation',
		])->get();

		$filename = 'employees_complete_' . now()->format('Ymd_His') . '.csv';

		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename=$filename",
		];

		$callback = function () use ($employees) {
			$file = fopen('php://output', 'w');

			// Header row
			fputcsv($file, [
				'Employee ID', 'Name', 'Email', 'Phone', 'Date of Birth', 'Department', 'Staff Type',
				'Permanent Address',
				'Bank Name', 'Account Number',
				'Biometric ID',
				'Highest Education', 'Institution',
				'Experience Years',
				'Emergency Contact Name', 'Emergency Contact Phone',
				'PAN', 'Aadhar', 'ESIC Number', 'UAN Number', 'Joining Date', 'Confirmation Date', 'Employement Status', 'Is Vacation'
			]);

			foreach ($employees as $emp) {

				$address   = $emp->addresses->first();
				$bank      = $emp->bankDetails->first();
				$biometric = $emp->biometricDetails;
				$edu       = $emp->educationalQualifications->first();
				$exp       = $emp->workExperiences->first();
				$emergency = $emp->emergencyContacts->first();
				$statutory = $emp->statutoryInformation;

				fputcsv($file, [
					$emp->id,
					$emp->first_name . ' ' . $emp->last_name,
					$emp->email,
					$emp->contact_number,
					$emp->date_of_birth,
					$emp->department->department_name ?? '',
					$emp->staffType->staff_type_name ?? '',
					$address->address_line ?? '',
					$bank->bank_name ?? '',
					$bank->account_number ?? '',
					$biometric->ess_emp_code ?? '',
					$edu->qualification ?? '',
					$edu->institution_name ?? '',
					$exp->total_experience ?? '',
					$emergency->contact_name ?? '',
					$emergency->phone_number ?? '',
					$statutory->pan_number ?? '',
					$statutory->aadhar_number ?? '',
					$statutory->esic_number ?? '',
					$statutory->uan_number ?? '',
					$emp->date_of_joining,
					$emp->confirmation_date,
					$emp->employee_status,
					$emp->is_vacation,
					
					
					
				]);
			}


			fclose($file);
		};

		return response()->stream($callback, 200, $headers);
	}


}

