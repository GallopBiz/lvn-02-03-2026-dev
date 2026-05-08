<?php

namespace App\Http\Controllers;

use App\Models\InternalAssessmentMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InternalAssessmentMasterController extends Controller
{
    public function index()
    {
        $stream = InternalAssessmentMaster::where('is_delete', 0)
            ->orderBy('display_order')
            ->orderBy('assessment_name')
            ->get();

        return view('backend.AcademicsModules.internal_assessment_master', compact('stream'));
    }

    public function create(Request $request)
    {
        $data = $this->validatedData($request);
        InternalAssessmentMaster::create($data);

        return redirect()->route('internal-assessment-master')->with('success', 'Internal Assessment created successfully.');
    }

    public function view($id)
    {
        $stream_master = InternalAssessmentMaster::where('is_delete', 0)->findOrFail($id);
        $stream = InternalAssessmentMaster::where('is_delete', 0)
            ->orderBy('display_order')
            ->orderBy('assessment_name')
            ->get();

        return view('backend.AcademicsModules.internal_assessment_master', compact('stream_master', 'stream'));
    }

    public function store(Request $request)
    {
        $assessment = InternalAssessmentMaster::where('is_delete', 0)->findOrFail($request->id);
        $assessment->update($this->validatedData($request, $assessment->id));

        return redirect()->route('internal-assessment-master')->with('success', 'Internal Assessment updated successfully.');
    }

    public function toggleStatus($id)
    {
        $assessment = InternalAssessmentMaster::where('is_delete', 0)->findOrFail($id);
        $assessment->update(['is_active' => $assessment->is_active ? 0 : 1]);

        return redirect()->route('internal-assessment-master')->with('success', 'Status updated successfully.');
    }

    public function delete($id)
    {
        $assessment = InternalAssessmentMaster::where('is_delete', 0)->findOrFail($id);
        $assessment->update(['is_delete' => 1, 'is_active' => 0]);

        return redirect()->route('internal-assessment-master')->with('success', 'Internal Assessment deleted successfully.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'assessment_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('dynamic.academic_internal_assessment_master', 'assessment_code')
                    ->where('is_delete', 0)
                    ->ignore($ignoreId),
            ],
            'assessment_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ]);
    }
}
