<?php 
namespace App\Http\Controllers;

use App\Models\HrmsShift;
use App\Models\HrmsShiftType;
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
