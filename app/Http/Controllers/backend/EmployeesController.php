<?php

namespace App\Http\Controllers;

use App\Models\CommanModel;
use App\Models\Employees;
use App\Models\Position;
use App\Models\Department;
use App\Models\Stafftype;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class EmployeesController extends Controller
{
    public function index()
    {
        $stream = Employees::where('is_delete', 0)->get(); 
        $deparments = Department::where('is_delete', 0)->distinct()->get();

        $staffTypes = Stafftype::where('is_delete', 0)->distinct()->get();

        $positions = Position::where('is_delete', 0)->distinct()->get();
        return view('backend.HRMS.employees', compact('stream', 'deparments', 'positions','staffTypes'));
    }

    public function create(Request $request)
    {
        $data = $request->all();
        Employees::create($data);

        return redirect()->route('employee')->with('success', 'Employee has been created successfully.');
    }

    public function view($id)
    {        
        $stream_master = Employees::where('EmployeeID', $id)->get();
        $stream = Employees::where('is_delete', 0)->get();
        $deparments = Department::where('is_delete', 0)->distinct()->get();
        $positions = Position::where('is_delete', 0)->distinct()->get();

        $staffTypes = Stafftype::where('is_delete', 0)->distinct()->get();
        return view('backend.HRMS.employees', compact('stream_master', 'stream', 'deparments', 'positions' ,'staffTypes'));
    }

    public function store(Request $request)
    {
        $data = [
            'FirstName' => $request->FirstName,
            'LastName' => $request->LastName,
            'Email' => $request->Email,
            'Phone' => $request->Phone,
            'mobile' => $request->mobile,
            'Address' => $request->Address,
            'DateOfBirth' => $request->DateOfBirth,
            'JoiningDate' => $request->JoiningDate,
            'DepartureDate' => $request->DepartureDate,
            'DepartmentID' => $request->DepartmentID,
            'PositionID' => $request->PositionID,
            'ess_emp_code' => $request->ess_emp_code,
            'DeviceCode' => $request->DeviceCode,
            'Company' => $request->Company,
            'Location' => $request->Location,
            'Designation' => $request->Designation,
            'Grade' => $request->Grade,
            'Team' => $request->Team,
            'Category' => $request->Category,
            'EmploymentType' => $request->EmploymentType,
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
            // 'is_vacatation_staff' => $request->has('is_vacatation_staff') ? 1 : 0, // Handle checkbox
            'is_vacatation_staff' => $request->has('is_vacatation_staff') ? 1 : 0,

        ];
    
        // Update the employee record with the new data
        Employees::where('EmployeeID', $request->id)->update($data);
    
        return redirect()->route('employee')->with('success', 'Employee has been updated successfully.');
    }
    

    public function delete($id)
    {
        $employee = Employees::findOrFail($id);
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
}

