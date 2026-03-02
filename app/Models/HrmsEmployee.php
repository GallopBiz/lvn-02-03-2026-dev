<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "hrms_employees";

    protected $fillable = [
        'first_name', 
        'last_name', 
        'date_of_birth', 
        'gender', 
        'father_name',
        'marital_status', 
        'spouse_name', 
        'contact_number', 
        'email',
        'date_of_joining', 
        'confirmation_date', 
        'employee_id', 
        'department_id', 
        'shift_id',
        'position_id', 
        'staff_type_id',
        'is_vacation',
        'profile_picture',
        'gross_salary',
        'employee_status',
		'employee_status_date',
    ];

    /** Relationships **/
    public function department()
    {
        return $this->belongsTo(HrmsDepartment::class);
    }

    public function staffType()
    {
        return $this->belongsTo(HrmsStaffType::class);
    }

    public function position()
    {
        return $this->belongsTo(HrmsPosition::class);
    }

    public function addresses()
    {
        return $this->hasMany(HrmsEmployeeAddress::class,'employee_id');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(HrmsEmergencyContact::class,'employee_id');
    }

    public function educationalQualifications()
    {
        return $this->hasMany(HrmsEducationalQualification::class,'employee_id');
    }

    public function workExperiences()
    {
        return $this->hasMany(HrmsWorkExperience::class,'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(HrmsDocument::class,'employee_id');
    }

    public function statutoryInformation()
    {
        return $this->hasOne(HrmsStatutoryInformation::class,'employee_id');
    }

    public function salaries()
    {
        return $this->hasMany(HrmsSalary::class,'employee_id');
    }

    public function activeSalary()
    {
        return $this->hasOne(HrmsSalary::class,'employee_id')->where('is_active', 1);
    }

    public function bankDetails()
    {
        return $this->hasMany(HrmsBankDetail::class,'employee_id');
    }

    public function biometricDetails()
    {
        return $this->hasOne(HrmsBiometricDetail::class,'employee_id');
    }

    public function biometricDetail()
    {
        return $this->hasOne(HrmsBiometricDetail::class, 'employee_id');
    }

    public function loans()
    {
        return $this->hasMany(HrmsEmployeeLoan::class,'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(HrmsPayroll::class);
    }

    public function children()
    {
        return $this->hasMany(HrmsEmployeeChild::class,'employee_id');
    }

    public function employeeDeductions()
    {
        return $this->hasMany(HrmsEmployeeDeduction::class,'employee_id');
    }

    public function deductions()
    {
        return $this->belongsToMany(HrmsBasicDeduction::class, 'hrms_employee_deductions')
                    ->withTimestamps();
    }

    public function leaveRequest()
    {
        return $this->hasMany(HrmsLeaveRequest::class,'employee_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(HrmsEmployeeLeaveBalance::class,'employee_id');
    }

    /** Query Scopes **/
    public function scopeActive($query)
    {
        return $query->where('employee_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('employee_status', 'inactive');
    }

    /** Boot method for cascading deletes **/
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($employee) {
            $employee->addresses()->delete();
            $employee->bankDetails()->delete();
            $employee->biometricDetails()->delete();
            $employee->documents()->delete();
            $employee->educationalQualifications()->delete();
            $employee->emergencyContacts()->delete();
            $employee->salaries()->delete();
            $employee->statutoryInformation()->delete();
            $employee->workExperiences()->delete();
        });
    }
}
