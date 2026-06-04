<?php 
namespace App\Http\Controllers;

use App\Models\HrmsShift;
use App\Models\HrmsShiftType;
use App\Models\HrmsDepartment;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeShiftHistory;
use App\Models\HrmsPosition;
use App\Models\HrmsStaffType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rules\ValidTimeRange;


class ShiftController extends Controller
{
    public function index()
    {
        $shifts = HrmsShift::with('shiftType')->get();
        return view('backend.HRMS.shifts.index', compact('shifts'));
    }

    public function history(Request $request)
    {
        $departments = HrmsDepartment::orderBy('department_name')->get();
        $positions = HrmsPosition::orderBy('position_name')->get();
        $staffTypes = HrmsStaffType::orderBy('staff_type_name')->get();
        $shifts = HrmsShift::with('shiftType')->get();

        $employees = HrmsEmployee::with(['department', 'position', 'staffType', 'shiftHistories.shift.shiftType'])
            ->where(fn($q) => $q->where('employee_status', 'active')->orWhereNull('employee_status'))
            ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->filled('position_id'), fn($q) => $q->where('position_id', $request->position_id))
            ->when($request->filled('staff_type_id'), fn($q) => $q->where('staff_type_id', $request->staff_type_id))
            ->when($request->filled('employee_status'), fn($q) => $q->where('employee_status', $request->employee_status))
            ->orderBy('first_name')
            ->get();

        $recentHistories = HrmsEmployeeShiftHistory::with(['employee', 'shift.shiftType'])
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('backend.HRMS.shifts.history', compact(
            'departments',
            'employees',
            'positions',
            'recentHistories',
            'shifts',
            'staffTypes'
        ));
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:hrms_employees,id'],
            'shift_id' => ['required', 'integer', 'exists:hrms_shifts,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['required', 'date', 'after_or_equal:effective_from'],
            'remarks' => ['nullable', 'string'],
        ]);

        $createdBy = auth()->id();
        $created = 0;

        foreach ($validated['employee_ids'] as $employeeId) {
            HrmsEmployeeShiftHistory::create([
                'employee_id' => $employeeId,
                'shift_id' => $validated['shift_id'],
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'],
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $createdBy,
            ]);
            $created++;
        }

        return redirect()
            ->route('shifts.history', $request->only(['department_id', 'position_id', 'staff_type_id', 'employee_status']))
            ->with('success', "Shift history assigned for {$created} employee(s).");
    }

    public function editHistory(HrmsEmployeeShiftHistory $history)
    {
        $history->load(['employee', 'shift.shiftType']);
        $shifts = HrmsShift::with('shiftType')->get();

        return view('backend.HRMS.shifts.edit_history', compact('history', 'shifts'));
    }

    public function updateHistory(Request $request, HrmsEmployeeShiftHistory $history)
    {
        $validated = $request->validate([
            'shift_id' => ['required', 'integer', 'exists:hrms_shifts,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['required', 'date', 'after_or_equal:effective_from'],
            'remarks' => ['nullable', 'string'],
        ]);

        $history->update($validated);

        return redirect()
            ->route('shifts.history')
            ->with('success', 'Shift assignment updated successfully.');
    }

    public function destroyHistory(HrmsEmployeeShiftHistory $history)
    {
        $history->delete();

        return redirect()
            ->route('shifts.history')
            ->with('success', 'Shift assignment deleted successfully.');
    }

    public function create()
    {
        $shiftTypes = HrmsShiftType::all();
		return view('backend.HRMS.shifts.create', compact('shiftTypes'));
    }

    
	public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'shift_type_id' => [
                'required',
                'exists:hrms_shift_types,id',
                // Ensures that the combination of shift_type_id is unique in hrms_shifts table
                function ($attribute, $value, $fail) {
                    if (HrmsShift::where('shift_type_id', $value)->exists()) {
                        $fail('A shift with this Shift Type ID already exists.');
                    }
                }
            ],
            'start_time' => 'required|string',
            'end_time' => [
                'required',
                'string',
                new ValidTimeRange($request->input('start_time'), $request->input('end_time'))
            ],
            'late_coming_threshold' => 'required|integer|min:0|max:60',
        ]);

        // Check if a shift with the same Shift_Type_ID already exists
        if (HrmsShift::where('id', $request->id)->exists()) {
            return redirect()->back()->withErrors(['Shift_Type_ID' => 'A shift with this type already exists.'])->withInput();
        }

        // Create a new shift
        HrmsShift::create($request->all());

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully.');
    }

    public function show(HrmsShift $shift)
    {
        return view('backend.HRMS.shifts.show', compact('shift'));
    }

    public function edit(HrmsShift $shift)
    {
        $shiftTypes = HrmsShiftType::all();
        return view('backend.HRMS.shifts.edit', compact('shift', 'shiftTypes'));
    }

	public function update(Request $request, $id)
	{
		// Validate the input
		$request->validate([
			'shift_type_id' => [
				'required',
				'exists:hrms_shift_types,id',
				function ($attribute, $value, $fail) use ($id) {
					// Ensure the Shift_Type_ID is unique for all shifts except the current one
					if (HrmsShift::where('shift_type_id', $value)
						->where('id', '<>', $id)
						->exists()) {
						$fail('A shift with this type already exists.');
					}
				}
			],
			'start_time' => 'required|string',
			'end_time' => ['required', 'string', new ValidTimeRange($request->input('start_time'), $request->input('end_time'))],
			'late_coming_threshold' => 'required|integer|min:0|max:60',
		]);

		// Find the shift and update it
		$shift = HrmsShift::findOrFail($id);
		$shift->update($request->all());

		return redirect()->route('shifts.index')->with('success', 'Shift updated successfully.');
	}


    public function destroy(HrmsShift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
    }
}
